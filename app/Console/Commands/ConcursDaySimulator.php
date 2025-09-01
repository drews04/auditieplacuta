<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\Song;
use App\Models\Vote;
use App\Models\Winner;
use App\Models\Tiebreak;
use App\Models\ContestTheme;
use App\Models\ThemePool;
use App\Models\User;

class ConcursDaySimulator extends Command
{
    protected $signature = 'concurs:simulate-day {--users=3 : Number of fake users to create} {--songs=5 : Number of fake songs to create}';
    protected $description = 'Simulate a full weekday cycle for Concurs music contest';

    private $testUsers = [];
    private $testSongs = [];
    private $testVotes = [];

    public function handle()
    {
        $this->info('🎵 Starting Concurs Day Simulator...');
        $this->info('=====================================');

        // Ensure we're on a weekday
        $today = Carbon::today();
        if ($today->isWeekend()) {
            $this->error('❌ Cannot simulate on weekends. Please run on a weekday.');
            return self::FAILURE;
        }

        // Clear any existing data for today
        $this->clearTodaysData($today);

        // Phase 1: Submissions window (09:00 - 20:00)
        $this->simulateSubmissionsPhase($today);

        // Phase 2: Voting closes at 20:00
        $this->simulateVotingCloses($today);

        // Phase 3: Check for tie and run tiebreak if needed
        $this->simulateTiebreakPhase($today);

        // Phase 4: Final winner declaration
        $this->simulateWinnerDeclaration($today);

        // Phase 5: Theme selection deadline (21:00)
        $this->simulateThemeSelection($today);

        $this->info('✅ Concurs day simulation completed successfully!');
        return self::SUCCESS;
    }

    private function clearTodaysData(Carbon $today): void
    {
        $this->info('🧹 Clearing existing data for today...');
        
        // Clear today's data
        Song::whereDate('competition_date', $today)->delete();
        Vote::whereDate('vote_date', $today)->delete();
        Winner::whereDate('contest_date', $today)->delete();
        Tiebreak::whereDate('contest_date', $today)->delete();
        ContestTheme::whereDate('contest_date', $today)->delete();
        
        $this->info('   ✓ Cleared existing contest data');
    }

    private function simulateSubmissionsPhase(Carbon $today): void
    {
        $this->info('📝 Phase 1: Submissions Window (09:00 - 20:00)');
        
        // Set time to 09:00 (submissions open)
        Carbon::setTestNow($today->copy()->setTime(9, 0));
        $this->info('   ⏰ Time: ' . Carbon::now()->format('H:i'));

        // Create test users
        $this->createTestUsers();
        
        // Create test songs
        $this->createTestSongs($today);
        
        $this->info('   ✓ Created ' . count($this->testUsers) . ' test users');
        $this->info('   ✓ Created ' . count($this->testSongs) . ' test songs');
        $this->info('   ✓ Submissions are now open');
    }

    private function simulateVotingCloses(Carbon $today): void
    {
        $this->info('🗳️  Phase 2: Voting Closes (20:00)');
        
        // Set time to 20:00 (voting closes)
        Carbon::setTestNow($today->copy()->setTime(20, 0));
        $this->info('   ⏰ Time: ' . Carbon::now()->format('H:i'));

        // Simulate some voting activity
        $this->simulateVotingActivity($today);
        
        $this->info('   ✓ Simulated voting activity');
        $this->info('   ✓ Voting is now closed');
    }

    private function simulateTiebreakPhase(Carbon $today): void
    {
        $this->info('⚖️  Phase 3: Tiebreak Check & Resolution (20:00 - 20:30)');
        
        // Check if we have a tie
        $tieSongs = $this->getTieSongs($today);
        
        if ($tieSongs->count() >= 2) {
            $this->info('   🔗 Tie detected! Opening tiebreak...');
            
            // Create tiebreak
            $tiebreak = Tiebreak::create([
                'contest_date' => $today,
                'starts_at' => $today->copy()->setTime(20, 0),
                'ends_at' => $today->copy()->setTime(20, 30),
                'song_ids' => $tieSongs->pluck('id')->toArray(),
                'resolved' => false,
            ]);
            
            $this->info('   ✓ Tiebreak created from ' . $today->copy()->setTime(20, 0)->format('H:i') . ' to ' . $today->copy()->setTime(20, 30)->format('H:i'));
            
            // Simulate tiebreak voting
            $this->simulateTiebreakVoting($tiebreak);
            
            // Set time to 20:30 (tiebreak ends)
            Carbon::setTestNow($today->copy()->setTime(20, 30));
            $this->info('   ⏰ Time: ' . Carbon::now()->format('H:i'));
            $this->info('   ✓ Tiebreak voting period ended');
            
            // Resolve tiebreak
            $this->resolveTiebreak($tiebreak);
        } else {
            $this->info('   ✅ No tie detected, proceeding to winner declaration');
        }
    }

    private function simulateWinnerDeclaration(Carbon $today): void
    {
        $this->info('🏆 Phase 4: Winner Declaration');
        
        // Set time to 20:30 (after potential tiebreak)
        Carbon::setTestNow($today->copy()->setTime(20, 30));
        $this->info('   ⏰ Time: ' . Carbon::now()->format('H:i'));

        // Get the winner
        $winner = $this->declareWinner($today);
        
        if ($winner) {
            $this->info('   🎉 Winner declared: ' . $winner->song->title);
            $this->info('   👤 Winner user: ' . $winner->user->name);
            $this->info('   🗳️  Vote count: ' . $winner->vote_count);
        } else {
            $this->info('   ❌ No winner could be declared');
        }
    }

    private function simulateThemeSelection(Carbon $today): void
    {
        $this->info('🎯 Phase 5: Theme Selection Deadline (21:00)');
        
        // Set time to 21:00 (theme deadline)
        Carbon::setTestNow($today->copy()->setTime(21, 0));
        $this->info('   ⏰ Time: ' . Carbon::now()->format('H:i'));

        // Check if winner has chosen a theme
        $winner = Winner::whereDate('contest_date', $today)->first();
        
        if ($winner && !$winner->theme_chosen) {
            $this->info('   ⏰ Theme selection deadline reached');
            $this->info('   ❌ Winner has not chosen a theme yet');
            
            // Simulate theme selection (optional - you can comment this out to test deadline)
            $this->simulateThemeSelectionByWinner($winner, $today);
        } else {
            $this->info('   ✅ Theme already selected or no winner');
        }
        
        $this->info('   ✓ Theme selection phase completed');
    }

    private function createTestUsers(): void
    {
        $userCount = (int) $this->option('users');
        
        for ($i = 1; $i <= $userCount; $i++) {
            $user = User::create([
                'name' => "Test User {$i}",
                'email' => "testuser{$i}@example.com",
                'password' => bcrypt('password'),
                'email_verified_at' => now(),
            ]);
            
            $this->testUsers[] = $user;
        }
    }

    private function createTestSongs(Carbon $today): void
    {
        $songCount = (int) $this->option('songs');
        $titles = [
            'Amazing Melody',
            'Rock Anthem',
            'Jazz Fusion',
            'Electronic Dreams',
            'Classical Harmony',
            'Blues Journey',
            'Pop Sensation',
            'Folk Tale'
        ];
        
        for ($i = 0; $i < $songCount; $i++) {
            $user = $this->testUsers[$i % count($this->testUsers)];
            
            $song = Song::create([
                'user_id' => $user->id,
                'title' => $titles[$i] ?? "Test Song " . ($i + 1),
                'youtube_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
                'competition_date' => $today,
                'votes' => 0,
                'is_winner' => false,
            ]);
            
            $this->testSongs[] = $song;
        }
    }

    private function simulateVotingActivity(Carbon $today): void
    {
        // Simulate random voting patterns
        foreach ($this->testSongs as $song) {
            $voteCount = rand(1, 8); // Random votes between 1-8
            
            for ($i = 0; $i < $voteCount; $i++) {
                $voter = $this->testUsers[array_rand($this->testUsers)];
                
                Vote::create([
                    'user_id' => $voter->id,
                    'song_id' => $song->id,
                    'vote_date' => $today,
                    'created_at' => $today->copy()->setTime(rand(9, 19), rand(0, 59)),
                ]);
            }
            
            // Update song vote count
            $song->update(['votes' => $voteCount]);
        }
    }

    private function simulateTiebreakVoting(Tiebreak $tiebreak): void
    {
        // Simulate additional voting during tiebreak
        foreach ($tiebreak->song_ids as $songId) {
            $additionalVotes = rand(1, 3);
            
            for ($i = 0; $i < $additionalVotes; $i++) {
                $voter = $this->testUsers[array_rand($this->testUsers)];
                
                Vote::create([
                    'user_id' => $voter->id,
                    'song_id' => $songId,
                    'vote_date' => $tiebreak->contest_date,
                    'tiebreak_id' => $tiebreak->id,
                    'created_at' => $tiebreak->starts_at->copy()->addMinutes(rand(1, 29)),
                ]);
            }
        }
    }

    private function getTieSongs(Carbon $today): \Illuminate\Support\Collection
    {
        $maxVotes = Song::whereDate('competition_date', $today)->max('votes');
        
        if ($maxVotes === null) {
            return collect();
        }
        
        return Song::whereDate('competition_date', $today)
            ->where('votes', $maxVotes)
            ->orderBy('created_at')
            ->get();
    }

    private function resolveTiebreak(Tiebreak $tiebreak): void
    {
        // Count votes during tiebreak period
        $voteCounts = Vote::select('song_id', DB::raw('COUNT(*) as total'))
            ->whereIn('song_id', $tiebreak->song_ids)
            ->whereBetween('created_at', [$tiebreak->starts_at, $tiebreak->ends_at])
            ->groupBy('song_id')
            ->orderByDesc('total')
            ->get();
        
        if ($voteCounts->isEmpty()) {
            // No votes during tiebreak, pick first song
            $winnerSongId = $tiebreak->song_ids[0];
        } else {
            $topVotes = $voteCounts->first()->total;
            $leaders = $voteCounts->where('total', $topVotes)->pluck('song_id');
            
            if ($leaders->count() === 1) {
                $winnerSongId = $leaders->first();
            } else {
                // Still a tie, pick the first one
                $winnerSongId = $leaders->first();
            }
        }
        
        $tiebreak->update(['resolved' => true]);
        $this->info("   🏆 Tiebreak resolved, winner song ID: {$winnerSongId}");
    }

    private function declareWinner(Carbon $today): ?Winner
    {
        // Get the song with the most votes
        $topSong = Song::whereDate('competition_date', $today)
            ->orderByDesc('votes')
            ->orderBy('created_at')
            ->first();
        
        if (!$topSong) {
            return null;
        }
        
        // Check if winner already exists
        $existingWinner = Winner::whereDate('contest_date', $today)->first();
        if ($existingWinner) {
            return $existingWinner;
        }
        
        // Create winner record
        $winner = Winner::create([
            'contest_date' => $today,
            'user_id' => $topSong->user_id,
            'song_id' => $topSong->id,
            'vote_count' => $topSong->votes,
            'was_tie' => false,
            'theme_chosen' => false,
        ]);
        
        // Mark song as winner
        $topSong->update(['is_winner' => true]);
        
        return $winner;
    }

    private function simulateThemeSelectionByWinner(Winner $winner, Carbon $today): void
    {
        // Create a theme pool if it doesn't exist
        $themePool = ThemePool::firstOrCreate([
            'name' => 'Test Theme Pool',
            'category' => 'General'
        ]);
        
        // Create contest theme for tomorrow
        $tomorrow = $today->copy()->addDay();
        
        ContestTheme::create([
            'contest_date' => $tomorrow,
            'theme_pool_id' => $themePool->id,
            'picked_by_winner' => true,
        ]);
        
        // Mark winner as having chosen theme
        $winner->update(['theme_chosen' => true]);
        
        $this->info('   🎯 Winner has chosen a theme for tomorrow');
        $this->info('   📅 Theme set for: ' . $tomorrow->format('Y-m-d'));
    }
}
