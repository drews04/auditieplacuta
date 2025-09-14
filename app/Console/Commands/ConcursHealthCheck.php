<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

class ConcursHealthCheck extends Command
{
    protected $signature = 'concurs:health';
    protected $description = 'Deep health check for Concurs (DB constraints, routes, scheduler, commands)';

    public function handle(): int
    {
        $ok = true;

        $this->info('== Concurs Health Check ==');

        // 1) DB tables exist
        $needTables = ['contest_cycles','songs','votes','winners'];
        foreach ($needTables as $t) {
            $exists = DB::selectOne("SELECT COUNT(*) c FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = ?", [$t]);
            $this->line(sprintf('[DB] table %-18s : %s', $t, $exists && $exists->c ? 'OK' : 'MISSING'));
            $ok = $ok && (bool)($exists && $exists->c);
        }

        // 2) DB uniques we rely on
        $uniq = [
            ['songs','songs_user_cycle_unique'],
            ['songs','songs_cycle_youtube_unique'],
            ['votes','votes_user_cycle_unique'],
            ['votes','votes_user_tiebreak_unique'],
        ];
        foreach ($uniq as [$table,$idx]) {
            $exists = DB::selectOne("
                SELECT 1 x FROM information_schema.statistics
                WHERE table_schema = DATABASE() AND table_name = ? AND index_name = ? LIMIT 1
            ", [$table,$idx]);
            $this->line(sprintf('[DB] index %-28s on %-12s : %s', $idx, $table, $exists ? 'OK' : 'MISSING'));
            $ok = $ok && (bool)$exists;
        }

        // 3) Required routes
        $routes = [
            ['GET','/concurs'],
            ['GET','/concurs/p/upload'],
            ['GET','/concurs/p/vote'],
            ['POST','/concurs/upload'],       // ajax upload
            ['POST','/concurs/vote'],         // ajax vote
        ];
        foreach ($routes as [$m,$uri]) {
            $found = collect(Route::getRoutes())->first(function ($r) use ($m,$uri) {
                return in_array($m, $r->methods()) && '/'.$r->uri() === $uri;
            });
            $this->line(sprintf('[Route] %-5s %-25s : %s', $m, $uri, $found ? 'OK' : 'MISSING'));
            $ok = $ok && (bool)$found;
        }

        // 4) Required commands registered
        $cmds = ['concurs:declare-winner','concurs:resolve-versus','concurs:fallback-theme'];
        foreach ($cmds as $c) {
            $found = app('Illuminate\Contracts\Console\Kernel')->all()[$c] ?? null;
            $this->line(sprintf('[Cmd] %-24s : %s', $c, $found ? 'OK' : 'MISSING'));
            $ok = $ok && (bool)$found;
        }

        // 5) Scheduler — make sure entries exist (we can’t see cron, but we can see app schedule)
        $schedule = [];
        try {
            $out = shell_exec(PHP_BINARY.' artisan schedule:list');
            $schedule = is_string($out) ? $out : '';
        } catch (\Throwable $e) { /* shared hosting may block */ }

        $needSched = ['concurs:declare-winner','concurs:resolve-versus','award-points','concurs:fallback-theme'];
        foreach ($needSched as $needle) {
            $has = is_string($schedule) && str_contains($schedule, $needle);
            $this->line(sprintf('[Sched] %-24s : %s', $needle, $has ? 'OK' : 'MISSING'));
            $ok = $ok && $has;
        }

        // 6) Controller guards sanity (weekend redirects)
        $this->line('[Guards] weekend redirect logic expected in SongController::uploadPage() / votePage()');

        $this->newLine();
        $ok ? $this->info('✅ Concurs looks READY.') : $this->error('❌ Concurs has issues. Scan the lines above.');
        return $ok ? self::SUCCESS : self::FAILURE;
    }
}
