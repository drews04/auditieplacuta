<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;


// --- Auth (Laravel default) ---


// --- Header: Acasa ---
use App\Http\Controllers\Header\Acasa\AcasaController;
use App\Http\Controllers\Header\Acasa\ClasamentLunarController;
use App\Http\Controllers\Header\Acasa\EvenimenteController;
use App\Http\Controllers\Header\Acasa\RegulamentController;

// --- Header: Arena ---
use App\Http\Controllers\Header\Arena\ArenaController;
use App\Http\Controllers\Header\Arena\AbilitatiController;
use App\Http\Controllers\Header\Arena\CooldownController;
use App\Http\Controllers\Header\Arena\FolosesteAbilitateController;
use App\Http\Controllers\Header\Arena\AbilitatiDisponibileController;

// --- Header: Clasamente ---
use App\Http\Controllers\Header\Clasamente\ClasamenteController;
use App\Http\Controllers\Header\Clasamente\ClasamentGeneralController;
use App\Http\Controllers\Header\Clasamente\JucatoriDeTopController;
use App\Http\Controllers\Header\Clasamente\JucatoriTriviaDeTopController;
use App\Http\Controllers\Header\Clasamente\TemaLuniiController;

// --- Header: Concurs (legacy routes, to be migrated) ---
use App\Http\Controllers\Header\Concurs\ArhivaTemeController;
use App\Http\Controllers\Header\Concurs\IncarcaMelodieController;

// --- Header: Muzica ---
use App\Http\Controllers\Header\Muzica\MuzicaController;
use App\Http\Controllers\Header\Muzica\ArtistiController;
use App\Http\Controllers\Header\Muzica\GenuriMuzicaleController;
use App\Http\Controllers\Header\Muzica\PlaylistsController;
use App\Http\Controllers\Header\Muzica\NoutatiInMuzicaController;

// --- Header: Misiuni ---
use App\Http\Controllers\Header\Misiuni\MisiuniController;
use App\Http\Controllers\Header\Misiuni\GhicesteMelodiaController;
use App\Http\Controllers\Header\Misiuni\MisiuniZilniceController;
use App\Http\Controllers\Header\Misiuni\ProvocariSaptamanaleController;
use App\Http\Controllers\Header\Misiuni\RecompenseController;

// --- Header: Trivia ---
use App\Http\Controllers\Header\Trivia\IstoricTriviaController;
use App\Http\Controllers\Header\Trivia\JoacaTriviaController;
use App\Http\Controllers\Header\Trivia\RegulamentTriviaController;

// --- User ---
use App\Http\Controllers\User\UserProfileController;
use App\Http\Controllers\User\UserStatisticsController;
use App\Http\Controllers\User\UserSongsController;
use App\Http\Controllers\User\UserTriviaController;
use App\Http\Controllers\User\UserAbilitiesController;
use App\Http\Controllers\User\UserVotesController;
use App\Http\Controllers\User\UserSettingsController;
use App\Http\Controllers\User\DisconnectController;
use App\Http\Controllers\User\UserWinsController;

// --- Core / Concurs / Admin / Misc ---
use App\Http\Controllers\AbilityController;
use App\Http\Controllers\LeaderboardController;
use App\Http\Middleware\AdminOnly;
use App\Http\Controllers\ThemeLikeController;
use App\Http\Controllers\WinnersController;

// --- Concurs (NEW structure) ---
use App\Http\Controllers\Concurs\{ConcursController, UploadController, VoteController, ThemeController, ArchiveController};
use App\Http\Controllers\Concurs\Admin\{CycleController as AdminCycleController, PosterController as AdminPosterController, DisqualifyController};

// --- Forum ---
use App\Http\Controllers\Forum\CategoryController;
use App\Http\Controllers\Forum\ThreadController;
use App\Http\Controllers\Forum\PostController;
use App\Http\Controllers\Forum\ForumLikeController;
use App\Http\Controllers\Forum\NotificationController;

// --- Static / Events ---
use App\Http\Controllers\Static\AboutController;
use App\Http\Controllers\Events\EventsController;

// ───────────────────────────────────────────────────────────────────────────────
// Maintenance / Debug helpers
// ────────────────────────────────────────────────────────────────────────────
Route::get('/_log_reset', function () {
    @file_put_contents(storage_path('logs/laravel.log'), "== reset ".date('c')." ==\n");
    return response("log reset\n", 200)->header('Content-Type','text/plain');
});

Route::get('/_routes_scan', function () {
    $buf = @file(__FILE__) ?: [];
    $text = implode("", $buf);
    preg_match_all('/^use\s+App\\\\Http\\\\Controllers\\\\CustomAuthController;$/m', $text, $m);
    return response("imports_found: ".count($m[0])."\n", 200)
        ->header('Content-Type', 'text/plain');
});

Route::get('/_opcache_reset', function () {
    $ok = function_exists('opcache_reset') && opcache_reset();
    return response($ok ? "opcache: reset\n" : "opcache: not available\n", 200)
        ->header('Content-Type', 'text/plain');
});

Route::get('/_routes_debug', function () {
    $path  = __FILE__;
    $lines = @file($path) ?: [];
    $line121 = isset($lines[120]) ? trim($lines[120]) : 'n/a';
    return response("path: $path\nlines: ".count($lines)."\nline121: $line121\n", 200)
        ->header('Content-Type', 'text/plain');
});

Route::get('/_clear', function () {
    Artisan::call('optimize:clear');
    Artisan::call('config:clear');
    Artisan::call('route:clear');
    Artisan::call('view:clear');
    return response("cleared\n", 200)->header('Content-Type', 'text/plain');
});

Route::get('/_log', function () {
    $path = storage_path('logs/laravel.log');
    $lines = (int) request('lines', 200);
    $lines = max(50, min($lines, 2000));
    if (!File::exists($path)) {
        return response("no log\n", 200)->header('Content-Type', 'text/plain');
    }
    $content = explode("\n", File::get($path));
    $tail = implode("\n", array_slice($content, -$lines));
    return response($tail . "\n", 200)->header('Content-Type', 'text/plain');
});

Route::get('/_ping', fn () => response('pong', 200)->header('Content-Type', 'text/plain'));

// ───────────────────────────────────────────────────────────────────────────────
// Auth (all via CustomAuthController) — names match your Blade files
// ───────────────────────────────────────────────────────────────────────────────


// Login / Logout
Route::get('/login',  [\App\Http\Controllers\CustomAuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [\App\Http\Controllers\CustomAuthController::class, 'login'])->name('login.attempt');

// Logout (POST with CSRF + GET fallback for expired tokens)
Route::post('/logout', function () {
    auth()->logout();
    request()->session()->invalidate();
    request()->session()->regenerateToken();
    return redirect()->route('home')->with('status', 'Te-ai deconectat cu succes.');
})->name('logout');

Route::get('/logout', function () {
    auth()->logout();
    request()->session()->invalidate();
    request()->session()->regenerateToken();
    return redirect()->route('home')->with('status', 'Te-ai deconectat cu succes.');
})->name('logout.get');

// Guest-only flows
Route::middleware('guest')->group(function () {
    // Register
    Route::get('/register',  [\App\Http\Controllers\CustomAuthController::class, 'showRegistrationForm'])->name('register');
    Route::post('/register', [\App\Http\Controllers\CustomAuthController::class, 'register'])->name('register.submit');

    // Email code verify
    Route::get('/verify',  [\App\Http\Controllers\CustomAuthController::class, 'showVerifyForm'])->name('verify.view');
    Route::post('/verify', [\App\Http\Controllers\CustomAuthController::class, 'verify'])->name('verify.code');

    // Forgot / Reset password
    Route::get('/forgot-password',  [\App\Http\Controllers\CustomAuthController::class, 'showForgotPasswordForm'])->name('password.request');
    Route::post('/forgot-password', [\App\Http\Controllers\CustomAuthController::class, 'sendResetCode'])->name('password.email');

    Route::get('/reset-password',  [\App\Http\Controllers\CustomAuthController::class, 'showResetForm'])->name('password.reset.view');
    Route::post('/reset-password', [\App\Http\Controllers\CustomAuthController::class, 'resetPassword'])->name('password.update');
});




// ───────────────────────────────────────────────────────────────────────────────
// Home
// ───────────────────────────────────────────────────────────────────────────────
Route::get('/', [AcasaController::class, 'index'])->name('home');

// ───────────────────────────────────────────────────────────────────────────────
// Acasa
// ───────────────────────────────────────────────────────────────────────────────
Route::get('/clasament-lunar', [ClasamentLunarController::class, 'index'])->name('clasament-lunar');
Route::get('/evenimente',      [EvenimenteController::class, 'index'])->name('evenimente');
Route::get('/regulament',      [RegulamentController::class, 'index'])->name('regulament');

// ───────────────────────────────────────────────────────────────────────────────
// Arena
// ───────────────────────────────────────────────────────────────────────────────
Route::get('/arena',                 [ArenaController::class, 'index'])->name('arena');
Route::get('/abilitati',             [AbilitatiController::class, 'index'])->name('abilitati');
Route::get('/cooldown',              [CooldownController::class, 'index'])->name('cooldown');
Route::get('/foloseste-abilitate',   [FolosesteAbilitateController::class, 'index'])->name('foloseste-abilitate');
Route::get('/abilitati-disponibile', [AbilitatiDisponibileController::class, 'index'])->name('abilitati-disponibile');
Route::get('/abilities', fn () => redirect()->route('abilitati'))->name('abilities.index');

// ───────────────────────────────────────────────────────────────────────────────
// Trivia
// ───────────────────────────────────────────────────────────────────────────────
Route::get('/joaca-trivia',      [JoacaTriviaController::class, 'index'])->name('arena.trivia.joaca-trivia');
Route::get('/regulament-trivia', [RegulamentTriviaController::class, 'index'])->name('arena.trivia.regulament-trivia');
Route::get('/istoric-trivia',    [IstoricTriviaController::class, 'index'])->name('arena.trivia.istoric-trivia');

// ───────────────────────────────────────────────────────────────────────────────
// Misiuni
// ───────────────────────────────────────────────────────────────────────────────
Route::get('/ghiceste-melodia', [GhicesteMelodiaController::class, 'index'])->name('arena.misiuni.ghiceste-melodia');
Route::get('/misiuni-zilnice',  [MisiuniZilniceController::class, 'index'])->name('arena.misiuni.misiuni-zilnice');
Route::get('/provocari',        [ProvocariSaptamanaleController::class, 'index'])->name('arena.misiuni.provocari');
Route::get('/recompense',       [RecompenseController::class, 'index'])->name('arena.misiuni.recompense');
Route::get('/misiuni', fn () => view('misiuni.misiuni'))->name('arena.misiuni.index');

// ───────────────────────────────────────────────────────────────────────────────
// Clasamente
// ───────────────────────────────────────────────────────────────────────────────
Route::get('/clasamente',        [ClasamenteController::class, 'index'])->name('clasamente.index');
Route::get('/clasament-general', [ClasamentGeneralController::class, 'index'])->name('arena.clasamente.clasament-general');
Route::get('/jucatori-de-top',   [JucatoriDeTopController::class, 'index'])->name('arena.clasamente.jucatori-de-top');
Route::get('/jucatori-trivia',   [JucatoriTriviaDeTopController::class, 'index'])->name('arena.clasamente.jucatori-trivia');
Route::get('/tema-lunii',        [TemaLuniiController::class, 'index'])->name('arena.clasamente.tema-lunii');

// ───────────────────────────────────────────────────────────────────────────────
// Muzica
// ───────────────────────────────────────────────────────────────────────────────
Route::get('/muzica',           [MuzicaController::class, 'index'])->name('muzica');
Route::get('/muzica/noutati',   [NoutatiInMuzicaController::class, 'index'])->name('muzica.noutati');
Route::get('/muzica/artisti',   [ArtistiController::class, 'index'])->name('muzica.artisti');
Route::get('/muzica/genuri',    [GenuriMuzicaleController::class, 'index'])->name('muzica.genuri');
Route::get('/muzica/playlists', [PlaylistsController::class, 'index'])->name('muzica.playlists');

// ───────────────────────────────────────────────────────────────────────────────
// Magazin
// ───────────────────────────────────────────────────────────────────────────────
Route::get('/magazin',                     [\App\Http\Controllers\Header\Magazin\MagazinController::class, 'index'])->name('magazin.index');
Route::get('/magazin/premium',             [\App\Http\Controllers\Header\Magazin\PremiumController::class, 'index'])->name('magazin.premium');
Route::get('/magazin/produse-disponibile', [\App\Http\Controllers\Header\Magazin\ProduseDisponibileController::class, 'index'])->name('magazin.produse-disponibile');
Route::get('/magazin/cumpara-apbucksi',    [\App\Http\Controllers\Header\Magazin\CumparaApbucksiController::class, 'index'])->name('magazin.cumpara-apbucksi');

// ───────────────────────────────────────────────────────────────────────────────
// User area (pages)
// ───────────────────────────────────────────────────────────────────────────────
Route::middleware('auth')->prefix('contul-meu')->group(function () {
    Route::get('/profil',     [UserProfileController::class, 'index'])->name('user.user_profile');
    Route::get('/statistici', [UserStatisticsController::class, 'index'])->name('user.statistics');
    Route::get('/melodii',    [UserSongsController::class, 'index'])->name('user.songs');
    Route::get('/trivia',     [UserTriviaController::class, 'index'])->name('user.user-trivia');
    Route::get('/abilitati',  [UserAbilitiesController::class, 'index'])->name('user.abilities');
    Route::get('/voturi',     [UserVotesController::class, 'index'])->name('user.votes');
    Route::get('/setari',     [UserSettingsController::class, 'index'])->name('user.settings');
    Route::get('/deconectare',[DisconnectController::class, 'index'])->name('user.disconnect');
});

// Settings POSTs
Route::post('/setari/email',  [UserSettingsController::class, 'updateEmail'])->name('user.settings.updateEmail');
Route::post('/setari/parola', [UserSettingsController::class, 'updatePassword'])->name('user.settings.updatePassword');

// Profile photo upload
Route::post('/profil/upload-photo', [UserProfileController::class, 'uploadPhoto'])->name('user.profile.uploadPhoto')->middleware('auth');

// ═══════════════════════════════════════════════════════════════════════════════
// CONCURS SYSTEM
// ═══════════════════════════════════════════════════════════════════════════════

// ─────────────────────────────────────────────────────────────────────────────
// PUBLIC ROUTES
// ─────────────────────────────────────────────────────────────────────────────

// Main page (dual upload/vote view)
Route::get('/concurs', [ConcursController::class, 'index'])->name('concurs');

// Dedicated pages
Route::get('/concurs/p/upload', [UploadController::class, 'page'])->name('concurs.upload.page');
Route::get('/concurs/p/vote',   [VoteController::class, 'page'])->name('concurs.vote.page');

// Actions (require auth)
Route::middleware('auth')->group(function () {
    Route::post('/concurs/upload', [UploadController::class, 'store'])->name('concurs.upload');
    Route::post('/concurs/vote',   [VoteController::class, 'store'])->name('concurs.vote');
});

// Winner theme selection (1-hour window after 20:00)
Route::middleware('auth')->group(function () {
    Route::get('/concurs/alege-tema',  [ThemeController::class, 'create'])->name('concurs.alege-tema.create');
    Route::post('/concurs/alege-tema', [ThemeController::class, 'store'])->name('concurs.alege-tema.store');
    
});

// Theme likes
Route::middleware('auth')->group(function () {
    Route::post('/theme/like', [ThemeLikeController::class, 'like'])->name('theme.like');
});

// Archive
Route::get('/concurs/arhiva', [ArchiveController::class, 'index'])->name('concurs.arhiva');
Route::get('/concurs/arhiva/{cycleId}', [ArchiveController::class, 'show'])->name('concurs.arhiva.show');
Route::get('/concurs/arhiva/navigate/{direction}', [ArchiveController::class, 'navigate'])->name('concurs.arhiva.navigate');

// Legacy redirects
Route::get('/concurs/incarca-melodie', fn () => redirect()->to(route('concurs') . '#concurs-submit'))->name('concurs.incarca-melodie');
Route::get('/concurs/melodiile-zilei', fn () => redirect()->route('concurs'))->name('concurs.melodiile-zilei');
Route::get('/concurs/voteaza', fn () => redirect()->to(route('concurs') . '#concurs-vote'))->name('concurs.voteaza');
Route::get('/concurs/rezultate', fn () => redirect()->route('concurs'))->name('concurs.rezultate');

// Arhivă teme page (keep for now)
Route::get('/concurs/arhiva-teme', [ArhivaTemeController::class, 'index'])->name('concurs.arhiva-teme');

// ─────────────────────────────────────────────────────────────────────────────
// ADMIN ROUTES
// ─────────────────────────────────────────────────────────────────────────────

Route::middleware(['auth', AdminOnly::class])->group(function () {
    // Dashboard & cycle management
    Route::get('/admin/concurs', [AdminCycleController::class, 'dashboard'])->name('admin.concurs');
    Route::post('/concurs/start', [AdminCycleController::class, 'start'])->name('concurs.start');
    Route::get('/admin/concurs/health', [AdminCycleController::class, 'health'])->name('admin.concurs.health');
    Route::post('/admin/concurs/close-20', [AdminCycleController::class, 'close'])->name('admin.concurs.close20');

    // Poster management
    Route::post('/admin/concurs/poster', [AdminPosterController::class, 'store'])->name('admin.concurs.poster.store');
    Route::delete('/admin/concurs/poster', [AdminPosterController::class, 'destroy'])->name('admin.concurs.poster.destroy');
    
    // Song disqualification
    Route::post('/concurs/admin/disqualify/{songId}', [DisqualifyController::class, 'toggle'])->name('concurs.admin.disqualify');
});

// ───────────────────────────────────────────────────────────────────────────────
// Leaderboards
// ───────────────────────────────────────────────────────────────────────────────
Route::get('/leaderboard/home', [LeaderboardController::class, 'home'])->name('leaderboard.home');
Route::get('/clasament',        [LeaderboardController::class, 'index'])->name('leaderboard.index');

Route::get('/clasament/positions', fn () => redirect()->route('leaderboard.index', ['scope' => 'positions']))->name('leaderboard.positions');
Route::get('/clasament/all-time', fn () => redirect()->route('leaderboard.index', ['scope' => 'alltime']))->name('leaderboard.alltime');
Route::get('/clasament/monthly', fn () => redirect()->route('leaderboard.index', ['scope' => 'monthly']))->name('leaderboard.monthly');
Route::get('/clasament/yearly',  fn () => redirect()->route('leaderboard.index', ['scope' => 'yearly'])) ->name('leaderboard.yearly');

// ───────────────────────────────────────────────────────────────────────────────
// Personal stats
// ───────────────────────────────────────────────────────────────────────────────
Route::middleware('auth')->group(function () {
    Route::get('/me/wins',             [UserWinsController::class, 'index'])->name('me.wins');
    Route::get('/users/{userId}/wins', [UserWinsController::class, 'index'])->name('users.wins');
});

// ───────────────────────────────────────────────────────────────────────────────
// Misc
// ───────────────────────────────────────────────────────────────────────────────
Route::get('/test-mail', function () {
    Mail::raw('This is a test email from Brevo SMTP.', function ($message) {
        $message->to('tiagomota121@yahoo.com')->subject('Brevo SMTP Test');
    });
    return 'Test email sent!';
});

// Melodii Castigatoare (TODO: create controller for this)
// Route::get('/concurs/melodii-castigatoare', [MelodiiCastigatoareController::class, 'index'])
//     ->name('concurs.melodii-castigatoare');

// Theme likes (legacy route, TODO: consolidate)
Route::middleware('auth')->group(function () {
    Route::post('/themes/like/toggle', [ThemeLikeController::class, 'toggle'])->name('themes.like.toggle');
});

// ───────────────────────────────────────────────────────────────────────────────
// Forum Routes
// ───────────────────────────────────────────────────────────────────────────────
Route::prefix('forum')->name('forum.')->group(function () {
    // Public
    Route::get('/',                  [CategoryController::class, 'index'])->name('home');
    Route::get('/threads',           [ThreadController::class, 'index'])->name('threads.index');
    Route::get('/c/{category:slug}', [ThreadController::class, 'index'])->name('categories.show');
    Route::get('/t/{thread:slug}',   [ThreadController::class, 'show'])->name('threads.show');

    // Auth-only actions
    Route::middleware(['auth', 'throttle:30,1'])->group(function () {
        // Create
        Route::get('/threads/create', [ThreadController::class, 'create'])->name('threads.create');
        Route::post('/threads',       [ThreadController::class, 'store'])->name('threads.store');
        Route::post('/posts',         [PostController::class,   'store'])->name('posts.store');

        // Thread edit/update/delete
        Route::get('/t/{thread:slug}/edit', [ThreadController::class, 'edit'])->name('threads.edit');
        Route::put('/t/{thread:slug}',      [ThreadController::class, 'update'])->name('threads.update');
        Route::delete('/t/{thread:slug}',   [ThreadController::class, 'destroy'])->name('threads.destroy');

        // Post edit/update/delete
        Route::get('/p/{post}/edit',  [PostController::class, 'edit'])->whereNumber('post')->name('posts.edit');
        Route::put('/p/{post}',       [PostController::class, 'update'])->whereNumber('post')->name('posts.update');
        Route::delete('/p/{post}',    [PostController::class, 'destroy'])->whereNumber('post')->name('posts.destroy');

        // Likes
        Route::post('/like/thread/{thread:slug}', [ForumLikeController::class, 'toggleThread'])->name('likes.thread.toggle');
        Route::post('/like/post/{post}',          [ForumLikeController::class, 'togglePost'])->whereNumber('post')->name('likes.post.toggle');
    });
});

// Alerts API (auth only)
Route::prefix('forum/alerts')->name('forum.alerts.')->middleware('auth')->group(function () {
    Route::get('/unread-count',  [NotificationController::class, 'unreadCount'])->name('unread-count');
    Route::get('/unread-detail', [NotificationController::class, 'unreadDetail'])->name('unread-detail');
    Route::post('/ack-shown',    [NotificationController::class, 'ackShown'])->name('ack');
    Route::post('/mark-seen',    [NotificationController::class, 'markSeen'])->name('mark-seen');
});

// ───────────────────────────────────────────────────────────────────────────────
// Static / Events
// ───────────────────────────────────────────────────────────────────────────────
Route::get('/despre-noi', [AboutController::class, 'index'])->name('about');

Route::prefix('evenimente')->as('events.')->group(function () {
    Route::get('/', [EventsController::class, 'index'])->name('index'); // list events (public)
    Route::middleware('auth')->group(function () {
        Route::get('/create', [EventsController::class, 'create'])->name('create');
        Route::post('/', [EventsController::class, 'store'])->name('store');
    });
});

// Winners
Route::get('/winners', [WinnersController::class, 'index'])->name('winners.index');


// ───────────────────────────────────────────────────────────────────────────────
// Noutati in muzica
// ───────────────────────────────────────────────────────────────────────────────
Route::prefix('muzica/noutati')->group(function () {
    Route::get('/', [NoutatiInMuzicaController::class, 'index'])->name('releases.index'); // current week
    Route::get('{week_key}', [NoutatiInMuzicaController::class, 'week'])
        ->where('week_key', '\d{4}W\d{2}')
        ->name('releases.week'); // specific week e.g. 2025W39
    Route::get('r/{slug}', [NoutatiInMuzicaController::class, 'show'])->name('releases.show'); // single “read more”
});


// ───────────────────────────────────────────────────────────────────────────────
// Utilities
// ───────────────────────────────────────────────────────────────────────────────
Route::get('/make-storage-link', function () {
    if (!Auth::check()) { abort(403); }
    $target = storage_path('app/public');
    $link   = public_path('storage');
    if (is_link($link) || file_exists($link)) return 'public/storage already exists';
    return @symlink($target, $link) ? 'created' : 'failed: ' . (error_get_last()['message'] ?? 'unknown');
});



