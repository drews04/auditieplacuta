<?php

use Illuminate\Support\Facades\Auth;

Route::post('/logout', function () {
    Auth::logout();
    return redirect('/');
})->name('logout');

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Header\Acasa\AcasaController;

Route::get('/', [AcasaController::class, 'index'])->name('home');
//Acasa
use App\Http\Controllers\Header\Acasa\ClasamentLunarController;
use App\Http\Controllers\Header\Acasa\EvenimenteController;
use App\Http\Controllers\Header\Acasa\RegulamentController;
//Arena
use App\Http\Controllers\Header\Arena\ArenaController;
use App\Http\Controllers\Header\Arena\AbilitatiController;
use App\Http\Controllers\Header\Arena\CooldownController;
use App\Http\Controllers\Header\Arena\FolosesteAbilitateController;
use App\Http\Controllers\Header\Arena\AbilitatiDisponibileController;
//Clasamente 
use App\Http\Controllers\Header\Clasamente\ClasamenteController;
use App\Http\Controllers\Header\Clasamente\ClasamentGeneralController;
use App\Http\Controllers\Header\Clasamente\JucatoriDeTopController;
use App\Http\Controllers\Header\Clasamente\JucatoriTriviaDeTopController;
use App\Http\Controllers\Header\Clasamente\TemaLuniiController;
//Concurs
use App\Http\Controllers\Header\Concurs\ConcursController;
use App\Http\Controllers\Header\Concurs\ArhivaTemeController;
use App\Http\Controllers\Header\Concurs\IncarcaMelodieController;
use App\Http\Controllers\Header\Concurs\MelodiileZileiController;
use App\Http\Controllers\Header\Concurs\RezultateController;
use App\Http\Controllers\Header\Concurs\VoteazaController;
//Magazin
use App\Http\Controllers\Header\Magazin\MagazinController;
use App\Http\Controllers\Header\Magazin\PremiumController;
use App\Http\Controllers\Header\Magazin\ProduseDisponibileController;
use App\Http\Controllers\Header\Magazin\CumparaApbucksiController;
//Misiuni
use App\Http\Controllers\Header\Misiuni\MisiuniController;
use App\Http\Controllers\Header\Misiuni\GhicesteMelodiaController;
use App\Http\Controllers\Header\Misiuni\MisiuniZilniceController;
use App\Http\Controllers\Header\Misiuni\ProvocariSaptamanaleController;
use App\Http\Controllers\Header\Misiuni\RecompenseController;
//Muzica
use App\Http\Controllers\Header\Muzica\MuzicaController;
use App\Http\Controllers\Header\Muzica\ArtistiController;
use App\Http\Controllers\Header\Muzica\GenuriMuzicaleController;
use App\Http\Controllers\Header\Muzica\NoutatiInMuzicaController;
use App\Http\Controllers\Header\Muzica\PlaylistsController;
//Trivia

use App\Http\Controllers\Header\Trivia\IstoricTriviaController;
use App\Http\Controllers\Header\Trivia\JoacaTriviaController;
use App\Http\Controllers\Header\Trivia\RegulamentTriviaController;


//User

use App\Http\Controllers\User\UserProfileController;
use App\Http\Controllers\User\UserStatisticsController;
use App\Http\Controllers\User\UserSongsController;
use App\Http\Controllers\User\UserTriviaController;
use App\Http\Controllers\User\UserAbilitiesController;
use App\Http\Controllers\User\UserVotesController;
use App\Http\Controllers\User\UserSettingsController;
use App\Http\Controllers\User\DisconnectController;

//Ablitati disponibile
use App\Http\Controllers\AbilityController;

//Concurs 
use App\Http\Controllers\SongController;

//Concurs - algege tema
use App\Http\Controllers\ConcursTemaController;

//Leaderbords
use App\Http\Controllers\LeaderboardController;

// Home Controller
use App\Http\Controllers\HomeController;
// Admin controller for testing the competition
use App\Http\Controllers\Admin\ConcursTestController;


//Acasa - Routes
Route::get('/clasament-lunar', [ClasamentLunarController::class, 'index'])->name('clasament-lunar');
Route::get('/evenimente', [EvenimenteController::class, 'index'])->name('evenimente');
Route::get('/regulament', [RegulamentController::class, 'index'])->name('regulament');
//Arena
Route::get('/arena', [ArenaController::class, 'index'])->name('arena');
Route::get('/abilitati', [AbilitatiController::class, 'index'])->name('abilitati');
Route::get('/cooldown', [CooldownController::class, 'index'])->name('cooldown');
Route::get('/foloseste-abilitate', [FolosesteAbilitateController::class, 'index'])->name('foloseste-abilitate');
Route::get('/abilitati-disponibile', [AbilitatiDisponibileController::class, 'index'])->name('abilitati-disponibile');

//Trivia - Routes
Route::get('/joaca-trivia', [JoacaTriviaController::class, 'index'])->name('arena.trivia.joaca-trivia');
Route::get('/regulament-trivia', [RegulamentTriviaController::class, 'index'])->name('arena.trivia.regulament-trivia');
Route::get('/istoric-trivia', [IstoricTriviaController::class, 'index'])->name('arena.trivia.istoric-trivia');

//Misiuni
Route::get('/ghiceste-melodia', [GhicesteMelodiaController::class, 'index'])->name('arena.misiuni.ghiceste-melodia');
Route::get('/misiuni-zilnice', [MisiuniZilniceController::class, 'index'])->name('arena.misiuni.misiuni-zilnice');
Route::get('/provocari', [ProvocariSaptamanaleController::class, 'index'])->name('arena.misiuni.provocari');
Route::get('/recompense', [RecompenseController::class, 'index'])->name('arena.misiuni.recompense');
Route::get('/misiuni', function () {
    return view('misiuni.misiuni'); // or whatever your file is
})->name('arena.misiuni.index');

//Clasamente
Route::get('/clasamente', [ClasamenteController::class, 'index'])->name('clasamente.index');
Route::get('/clasament-general', [ClasamentGeneralController::class, 'index'])->name('arena.clasamente.clasament-general');
Route::get('/jucatori-de-top', [JucatoriDeTopController::class, 'index'])->name('arena.clasamente.jucatori-de-top');
Route::get('/jucatori-trivia', [JucatoriTriviaDeTopController::class, 'index'])->name('arena.clasamente.jucatori-trivia');
Route::get('/tema-lunii', [TemaLuniiController::class, 'index'])->name('arena.clasamente.tema-lunii');

//Concurs
Route::get('/concurs', [ConcursController::class, 'index'])->name('concurs.index');
Route::get('/concurs/incarca-melodie', [IncarcaMelodieController::class, 'index'])->name('concurs.incarca-melodie');
Route::get('/concurs/melodiile-zilei', [MelodiileZileiController::class, 'index'])->name('concurs.melodiile-zilei');
Route::get('/concurs/voteaza', [VoteazaController::class, 'index'])->name('concurs.voteaza');
Route::get('/concurs/rezultate', [RezultateController::class, 'index'])->name('concurs.rezultate');
Route::get('/concurs/arhiva-teme', [ArhivaTemeController::class, 'index'])->name('concurs.arhiva-teme');

//Muzica
Route::get('/muzica', [MuzicaController::class, 'index'])->name('muzica');
Route::get('/muzica/noutati', [NoutatiInMuzicaController::class, 'index'])->name('muzica.noutati');
Route::get('/muzica/artisti', [ArtistiController::class, 'index'])->name('muzica.artisti');
Route::get('/muzica/genuri', [GenuriMuzicaleController::class, 'index'])->name('muzica.genuri');
Route::get('/muzica/playlists', [PlaylistsController::class, 'index'])->name('muzica.playlists');

//Magazin 
Route::get('/magazin', [MagazinController::class, 'index'])->name('magazin.index');
Route::get('/magazin/premium', [PremiumController::class, 'index'])->name('magazin.premium');
Route::get('/magazin/produse-disponibile', [ProduseDisponibileController::class, 'index'])->name('magazin.produse-disponibile');
Route::get('/magazin/cumpara-apbucksi', [CumparaApbucksiController::class, 'index'])->name('magazin.cumpara-apbucksi');

// Auth Routes
use App\Http\Controllers\CustomAuthController;

Route::get('/login', [CustomAuthController::class, 'showLoginForm'])->name('login.form');
Route::post('/login', [CustomAuthController::class, 'login'])->name('login');

Route::get('/register', [CustomAuthController::class, 'showRegistrationForm'])->name('register.form');
Route::post('/register', [CustomAuthController::class, 'register'])->name('register');
//User


Route::middleware('auth')->prefix('contul-meu')->group(function () {
    Route::get('/profil', [UserProfileController::class, 'index'])->name('user.user_profile');
    Route::get('/statistici', [UserStatisticsController::class, 'index'])->name('user.statistics');
    Route::get('/melodii', [UserSongsController::class, 'index'])->name('user.songs');
    Route::get('/trivia', [UserTriviaController::class, 'index'])->name('user.user-trivia');
    Route::get('/abilitati', [UserAbilitiesController::class, 'index'])->name('user.abilities');
    Route::get('/voturi', [UserVotesController::class, 'index'])->name('user.votes');
    Route::get('/setari', [UserSettingsController::class, 'index'])->name('user.settings');
    Route::get('/deconectare', [DisconnectController::class, 'index'])->name('user.disconnect');
    Route::get('/profil', [UserProfileController::class, 'index'])->name('user.user_profile');
});

//Log-out 


Route::post('/logout', function () {
    Auth::logout();
    return redirect('/');
})->name('logout');

//Brevo test route
use Illuminate\Support\Facades\Mail;


Route::get('/test-mail', function () {
    Mail::raw('This is a test email from Brevo SMTP.', function ($message) {
        $message->to('tiagomota121@yahoo.com')
                ->subject('Brevo SMTP Test');
    });

    return 'Test email sent!';
});

//Verify 
Route::post('/verify', [CustomAuthController::class, 'verify'])->name('verify.code');

// ✅ Show verify form again (after wrong code)
Route::get('/verify', function () {
    $email = session('registration_data.email') ?? null;
    return view('auth.verify', ['email' => $email]);
})->name('verify.view');

// ==================== AUTENTIFICARE ====================
// ✅ Show login form
Route::get('/login', [CustomAuthController::class, 'showLoginForm'])->name('login');

// ✅ Handle login with rate limiting: 5 tries per minute
Route::post('/login', [CustomAuthController::class, 'login'])
    ->middleware('throttle:5,1')
    ->name('login.attempt');

// ==================== INREGISTRARE ====================
Route::get('/register', [CustomAuthController::class, 'showRegistrationForm'])->name('register');
Route::post('/register', [CustomAuthController::class, 'register']);

// ==================== VERIFICARE COD ====================
Route::post('/verify', [CustomAuthController::class, 'verify'])->name('verify.code');
Route::get('/verify', [CustomAuthController::class, 'showVerifyForm'])->name('verify.view');

//Change password
Route::get('/password/change', [CustomAuthController::class, 'showChangePasswordForm'])
    ->middleware('auth')
    ->name('password.change.form');
Route::post('/password/change', [CustomAuthController::class, 'changePassword'])
    ->middleware('auth')
    ->name('password.change');

// ✅ Show the password change form (GET request)
Route::view('/password/change', 'auth.password') 
    ->middleware('auth') 
    ->name('password.view');

// ✅ Handle the password change form submission (POST request)
Route::post('/password/change', [CustomAuthController::class, 'changePassword']) 
    ->middleware('auth') 
    ->name('password.change');

// Show the forgot password form
Route::get('/forgot-password', [CustomAuthController::class, 'showForgotPasswordForm'])->name('password.request');

// Handle forgot password submission (send code via email)
Route::post('/forgot-password', [CustomAuthController::class, 'sendResetCode'])->name('password.email');

// Show the reset form (with code input)
Route::get('/reset-password', [CustomAuthController::class, 'showResetForm'])->name('password.reset.view');

// Handle the final reset
Route::post('/reset-password', [CustomAuthController::class, 'resetPassword'])->name('password.update');
Route::post('/password/send-code', [CustomAuthController::class, 'sendResetCode'])->name('password.send.code');
Route::view('/register-success', 'auth.register-success')->name('register.success');


// Display all available abilities to the user (view: user/abilities.blade.php)
Route::get('/abilitati', [AbilityController::class, 'index'])->name('abilities.index');

//Settings page
Route::post('/setari/email', [UserSettingsController::class, 'updateEmail'])->name('user.settings.updateEmail');
Route::post('/setari/parola', [UserSettingsController::class, 'updatePassword'])->name('user.settings.updatePassword');

/*
|/*
|--------------------------------------------------------------------------
| Concurs Routes 🎵
|--------------------------------------------------------------------------
|
| These routes handle the daily music competition:
| - Uploading a song (1 per day)
| - Anonymous song list
| - Voting system
| - Winner selection
|
*/
// 📅 Show today's uploaded songs anonymously
Route::get('/concurs', [SongController::class, 'showTodaySongs'])
    ->name('concurs')
    ->middleware(\App\Http\Middleware\ForceWeekdayIfTesting::class);

// 🔒 Upload a song (must be logged in)
Route::post('/concurs/upload', [SongController::class, 'uploadSong'])
    ->name('concurs.upload')
    ->middleware(['auth', \App\Http\Middleware\ForceWeekdayIfTesting::class]);

// ✅ Voting (requires login)
Route::post('/vote/{songId}', [SongController::class, 'voteForSong'])
    ->name('vote.song')
    ->middleware(['auth', \App\Http\Middleware\ForceWeekdayIfTesting::class]);

// 🔄 Return today's songs HTML (for AJAX refresh after upload)
Route::get('/concurs/songs/today', [SongController::class, 'todayList'])
    ->name('concurs.songs.today')
    ->middleware(\App\Http\Middleware\ForceWeekdayIfTesting::class);

// Concurs – Choose next theme
Route::middleware(['auth', \App\Http\Middleware\ForceWeekdayIfTesting::class])->group(function () {
    Route::get('/concurs/alege-tema', [ConcursTemaController::class, 'create'])
        ->name('concurs.alege-tema.create');
    Route::post('/concurs/alege-tema', [ConcursTemaController::class, 'store'])
        ->name('concurs.alege-tema.store');
});

// Redirect to Versus Page
Route::get('/concurs/versus', [SongController::class, 'versus'])
    ->name('concurs.versus')
    ->middleware(\App\Http\Middleware\ForceWeekdayIfTesting::class);

/*
|--------------------------------------------------------------------------
| Leaderboard Routes
|--------------------------------------------------------------------------
|
| 1) Home partial (weekly top-3 podium on the homepage loads this internally)
| 2) Full leaderboard page with tabs
|
| /clasament accepts:
|   ?scope=alltime|weekly|monthly|yearly   (default = alltime)
|   weekly  → &week=YYYY-Www  and/or  &ws=YYYY-MM-DD (Monday of week)
|   monthly → &ym=YYYY-MM
|   yearly  → &y=YYYY
*/

Route::get('/leaderboard/home', [LeaderboardController::class, 'home'])
    ->name('leaderboard.home');

Route::get('/clasament', [LeaderboardController::class, 'index'])
    ->name('leaderboard.index');

   

    

//personal stats
use App\Http\Controllers\User\UserWinsController;

Route::middleware('auth')->group(function () {
    Route::get('/me/wins', [UserWinsController::class, 'index'])->name('me.wins');
    Route::get('/users/{userId}/wins', [UserWinsController::class, 'index'])->name('users.wins');
});
// Canonical with query stays:


// Pretty aliases that redirect to the canonical route with scope:
Route::get('/clasament/positions', fn() =>
    redirect()->route('leaderboard.index', ['scope' => 'positions'])
)->name('leaderboard.positions');

Route::get('/clasament/all-time', fn() =>
    redirect()->route('leaderboard.index', ['scope' => 'alltime'])
)->name('leaderboard.alltime');

Route::get('/clasament/monthly', fn() =>
    redirect()->route('leaderboard.index', ['scope' => 'monthly'])
)->name('leaderboard.monthly');

Route::get('/clasament/yearly', fn() =>
    redirect()->route('leaderboard.index', ['scope' => 'yearly'])
)->name('leaderboard.yearly');

use Illuminate\Support\Facades\Artisan;


// Admin-only routes for Concurs
use App\Http\Middleware\AdminOnly;

// Admin: go straight to the theme picker
Route::middleware(['auth', AdminOnly::class])->group(function () {
    Route::get('/admin/concurs/start', function () {
        $today = now()->toDateString();

        DB::transaction(function () use ($today) {
            // 1) Delete TODAY’s votes (only for songs from today)
            if (Schema::hasTable('votes') && Schema::hasTable('songs')) {
                // find today song IDs
                $songIds = DB::table('songs')
                    ->whereDate('competition_date', $today)   // << use your real date column
                    ->pluck('id');

                if ($songIds->isNotEmpty()) {
                    DB::table('votes')->whereIn('song_id', $songIds)->delete();
                }
            }

            // 2) Delete TODAY’s songs
            if (Schema::hasTable('songs')) {
                DB::table('songs')->whereDate('competition_date', $today)->delete();
            }

            // 3) Delete TODAY’s winner (if stored per day)
            if (Schema::hasTable('winners')) {
                // try common columns; keep the one you actually use
                if (Schema::hasColumn('winners','competition_date')) {
                    DB::table('winners')->whereDate('competition_date', $today)->delete();
                } elseif (Schema::hasColumn('winners','for_date')) {
                    DB::table('winners')->whereDate('for_date', $today)->delete();
                }
            }

            // 4) (Optional) Clear TODAY’s theme so you MUST pick a new one
            if (Schema::hasTable('themes')) {
                if (Schema::hasColumn('themes','competition_date')) {
                    DB::table('themes')->whereDate('competition_date', $today)->delete();
                } elseif (Schema::hasColumn('themes','date')) {
                    DB::table('themes')->whereDate('date', $today)->delete();
                }
            }
        });

        // back to the theme picker (fresh start)
        return redirect()
            ->route('concurs.alege-tema.create')
            ->with('status', 'Concurs reset pentru azi. Alege o temă nouă.');
    })->name('admin.concurs.start');
});

// Admin to force a winner on the current stats 

use App\Http\Middleware\ForceWeekdayIfTesting;


// Admin testing toggles (no time travel until you turn it ON)
Route::middleware(['auth', AdminOnly::class])->group(function () {
    // Turn ON: pretend it's a weekday for admin requests (stored in session)
    Route::get('/admin/testing/weekday/on', function () {
        session(['ap_force_weekday' => true]);
        return back()->with('status', 'Test Mode ON — weekday forced for this session.');
    })->name('admin.test.forceWeekdayOn');

    // Turn OFF: go back to real time; clear Carbon test clock
    Route::get('/admin/testing/weekday/off', function () {
        session()->forget('ap_force_weekday');
        Carbon::setTestNow(null);
        return back()->with('status', 'Test Mode OFF — real dates restored.');
    })->name('admin.test.forceWeekdayOff');
});

// Concurs admin tools (these inherit the fake weekday when ON)
Route::middleware(['auth', AdminOnly::class, ForceWeekdayIfTesting::class])->group(function () {
    // Start Concurs Test = reset today and go pick a new theme
    Route::get('/admin/concurs/start', function () {
        $today = now()->toDateString();

        \DB::transaction(function () use ($today) {
            if (\Schema::hasTable('votes') && \Schema::hasTable('songs')) {
                $songIds = \DB::table('songs')->whereDate('competition_date', $today)->pluck('id');
                if ($songIds->isNotEmpty()) {
                    \DB::table('votes')->whereIn('song_id', $songIds)->delete();
                }
            }
            if (\Schema::hasTable('songs')) {
                \DB::table('songs')->whereDate('competition_date', $today)->delete();
            }
            if (\Schema::hasTable('winners')) {
                if (\Schema::hasColumn('winners','contest_date')) {
                    \DB::table('winners')->whereDate('contest_date', $today)->delete();
                } elseif (\Schema::hasColumn('winners','competition_date')) {
                    \DB::table('winners')->whereDate('competition_date', $today)->delete();
                }
            }
            if (\Schema::hasTable('themes')) {
                if (\Schema::hasColumn('themes','competition_date')) {
                    \DB::table('themes')->whereDate('competition_date', $today)->delete();
                } elseif (\Schema::hasColumn('themes','date')) {
                    \DB::table('themes')->whereDate('date', $today)->delete();
                }
            }
        });

        return redirect()->route('concurs.alege-tema.create')
            ->with('status', 'Concurs reset pentru azi. Alege o temă nouă.');
    })->name('admin.concurs.start');

    // Declare winner NOW = run your real command, then return to normal flow
    Route::post('/admin/concurs/declare-now', function () {
        Artisan::call('concurs:declare-winner');
        return redirect()->route('concurs', ['force_popup' => 1])
            ->with('status', trim(Artisan::output()) ?: 'Câștigător recalculat acum.');
    })->name('admin.concurs.declareNow');
});

/*
|--------------------------------------------------------------------------
| Admin · Declare Winner NOW (test helper)
|--------------------------------------------------------------------------
| What this does:
| 1) Runs the same cron logic as at 20:00 → `concurs:declare-winner`.
| 2) Respects the ForceWeekday test mode (so you can run on weekends).
| 3) After computing the winner, if *you* are the winner and the theme
|    is not chosen yet, it flashes `ap_show_theme_modal = true`.
| 4) Redirects back to /concurs where the normal winner→pick-theme modal
|    will auto-open (Step 2 will add the Blade hook).
|
| Requirements: must be logged in AND admin.
*/
Route::get('/admin/concurs/declare-winner-now', function (Request $request) {
    Artisan::call('concurs:declare-winner');        // run the real winner logic

    $d = now()->toDateString();

    // Find today's winner (works with either column)
    $winner = DB::table('winners')
        ->whereDate('contest_date', $d)
        ->orWhereDate('win_date', $d)
        ->orderByDesc('id')
        ->first();

    $flash = ['status' => 'Winner logic executed.'];

    if ($winner) {
        $isMe = auth()->id() === (int) $winner->user_id;
        $needsTheme = (int) ($winner->theme_chosen ?? 0) === 0;

        if ($isMe && $needsTheme) {
            $flash['ap_show_theme_modal'] = true;   // tell Blade to open the modal NOW
        }

        $flash['status'] = "Winner today is user #{$winner->user_id}, song #{$winner->song_id} ({$winner->vote_count} votes).";
    }

    return redirect()->route('concurs')->with($flash);
})
->name('admin.concurs.declare-now')
->middleware([
    'auth',
    \App\Http\Middleware\AdminOnly::class,
    \App\Http\Middleware\ForceWeekdayIfTesting::class,
]);

//Points stats


