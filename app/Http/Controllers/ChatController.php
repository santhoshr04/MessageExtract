<?php

namespace App\Http\Controllers;

use App\Models\Message;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use ZipArchive;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Collection;


class ChatController extends Controller
{
    public function index()
    {
        $messages = Message::orderBy('timestamp')->get();
        return view('index', compact('messages'));
    }
    public function upload(Request $request)
    {
        $request->validate([
            'chat_zip' => 'required|file|mimes:zip|max:20480',
        ]);

        $file = $request->file('chat_zip');

        if (!$file || !$file->isValid()) {
            return back()->with('error', 'Invalid or corrupted ZIP file.');
        }

        // Store in 'public/storage/chats/'
        $filename = uniqid('chat_') . '.zip';
        $zipPath = $file->storeAs('chats', $filename, 'public'); // use 'public' disk
        $fullPath = public_path('storage/chats/' . $filename);   // match ZipArchive path

        // Extract ZIP
        $zip = new ZipArchive();
        if ($zip->open($fullPath) !== TRUE) {
            return back()->with('error', 'Failed to open ZIP file at ' . $fullPath);
        }

        $extractFolder = 'extracted/' . uniqid();
        $extractPath = storage_path('app/' . $extractFolder);

        if (!file_exists($extractPath)) {
            mkdir($extractPath, 0755, true);
        }

        $zip->extractTo($extractPath);
        $zip->close();

        // Find chat text file
        $txtFile = collect(glob($extractPath . '/*.txt'))->first();
        if (!$txtFile) {
            return back()->with('error', 'No chat .txt file found in ZIP.');
        }

        $groupName = pathinfo($txtFile, PATHINFO_FILENAME);
        $lines = file($txtFile);

        // Get media files
        $mediaFiles = collect(glob($extractPath . '/*'))
            ->filter(fn($f) => !Str::endsWith($f, '.txt'))
            ->values()
            ->toArray();

        $mediaIndex = 0;

        foreach ($lines as $line) {
            // Normalize non-breaking space to normal space
            $line = preg_replace('/\x{202F}|\xC2\xA0/u', ' ', $line);

            // Full message with sender: message
            if (preg_match('/^(\d{1,2}\/\d{1,2}\/\d{2,4}),\s(\d{1,2}:\d{2}(?:\s?[APMapm]{2}))\s-\s([^:]+):\s(.*)$/', $line, $matches)) {
                $date = $matches[1];
                $time = $matches[2];
                $sender = trim($matches[3]);
                $message = trim($matches[4]);
            }
            // System message (e.g., "created group")
            elseif (preg_match('/^(\d{1,2}\/\d{1,2}\/\d{2,4}),\s(\d{1,2}:\d{2}(?:\s?[APMapm]{2}))\s-\s(.*)$/', $line, $matches)) {
                $date = $matches[1];
                $time = $matches[2];
                $sender = 'System';
                $message = trim($matches[3]);
            } else {
                continue; // Skip lines that don't match either pattern
            }

            try {
                $timestamp = Carbon::createFromFormat('d/m/y, h:i A', $date . ', ' . $time);
            } catch (\Exception $e) {
                continue; // Skip invalid date
            }

            // Handle media attachment
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

            if (!$this->isRelevantMessage($message)) {
                continue; // skip chit-chat
            }

            Message::create([
                'group_name' => $groupName,
                'sender' => $sender,
                'timestamp' => $timestamp,
                'message' => $message,
                'media_path' => $media_path,
                'language' => null,
            ]);
        }

        return redirect('/')->with('success', 'Chat uploaded and processed successfully.');
    }

    public function isRelevantMessage($text)
    {
        $response = Http::timeout(15)->post('http://localhost:11434/api/generate', [
            'model' => 'nova',
            'prompt' => "You're a filter. Is the following WhatsApp message meaningful for analysis (not greetings, ok, hi, emojis etc)? Reply ONLY with 'yes' or 'no'. Message: \"$text\"",
            'stream' => false
        ]);

        $reply = strtolower(trim($response['response'] ?? ''));

        return $reply === 'yes';
    }

}
