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

//Acasa - Routes
Route::get('/clasament-lunar', [ClasamentLunarController::class, 'index'])->name('clasament-lunar');
Route::get('/evenimente', [EvenimenteController::class, 'index'])->name('evenimente');
Route::get('/regulament', [RegulamentController::class, 'index'])->name('regulament');
//Arena
Route::get('/arena', [ArenaController::class, 'index'])->name('arena');
Route::get('/abilitati', [AbilitatiController::class, 'index'])->name('abilitati');
Route::get('/cooldown', [CoolDownController::class, 'index'])->name('cooldown');
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