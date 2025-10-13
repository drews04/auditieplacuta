<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use App\Models\ContestCycle;
use Carbon\Carbon;

class ConcursInheritPoster extends Command
{
    protected $signature = 'concurs:inherit-poster';
    protected $description = 'Copy yesterday\'s submission poster onto today\'s vote cycle if missing';

    public function handle()
    {
        $today = Carbon::today('Europe/Bucharest');
        $yesterday = $today->copy()->subDay();

        $submit = ContestCycle::whereDate('start_at', $yesterday)
            ->whereNotNull('poster_url')
            ->first();

        $vote = ContestCycle::whereDate('vote_start_at', $today)
            ->first();

        if ($submit && $submit->poster_url && $vote && !$vote->poster_url) {
            $path = str_replace('/storage/', '', parse_url($submit->poster_url, PHP_URL_PATH));
            if (Storage::disk('public')->exists($path)) {
                $copyPath = 'concurs/posters/vote_' . $today->format('Ymd') . '_' . basename($path);
                Storage::disk('public')->copy($path, $copyPath);
                $vote->poster_url = Storage::url($copyPath) . '?t=' . time();
                $vote->save();
                $this->info("Poster inherited from submission to vote cycle.");
            } else {
                $this->warn("Source poster not found on disk.");
            }
        } else {
            $this->info("No poster to inherit or vote already has one.");
        }

        return Command::SUCCESS;
    }
}
