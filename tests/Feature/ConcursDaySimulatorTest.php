<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Carbon\Carbon;
use App\Models\Song;
use App\Models\Vote;
use App\Models\Winner;
use App\Models\Tiebreak;
use App\Models\ContestTheme;
use App\Models\ThemePool;
use App\Models\User;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Ensure we're testing on a weekday
    Carbon::setTestNow(Carbon::create(2024, 1, 15)); // Monday
});

test('simulator creates test users and songs', function () {
    $today = Carbon::today();
    
    // Run the simulator
    $result = Artisan::call('concurs:simulate-day', [
        '--users' => 3,
        '--songs' => 5
    ]);
    
    expect($result)->toBe(0);
    
    // Assert test users were created
    expect(User::count())->toBe(3);
    expect(User::where('name', 'like', 'Test User%')->count())->toBe(3);
    
    // Assert test songs were created
    expect(Song::whereDate('competition_date', $today)->count())->toBe(5);
    expect(Song::where('title', 'like', 'Test Song%')->orWhere('title', 'like', 'Amazing%')->count())->toBeGreaterThan(0);
});

test('simulator handles submissions phase correctly', function () {
    $today = Carbon::today();
    
    // Run simulator up to submissions phase
    Artisan::call('concurs:simulate-day', ['--users' => 2, '--songs' => 3]);
    
    // Check that songs were created with correct competition date
    $songs = Song::whereDate('competition_date', $today)->get();
    expect($songs)->toHaveCount(3);
    
    // Check that songs have users assigned
    foreach ($songs as $song) {
        expect($song->user_id)->not->toBeNull();
        expect($song->votes)->toBe(0);
        expect($song->is_winner)->toBeFalse();
    }
});

test('simulator handles voting phase correctly', function () {
    $today = Carbon::today();
    
    // Run simulator
    Artisan::call('concurs:simulate-day', ['--users' => 3, '--songs' => 4]);
    
    // Check that votes were created
    $votes = Vote::whereDate('vote_date', $today)->get();
    expect($votes)->not->toBeEmpty();
    
    // Check that songs have vote counts updated
    $songs = Song::whereDate('competition_date', $today)->get();
    foreach ($songs as $song) {
        expect($song->votes)->toBeGreaterThan(0);
    }
    
    // Check vote timestamps are within submission window (9:00-19:59)
    foreach ($votes as $vote) {
        $hour = Carbon::parse($vote->created_at)->hour;
        expect($hour)->toBeGreaterThanOrEqual(9);
        expect($hour)->toBeLessThan(20);
    }
});

test('simulator handles tiebreak scenario correctly', function () {
    $today = Carbon::today();
    
    // Create a scenario where we'll have a tie
    $user1 = User::factory()->create(['name' => 'User 1']);
    $user2 = User::factory()->create(['name' => 'User 2']);
    
    // Create songs with same vote count
    $song1 = Song::create([
        'user_id' => $user1->id,
        'title' => 'Tied Song 1',
        'youtube_url' => 'https://youtube.com/watch?v=test1',
        'competition_date' => $today,
        'votes' => 5,
        'is_winner' => false,
    ]);
    
    $song2 = Song::create([
        'user_id' => $user2->id,
        'title' => 'Tied Song 2',
        'youtube_url' => 'https://youtube.com/watch?v=test2',
        'competition_date' => $today,
        'votes' => 5,
        'is_winner' => false,
    ]);
    
    // Create votes to establish the tie
    Vote::create([
        'user_id' => $user1->id,
        'song_id' => $song1->id,
        'vote_date' => $today,
        'created_at' => $today->copy()->setTime(10, 0),
    ]);
    
    Vote::create([
        'user_id' => $user2->id,
        'song_id' => $song2->id,
        'vote_date' => $today,
        'created_at' => $today->copy()->setTime(10, 0),
    ]);
    
    // Run simulator
    Artisan::call('concurs:simulate-day', ['--users' => 1, '--songs' => 1]);
    
    // Check that tiebreak was created
    $tiebreak = Tiebreak::whereDate('contest_date', $today)->first();
    expect($tiebreak)->not->toBeNull();
    expect($tiebreak->resolved)->toBeTrue();
    
    // Check tiebreak timing
    expect($tiebreak->starts_at->format('H:i'))->toBe('20:00');
    expect($tiebreak->ends_at->format('H:i'))->toBe('20:30');
    
    // Check tiebreak songs
    expect($tiebreak->song_ids)->toContain($song1->id);
    expect($tiebreak->song_ids)->toContain($song2->id);
});

test('simulator declares winner correctly', function () {
    $today = Carbon::today();
    
    // Run simulator
    Artisan::call('concurs:simulate-day', ['--users' => 2, '--songs' => 3]);
    
    // Check that a winner was declared
    $winner = Winner::whereDate('contest_date', $today)->first();
    expect($winner)->not->toBeNull();
    
    // Check winner details
    expect($winner->song_id)->not->toBeNull();
    expect($winner->user_id)->not->toBeNull();
    expect($winner->vote_count)->toBeGreaterThan(0);
    expect($winner->theme_chosen)->toBeFalse();
    
    // Check that the winning song is marked as winner
    $winningSong = Song::find($winner->song_id);
    expect($winningSong->is_winner)->toBeTrue();
});

test('simulator handles theme selection phase correctly', function () {
    $today = Carbon::today();
    
    // Run simulator
    Artisan::call('concurs:simulate-day', ['--users' => 2, '--songs' => 2]);
    
    // Check that theme was selected for tomorrow
    $tomorrow = $today->copy()->addDay();
    $contestTheme = ContestTheme::whereDate('contest_date', $tomorrow)->first();
    expect($contestTheme)->not->toBeNull();
    expect($contestTheme->picked_by_winner)->toBeTrue();
    
    // Check that winner is marked as having chosen theme
    $winner = Winner::whereDate('contest_date', $today)->first();
    expect($winner->theme_chosen)->toBeTrue();
    
    // Check that theme pool was created
    $themePool = ThemePool::where('name', 'Test Theme Pool')->first();
    expect($themePool)->not->toBeNull();
    expect($themePool->category)->toBe('General');
});

test('simulator respects weekday constraint', function () {
    // Set to weekend
    Carbon::setTestNow(Carbon::create(2024, 1, 14)); // Sunday
    
    // Run simulator
    $result = Artisan::call('concurs:simulate-day');
    
    // Should fail on weekends
    expect($result)->toBe(1);
    
    // No data should be created
    expect(User::count())->toBe(0);
    expect(Song::count())->toBe(0);
});

test('simulator clears existing data before running', function () {
    $today = Carbon::today();
    
    // Create some existing data
    $user = User::factory()->create();
    $song = Song::create([
        'user_id' => $user->id,
        'title' => 'Existing Song',
        'youtube_url' => 'https://youtube.com/watch?v=existing',
        'competition_date' => $today,
        'votes' => 0,
        'is_winner' => false,
    ]);
    
    $vote = Vote::create([
        'user_id' => $user->id,
        'song_id' => $song->id,
        'vote_date' => $today,
    ]);
    
    // Run simulator
    Artisan::call('concurs:simulate-day', ['--users' => 2, '--songs' => 2]);
    
    // Check that old data was cleared
    expect(Song::where('title', 'Existing Song')->exists())->toBeFalse();
    expect(Vote::where('song_id', $song->id)->exists())->toBeFalse();
    
    // Check that new data was created
    expect(Song::whereDate('competition_date', $today)->count())->toBe(2);
});

test('simulator handles custom user and song counts', function () {
    $today = Carbon::today();
    
    // Run with custom counts
    Artisan::call('concurs:simulate-day', [
        '--users' => 5,
        '--songs' => 8
    ]);
    
    // Check custom counts were respected
    expect(User::count())->toBe(5);
    expect(Song::whereDate('competition_date', $today)->count())->toBe(8);
    
    // Check that all users have songs assigned
    $usersWithSongs = User::whereHas('songs', function ($query) use ($today) {
        $query->whereDate('competition_date', $today);
    })->count();
    
    expect($usersWithSongs)->toBeGreaterThan(0);
});

test('simulator creates realistic voting patterns', function () {
    $today = Carbon::today();
    
    // Run simulator
    Artisan::call('concurs:simulate-day', ['--users' => 4, '--songs' => 6]);
    
    // Check that votes have realistic timestamps
    $votes = Vote::whereDate('vote_date', $today)->get();
    expect($votes)->not->toBeEmpty();
    
    foreach ($votes as $vote) {
        $voteTime = Carbon::parse($vote->created_at);
        $hour = $voteTime->hour;
        
        // Votes should be between 9:00 and 19:59 (before 20:00 deadline)
        expect($hour)->toBeGreaterThanOrEqual(9);
        expect($hour)->toBeLessThan(20);
    }
    
    // Check that songs have varying vote counts
    $songs = Song::whereDate('competition_date', $today)->get();
    $voteCounts = $songs->pluck('votes')->toArray();
    
    // Should have some variation in votes
    expect(count(array_unique($voteCounts)))->toBeGreaterThan(1);
});

test('simulator handles edge case with no votes', function () {
    $today = Carbon::today();
    
    // Create users and songs but no votes
    $user = User::factory()->create();
    $song = Song::create([
        'user_id' => $user->id,
        'title' => 'No Votes Song',
        'youtube_url' => 'https://youtube.com/watch?v=novotes',
        'competition_date' => $today,
        'votes' => 0,
        'is_winner' => false,
    ]);
    
    // Run simulator
    Artisan::call('concurs:simulate-day', ['--users' => 1, '--songs' => 1]);
    
    // Should still create a winner (with 0 votes)
    $winner = Winner::whereDate('contest_date', $today)->first();
    expect($winner)->not->toBeNull();
    expect($winner->vote_count)->toBe(0);
});

test('simulator maintains data integrity across phases', function () {
    $today = Carbon::today();
    
    // Run simulator
    Artisan::call('concurs:simulate-day', ['--users' => 3, '--songs' => 4]);
    
    // Check that all relationships are maintained
    $winner = Winner::whereDate('contest_date', $today)->first();
    expect($winner)->not->toBeNull();
    
    // Winner should have valid song and user
    $song = Song::find($winner->song_id);
    $user = User::find($winner->user_id);
    
    expect($song)->not->toBeNull();
    expect($user)->not->toBeNull();
    expect($song->user_id)->toBe($user->id);
    
    // Check that votes are properly linked
    $votes = Vote::whereDate('vote_date', $today)->get();
    foreach ($votes as $vote) {
        expect(Song::find($vote->song_id))->not->toBeNull();
        expect(User::find($vote->user_id))->not->toBeNull();
    }
});
