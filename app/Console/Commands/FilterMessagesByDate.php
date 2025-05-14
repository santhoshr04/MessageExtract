<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Message;
use Carbon\Carbon;

class FilterMessagesByDate extends Command
{
    protected $signature = 'chat:filter-date';
    protected $description = 'Filter WhatsApp messages between two dates (with optional limit)';

    public function handle()
    {
        $start = $this->ask('Enter start date (YYYY-MM-DD)');
        $end = $this->ask('Enter end date (YYYY-MM-DD)');
        $limit = $this->ask('Enter limit (press enter to show all)');

        try {
            $startDate = Carbon::parse($start)->startOfDay();
            $endDate = Carbon::parse($end)->endOfDay();
        } catch (\Exception $e) {
            $this->error("❌ Invalid date format. Use YYYY-MM-DD.");
            return 1;
        }

        $this->info("🔍 Searching messages between {$startDate->toDateTimeString()} and {$endDate->toDateTimeString()}...");

        $query = Message::whereBetween('timestamp', [$startDate, $endDate])
                        ->orderBy('timestamp');

        if ($limit && is_numeric($limit)) {
            $query->limit(intval($limit));
        }

        $messages = $query->get();

        if ($messages->isEmpty()) {
            $this->warn("⚠️ No messages found in this date range.");
            return 0;
        }

        $this->line("📄 Found {$messages->count()} messages:\n");

        foreach ($messages as $msg) {
            $timestamp = Carbon::parse($msg->timestamp); // Ensures proper formatting

            $this->line("📅 " . $timestamp->format('Y-m-d H:i'));
            $this->line("👤 " . $msg->sender);
            $this->line("💬 " . $msg->message);
            if ($msg->media_path) {
                $mediaUrl = config('app.url') . $msg->media_path;
                $this->line("📎 Media: " . $mediaUrl);
            }
            $this->line(str_repeat('-', 40));
        }

        $this->info("✅ Done. Total messages shown: " . $messages->count());
        return 0;
    }
}