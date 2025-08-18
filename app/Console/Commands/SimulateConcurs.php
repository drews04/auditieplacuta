<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Str;

class SimulateConcurs extends Command
{
    protected $signature = 'concurs:simulate
                            {--days=5 : How many contest days to simulate}
                            {--start= : Start date (YYYY-MM-DD); defaults to last Monday}
                            {--users=* : Limit to these user IDs (optional)}
                            {--dry-run : Show what would happen without writing}';

    protected $description = 'Simulate multiple contest days (themes, uploads, votes, winners) end-to-end';

    // ---------- helpers ----------
    protected function v(): bool
    {
        return $this->output->isVerbose() || $this->output->isVeryVerbose() || $this->output->isDebug();
    }

    protected function has(string $table, string $col = null): bool
    {
        if (!Schema::hasTable($table)) return false;
        return $col ? Schema::hasColumn($table, $col) : true;
    }

    protected function pickUsers(array $limitToIds = [], int $min = 3): array
    {
        $q = DB::table('users')->select('id','name');
        if (!empty($limitToIds)) $q->whereIn('id', $limitToIds);
        $users = $q->orderBy('id')->get()->all();

        // Need at least 3 to have a meaningful day
        if (count($users) < $min) return [];
        return $users;
    }

    protected function ensureTheme(Carbon $day, bool $dry): ?int
    {
        // Try to find a themes-like table
        $themeTableCandidates = ['themes','song_themes','concurs_themes','competition_themes'];
        $themesTable = null;
        foreach ($themeTableCandidates as $t) {
            if ($this->has($t)) { $themesTable = $t; break; }
        }
        if (!$themesTable) { if ($this->v()) $this->comment('No themes table found — skipping theme creation.'); return null; }

        // Detect likely columns
        $nameCol = Schema::hasColumn($themesTable,'name') ? 'name' : (Schema::hasColumn($themesTable,'title') ? 'title' : null);
        $dateCol = Schema::hasColumn($themesTable,'date') ? 'date' : (Schema::hasColumn($themesTable,'for_date') ? 'for_date' : null);

        // If there’s a per-day uniqueness, try to reuse
        $existingId = null;
        if ($dateCol) {
            $ex = DB::table($themesTable)->where($dateCol, $day->toDateString())->first();
            if ($ex) $existingId = $ex->id ?? null;
        }
        if ($existingId) return (int)$existingId;

        if ($dry) return null;

        $payload = [];
        if ($nameCol) $payload[$nameCol] = 'Tema zilei ' . $day->toDateString();
        if ($dateCol) $payload[$dateCol] = $day->toDateString();
        if ($this->has($themesTable, 'created_at')) $payload['created_at'] = now();
        if ($this->has($themesTable, 'updated_at')) $payload['updated_at'] = now();

        try {
            return (int) DB::table($themesTable)->insertGetId($payload);
        } catch (\Throwable $e) {
            if ($this->v()) $this->warn('Theme insert failed: ' . $e->getMessage());
            return null;
        }
    }

    protected function createSongsForUsers(array $users, Carbon $day, bool $dry): array
    {
        if (!$this->has('songs','user_id')) { if ($this->v()) $this->comment('No songs table with user_id — skipping song creation.'); return []; }

        $songIds = [];
        foreach ($users as $u) {
            $youtubeUrl = 'https://youtu.be/' . Str::random(11);
            $title = 'Test Song ' . Str::upper(Str::random(5));

            if ($dry) { $songIds[] = -1; continue; }

            $payload = [
                'user_id'    => $u->id,
            ];
            if ($this->has('songs','youtube_url')) $payload['youtube_url'] = $youtubeUrl;
            if ($this->has('songs','title'))       $payload['title'] = $title;

            // Try to attach to a specific day if schema has it
            foreach (['contest_date','for_date','day','date'] as $dc) {
                if ($this->has('songs', $dc)) { $payload[$dc] = $day->toDateString(); break; }
            }

            if ($this->has('songs','created_at')) $payload['created_at'] = $day->copy()->setTime(10,0,0);
            if ($this->has('songs','updated_at')) $payload['updated_at'] = $day->copy()->setTime(10,0,0);

            try {
                $sid = DB::table('songs')->insertGetId($payload);
                $songIds[] = $sid;
            } catch (\Throwable $e) {
                if ($this->v()) $this->warn('Song insert failed for user '.$u->id.': '.$e->getMessage());
            }
        }
        return $songIds;
    }

    protected function castVotes(array $users, array $songIds, bool $dry, Carbon $day): void
    {
        if (!$this->has('votes')) { if ($this->v()) $this->comment('No votes table — skipping votes.'); return; }

        // fetch song -> owner map to avoid self votes
        $songs = [];
        if (!empty($songIds) && $songIds[0] !== -1) {
            $songs = DB::table('songs')->whereIn('id',$songIds)->get(['id','user_id'])->keyBy('id')->all();
        } else {
            // Dry run: synthesize mapping array with fake owner ids to avoid self-vote logic errors
            foreach ($songIds as $k => $sid) {
                $songs[$k+1] = (object)['id'=>$k+1,'user_id'=>$users[$k]->id ?? -1];
            }
        }

        foreach ($users as $u) {
            // pick a random song not owned by this user
            $pool = array_values(array_filter($songs, fn($s) => $s->user_id != $u->id));
            if (empty($pool)) continue;
            $pick = $pool[array_rand($pool)];

            if ($dry) continue;

            $payload = [
                'user_id'  => $u->id,
                'song_id'  => $pick->id,
            ];
            // optional per-day / timestamps
            foreach (['for_date','date','voted_at'] as $dc) {
                if ($this->has('votes',$dc)) { $payload[$dc] = $day->toDateString(); break; }
            }
            if ($this->has('votes','created_at')) $payload['created_at'] = $day->copy()->setTime(19,0,0);
            if ($this->has('votes','updated_at')) $payload['updated_at'] = $day->copy()->setTime(19,0,0);

            try {
                DB::table('votes')->insert($payload);
            } catch (\Throwable $e) {
                if ($this->v()) $this->warn('Vote insert failed for user '.$u->id.' -> song '.$pick->id.': '.$e->getMessage());
            }
        }
    }

    // ---------- main ----------
    public function handle(): int
    {
        $days   = (int) $this->option('days');
        $start  = $this->option('start');
        $limit  = (array) $this->option('users');
        $dry    = (bool) $this->option('dry-run');

        // 1) Determine start date (default = last Monday)
        $current = $start
            ? Carbon::parse($start)->startOfDay()
            : Carbon::now()->startOfWeek(Carbon::MONDAY)->startOfDay();

        $this->info("Concurs simulation starting");
        $this->line("  Days:  {$days}");
        $this->line("  Start: " . $current->toDateString());
        $this->line("  Users: " . (empty($limit) ? '(auto-pick)' : implode(',', $limit)));
        $this->line("  Mode:  " . ($dry ? 'DRY-RUN (no writes)' : 'LIVE'));

        // 2) Get user pool (needs at least 3)
        if (!Schema::hasTable('users')) {
            $this->error('No users table found. Aborting.');
            return self::FAILURE;
        }
        $users = $this->pickUsers($limit, 3);
        if (empty($users)) {
            $this->error('Not enough users (need >= 3). Add users or pass --users=1 --users=2 --users=3');
            return self::FAILURE;
        }
        if ($this->v()) $this->line('Using '.count($users).' users.');

        $simulated = 0;

        for ($i = 0; $i < $days; $i++) {
            // Skip weekends
            if ($current->isSaturday() || $current->isSunday()) {
                if ($this->v()) $this->comment("Skipping weekend: " . $current->toDateString());
                $current->addDay();
                $i--; // don’t count weekends towards --days
                continue;
            }

            // 10:00 — submissions open (+ ensure theme)
            $submissionTime = $current->copy()->setTime(10, 0, 0);
            Carbon::setTestNow($submissionTime);
            if ($this->v()) $this->line("→ Day " . ($simulated + 1) . " @ " . $submissionTime->toDateTimeString());

            $themeId = $this->ensureTheme($current, $dry);
            if ($this->v()) $this->line('   Theme ' . ($themeId ? "#$themeId" : '(skipped)'));

            // Create songs for all users
            $songIds = $this->createSongsForUsers($users, $current, $dry);
            if ($this->v()) $this->line('   Songs created: ' . (empty($songIds) ? 0 : count($songIds)));

            // 19:00 — voting activity
            $voteTime = $current->copy()->setTime(19, 0, 0);
            Carbon::setTestNow($voteTime);
            $this->castVotes($users, $songIds, $dry, $current);

            // 20:00 — voting closes; run your real winner command
            $closeTime = $current->copy()->setTime(20, 0, 0);
            Carbon::setTestNow($closeTime);
            if ($this->v()) $this->line("   Closing & declaring winner @ " . $closeTime->toDateTimeString());

            if (!$dry) {
                try {
                    Artisan::call('concurs:declare-winner', [], $this->output);
                } catch (\Throwable $e) {
                    $this->warn('   Winner command failed: '.$e->getMessage());
                }
            }

            // 21:00 — winner chooses theme (we already created/ensured theme above; many apps create *next* day theme here)
            $chooseTime = $current->copy()->setTime(21, 0, 0);
            Carbon::setTestNow($chooseTime);
            if ($this->v()) $this->line("   Winner choose-theme window simulated @ " . $chooseTime->toDateTimeString());

            $this->info("Simulated " . $current->toDateString() . " (Mon–Fri day)");
            $simulated++;

            Carbon::setTestNow(null);
            $current->addDay();
        }

        $this->info("Done. Simulated {$simulated} contest day(s).");
        $this->line("Check: homepage Top‑3, weekly views, winners, personal stats.");

        return self::SUCCESS;
    }
}
