<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use ZipArchive;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Models\Message;
use App\Models\KnowledgeEntry;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\File;

class ProcessChatZip extends Command
{
    protected $signature = 'chat:process-zip';
    protected $description = 'Process a WhatsApp chat ZIP file via CLI with animation and message count';

    public function handle()
    {
        $path = $this->ask('Enter full path to ZIP file');

        if (!file_exists($path)) {
            $this->error("❌ File does not exist: $path");
            return 1;
        }

        $this->info("📦 Validating and opening ZIP file...");

        $zip = new ZipArchive();
        if ($zip->open($path) !== true) {
            $this->error("❌ Failed to open ZIP file.");
            return 1;
        }

        $extractFolder = 'extracted/' . uniqid();
        $extractPath = storage_path('app/' . $extractFolder);
        if (!file_exists($extractPath)) {
            mkdir($extractPath, 0755, true);
        }

        $zip->extractTo($extractPath);
        $zip->close();

        $txtFile = collect(glob($extractPath . '/*.txt'))->first();
        if (!$txtFile) {
            $this->error('❌ No .txt file found in ZIP.');
            return 1;
        }

        $groupName = pathinfo($txtFile, PATHINFO_FILENAME);
        $lines = file($txtFile);

        $mediaFiles = collect(glob($extractPath . '/*'))
            ->filter(fn($f) => !Str::endsWith($f, '.txt'))
            ->values()
            ->toArray();

        $mediaIndex = 0;
        $savedMessages = 0;
        $spinner = ['|', '/', '-', '\\'];
        $spinIndex = 0;
        $previousQuestions = [];

        foreach ($lines as $index => $line) {
            echo "\r" . $spinner[$spinIndex % 4] . " Processing line $index";
            $spinIndex++;

            $line = preg_replace('/\x{202F}|\xC2\xA0/u', ' ', $line);

            if (preg_match('/^(\d{1,2}\/\d{1,2}\/\d{2,4}),\s(\d{1,2}:\d{2}(?:\s?[APMapm]{2}))\s-\s([^:]+):\s(.*)$/', $line, $matches)) {
                $date = $matches[1];
                $time = $matches[2];
                $sender = trim($matches[3]);
                $message = trim($matches[4]);
            } elseif (preg_match('/^(\d{1,2}\/\d{1,2}\/\d{2,4}),\s(\d{1,2}:\d{2}(?:\s?[APMapm]{2}))\s-\s(.*)$/', $line, $matches)) {
                $date = $matches[1];
                $time = $matches[2];
                $sender = 'System';
                $message = trim($matches[3]);
            } else {
                continue;
            }

            try {
                $timestamp = Carbon::createFromFormat('d/m/y, h:i A', $date . ', ' . $time);
            } catch (\Exception $e) {
                continue;
            }

            $media_path = null;
            if (Str::contains($message, 'attached') || Str::contains($message, 'omitted')) {
                if (isset($mediaFiles[$mediaIndex]) && is_file($mediaFiles[$mediaIndex])) {
                    $sourcePath = $mediaFiles[$mediaIndex];

                    if (file_exists($sourcePath) && is_readable($sourcePath)) {
                        $mediaFile = new \Illuminate\Http\File($sourcePath);
                        $mediaFileName = uniqid() . '_' . basename($sourcePath);

                        try {
                            Storage::disk('public')->makeDirectory('media');
                            $storedPath = Storage::disk('public')->putFileAs('media', $mediaFile, $mediaFileName);
                            $media_path = $storedPath;
                        } catch (\Exception $e) {
                            logger()->error('Media save failed: ' . $e->getMessage());
                        }

                        $mediaIndex++;
                    }
                }
            }

            $skipPhrases = ['hi', 'hello', 'good morning', 'good night', 'gm', 'ok', 'okay', 'lol'];
            if (in_array(strtolower(trim($message)), $skipPhrases)) {
                continue;
            }

            $analysis = $this->analyzeMessageWithLLM($message, $previousQuestions);
            $type = $analysis['type'] ?? 'neither';
            $matchIndex = $analysis['question_index'] ?? null;

            if ($type === 'neither') {
                continue;
            }

            $messageObj = Message::create([
                'group_name' => $groupName,
                'sender' => $sender,
                'timestamp' => $timestamp,
                'message' => $message,
                'media_path' => $media_path,
                'language' => null,
            ]);
            $savedMessages++;

            if ($type === 'question') {
                KnowledgeEntry::create([
                    'question' => $message,
                    'answer' => null,
                    'category' => 'general',
                    'confidence' => 1.0,
                    'question_id' => $messageObj->id,
                    'answer_id' => null,
                    'media_path' => $media_path,
                    'remarks' => null,
                ]);

                $previousQuestions[] = [
                    'id' => $messageObj->id,
                    'message' => $message,
                ];
            } elseif ($type === 'answer' && $matchIndex !== null && isset($previousQuestions[$matchIndex - 1])) {
                $question = $previousQuestions[$matchIndex - 1];
                KnowledgeEntry::where('question_id', $question['id'])->update([
                    'answer' => $message,
                    'answer_id' => $messageObj->id,
                ]);
            }

            usleep(25000); // Delay to show spinner
        }

        echo "\r✅ Done! Total saved messages: $savedMessages\n";
        return 0;
    }

    private function analyzeMessageWithLLM($text, $previousQuestions = [])
    {
        $prompt = <<<EOT
        You are a WhatsApp message analyzer.

        Your job is to classify a given message as one of:
        - "question" if it asks something meaningful
        - "answer" if it provides an informative or relevant reply
        - "neither" if it is casual talk, greetings, jokes, or non-informative content (e.g. "hi", "good morning", "lol", "okay")

        Also, if the message is an answer, try to match it to one of the previous questions (by index).

        Only extract meaningful and analytical content — ignore short messages and chit-chat.

        Message:
        "$text"

        Previous Questions:
        EOT;

        foreach ($previousQuestions as $i => $q) {
            $prompt .= "\n" . ($i + 1) . ". " . $q['message'];
        }

        $prompt .= <<<EOT


        Respond ONLY with valid JSON format like:
        { "type": "question" | "answer" | "neither", "question_index": number | null }
        EOT;

        $response = Http::timeout(15)->post('http://localhost:11434/api/generate', [
            //TODO REPLACE THE MODAL NAME BEFORE RUN
            'model' => 'nova',
            'prompt' => $prompt,
            'stream' => false,
        ]);

        $reply = trim($response['response'] ?? '');

        try {
            return json_decode($reply, true);
        } catch (\Exception $e) {
            logger()->warning("Failed to parse LLM response: $reply");
            return ['type' => 'neither', 'question_index' => null];
        }
    }
}
