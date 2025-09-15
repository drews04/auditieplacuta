<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

// ───────────────────────────────────────────────────────────────────────────────
// Controllers (grouped to avoid scatter)
// ───────────────────────────────────────────────────────────────────────────────
use App\Http\Controllers\Header\Acasa\AcasaController;
use App\Http\Controllers\Header\Acasa\ClasamentLunarController;
use App\Http\Controllers\Header\Acasa\EvenimenteController;
use App\Http\Controllers\Header\Acasa\RegulamentController;

use App\Http\Controllers\Header\Arena\ArenaController;
use App\Http\Controllers\Header\Arena\AbilitatiController;
use App\Http\Controllers\Header\Arena\CooldownController;
use App\Http\Controllers\Header\Arena\FolosesteAbilitateController;
use App\Http\Controllers\Header\Arena\AbilitatiDisponibileController;

use App\Http\Controllers\Header\Clasamente\ClasamenteController;
use App\Http\Controllers\Header\Clasamente\ClasamentGeneralController;
use App\Http\Controllers\Header\Clasamente\JucatoriDeTopController;
use App\Http\Controllers\Header\Clasamente\JucatoriTriviaDeTopController;
use App\Http\Controllers\Header\Clasamente\TemaLuniiController;

use App\Http\Controllers\Header\Concurs\ConcursController;
use App\Http\Controllers\Header\Concurs\ArhivaTemeController;
use App\Http\Controllers\Header\Concurs\IncarcaMelodieController;
use App\Http\Controllers\Header\Concurs\MelodiileZileiController;
use App\Http\Controllers\Header\Concurs\RezultateController;
use App\Http\Controllers\Header\Concurs\VoteazaController;

use App\Http\Controllers\Header\Muzica\MuzicaController;
use App\Http\Controllers\Header\Muzica\ArtistiController;
use App\Http\Controllers\Header\Muzica\GenuriMuzicaleController;
use App\Http\Controllers\Header\Muzica\NoutatiInMuzicaController;
use App\Http\Controllers\Header\Muzica\PlaylistsController;

use App\Http\Controllers\Header\Misiuni\MisiuniController;
use App\Http\Controllers\Header\Misiuni\GhicesteMelodiaController;
use App\Http\Controllers\Header\Misiuni\MisiuniZilniceController;
use App\Http\Controllers\Header\Misiuni\ProvocariSaptamanaleController;
use App\Http\Controllers\Header\Misiuni\RecompenseController;

use App\Http\Controllers\Header\Trivia\IstoricTriviaController;
use App\Http\Controllers\Header\Trivia\JoacaTriviaController;
use App\Http\Controllers\Header\Trivia\RegulamentTriviaController;

use App\Http\Controllers\User\UserProfileController;
use App\Http\Controllers\User\UserStatisticsController;
use App\Http\Controllers\User\UserSongsController;
use App\Http\Controllers\User\UserTriviaController;
use App\Http\Controllers\User\UserAbilitiesController;
use App\Http\Controllers\User\UserVotesController;
use App\Http\Controllers\User\UserSettingsController;
use App\Http\Controllers\User\DisconnectController;

use App\Http\Controllers\AbilityController;

use App\Http\Controllers\SongController;
use App\Http\Controllers\ConcursTemaController;

use App\Http\Controllers\LeaderboardController;

use App\Http\Controllers\Admin\ConcursAdminController;
use App\Http\Middleware\AdminOnly;

use App\Http\Controllers\CustomAuthController;

use App\Http\Controllers\User\UserWinsController;

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
Route::get('/arena',                  [ArenaController::class, 'index'])->name('arena');
Route::get('/abilitati',              [AbilitatiController::class, 'index'])->name('abilitati');
Route::get('/cooldown',               [CooldownController::class, 'index'])->name('cooldown');
Route::get('/foloseste-abilitate',    [FolosesteAbilitateController::class, 'index'])->name('foloseste-abilitate');
Route::get('/abilitati-disponibile',  [AbilitatiDisponibileController::class, 'index'])->name('abilitati-disponibile');

// ───────────────────────────────────────────────────────────────────────────────
// Trivia
// ───────────────────────────────────────────────────────────────────────────────
Route::get('/joaca-trivia',        [JoacaTriviaController::class, 'index'])->name('arena.trivia.joaca-trivia');
Route::get('/regulament-trivia',   [RegulamentTriviaController::class, 'index'])->name('arena.trivia.regulament-trivia');
Route::get('/istoric-trivia',      [IstoricTriviaController::class, 'index'])->name('arena.trivia.istoric-trivia');

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
Route::get('/tema-lunii', [TemaLuniiController::class, 'index'])->name('arena.clasamente.tema-lunii');

// ───────────────────────────────────────────────────────────────────────────────
// Muzica
// ───────────────────────────────────────────────────────────────────────────────
Route::get('/muzica',          [MuzicaController::class, 'index'])->name('muzica');
Route::get('/muzica/noutati',  [NoutatiInMuzicaController::class, 'index'])->name('muzica.noutati');
Route::get('/muzica/artisti',  [ArtistiController::class, 'index'])->name('muzica.artisti');
Route::get('/muzica/genuri',   [GenuriMuzicaleController::class, 'index'])->name('muzica.genuri');
Route::get('/muzica/playlists',[PlaylistsController::class, 'index'])->name('muzica.playlists');

// ───────────────────────────────────────────────────────────────────────────────
// Magazin
// ───────────────────────────────────────────────────────────────────────────────
Route::get('/magazin',                   [\App\Http\Controllers\Header\Magazin\MagazinController::class, 'index'])->name('magazin.index');
Route::get('/magazin/premium',           [\App\Http\Controllers\Header\Magazin\PremiumController::class, 'index'])->name('magazin.premium');
Route::get('/magazin/produse-disponibile', [\App\Http\Controllers\Header\Magazin\ProduseDisponibileController::class, 'index'])->name('magazin.produse-disponibile');
Route::get('/magazin/cumpara-apbucksi',  [\App\Http\Controllers\Header\Magazin\CumparaApbucksiController::class, 'index'])->name('magazin.cumpara-apbucksi');

// ───────────────────────────────────────────────────────────────────────────────
// Auth
// ───────────────────────────────────────────────────────────────────────────────
Route::get('/login',  [CustomAuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [CustomAuthController::class, 'login'])->middleware('throttle:5,1')->name('login.attempt');

Route::get('/register',  [CustomAuthController::class, 'showRegistrationForm'])->name('register');
Route::post('/register', [CustomAuthController::class, 'register'])->name('register');

Route::get('/verify',  [CustomAuthController::class, 'showVerifyForm'])->name('verify.view');
Route::post('/verify', [CustomAuthController::class, 'verify'])->name('verify.code');

Route::get('/password/change',  [CustomAuthController::class, 'showChangePasswordForm'])->middleware('auth')->name('password.change.form');
Route::post('/password/change', [CustomAuthController::class, 'changePassword'])->middleware('auth')->name('password.change');

Route::get('/forgot-password',  [CustomAuthController::class, 'showForgotPasswordForm'])->name('password.request');
Route::post('/forgot-password', [CustomAuthController::class, 'sendResetCode'])->name('password.email');

Route::get('/reset-password',  [CustomAuthController::class, 'showResetForm'])->name('password.reset.view');
Route::post('/reset-password', [CustomAuthController::class, 'resetPassword'])->name('password.update');

Route::post('/password/send-code', [CustomAuthController::class, 'sendResetCode'])->name('password.send.code');

Route::view('/register-success', 'auth.register-success')->name('register.success');

// logout (single definition)
Route::post('/logout', function () {
    Auth::logout();
    return redirect('/');
})->name('logout');

// ───────────────────────────────────────────────────────────────────────────────
// User area
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

// settings POSTs
Route::post('/setari/email',  [UserSettingsController::class, 'updateEmail'])->name('user.settings.updateEmail');
Route::post('/setari/parola', [UserSettingsController::class, 'updatePassword'])->name('user.settings.updatePassword');

// abilities list
Route::get('/abilitati', [AbilityController::class, 'index'])->name('abilities.index');





// ──────────────────────────────────────────────
// Concurs — hub + functional pages + archive
// ──────────────────────────────────────────────



use App\Http\Controllers\ConcursArchiveController;
use App\Http\Controllers\WinnersController;

//
// Hub (main entry)
//
Route::get('/concurs', [SongController::class, 'hub'])->name('concurs');

Route::prefix('concurs')->group(function () {
    // Phase pages
    Route::get('/p/upload', [SongController::class, 'uploadPage'])->name('concurs.upload.page');
    Route::get('/p/vote',   [SongController::class, 'votePage'])->name('concurs.vote.page');

    // Actions
    Route::post('/upload',  [SongController::class, 'uploadSong'])->name('concurs.upload');
    Route::post('/vote',    [SongController::class, 'voteForSong'])->name('concurs.vote');

    // Helpers
    Route::get('/songs/today', [SongController::class, 'todayList'])->name('concurs.songs.today');
    Route::get('/versus',      [SongController::class, 'versus'])->name('concurs.versus');

    // Winner picks next theme
    Route::get('/alege-tema',  [ConcursTemaController::class, 'create'])->name('concurs.alege-tema');
    Route::post('/alege-tema', [ConcursTemaController::class, 'store'])->name('concurs.alege-tema.store');

    // Archive
    Route::get('/arhiva',                      [ConcursArchiveController::class, 'index'])->name('concurs.arhiva');
    Route::get('/arhiva/{date}',               [ConcursArchiveController::class, 'show'])->name('concurs.arhiva.show');
    Route::get('/arhiva/{date}/voters/{song}', [ConcursArchiveController::class, 'votersJson'])->name('concurs.arhiva.voters');
});

//
// Winners list (global)
//
Route::get('/winners', [WinnersController::class, 'index'])->name('winners.index');

//
// Admin (Control Cave)
//
Route::prefix('admin')->middleware('admin')->group(function () {
    Route::get('/concurs',        [ConcursAdminController::class, 'dashboard'])->name('admin.concurs');
    Route::post('/concurs/start', [ConcursAdminController::class, 'start'])->name('concurs.start');
});




// Admin Concurs dashboard + Start (protected)
Route::middleware(['auth', AdminOnly::class])->group(function () {
    Route::get('/admin/concurs',  [ConcursAdminController::class, 'dashboard'])->name('admin.concurs');

    // safety throttle: max 3 starts/min
    Route::post('/concurs/start', [ConcursAdminController::class, 'start'])
        ->middleware('throttle:3,1')
        ->name('concurs.start');

    // optional picker UI if you use it
    Route::get('/admin/concurs/alege-tema', [ConcursAdminController::class, 'pickTheme'])
        ->name('admin.concurs.pick');
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

//Arhiva Concursuri


Route::get('/concurs/arhiva', [ConcursArchiveController::class, 'index'])
    ->name('concurs.arhiva');

Route::get('/concurs/arhiva/{date}', [ConcursArchiveController::class, 'show'])
    ->where('date', '\d{4}-\d{2}-\d{2}')
    ->name('concurs.arhiva.show');
   

Route::get('/concurs/arhiva/{date}/voters/{song}', [ConcursArchiveController::class, 'votersJson'])
    ->where(['date' => '\d{4}-\d{2}-\d{2}', 'song' => '\d+'])
    ->name('concurs.arhiva.voters');

//Molodii Castigatoare
//use App\Http\Controllers\Header\Concurs\MelodiiCastigatoareController;



    use App\Http\Controllers\ThemeLikeController;
    use App\Http\Middleware\VerifyCsrfToken;
    
    Route::post('/themes/like/toggle', [ThemeLikeController::class, 'toggle'])
    ->name('themes.like.toggle')
    ->middleware(['auth', 'throttle:20,1']);




// ───────────────────────────────────────────────────────────────────────────────
// Forum Routes
// ───────────────────────────────────────────────────────────────────────────────
use App\Http\Controllers\Forum\CategoryController;
use App\Http\Controllers\Forum\ThreadController;
use App\Http\Controllers\Forum\PostController;
use App\Http\Controllers\Forum\ForumLikeController;
use App\Http\Controllers\Forum\NotificationController;


// ───────────────────────────────────────────────────────────────────────────────
// Static Pages
// ───────────────────────────────────────────────────────────────────────────────
use App\Http\Controllers\Static\AboutController;
Route::get('/despre-noi', [AboutController::class, 'index'])->name('about');

// ───────────────────────────────────────────────────────────────────────────────
// Events Routes
// ───────────────────────────────────────────────────────────────────────────────
use App\Http\Controllers\Events\EventsController;

Route::prefix('evenimente')->as('events.')->group(function () {
    Route::get('/', [EventsController::class, 'index'])->name('index');           // list events (public)
    Route::middleware('auth')->group(function () {
        Route::get('/create', [EventsController::class, 'create'])->name('create');  // form (admin/editor later)
        Route::post('/', [EventsController::class, 'store'])->name('store');         // save
    });
});

// ---------------------------------------
// Forum routes
// ---------------------------------------

// Forum pages (NO MarkForumSeen here; the Kernel web group middleware stamps forum_seen_at)
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
});
// -------- End forum routes --------
