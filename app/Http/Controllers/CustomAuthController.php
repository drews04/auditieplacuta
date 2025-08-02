<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Exceptions\ThrottleRequestsException;

class CustomAuthController extends Controller
{
    public function showRegistrationForm()
    {
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $data = $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users,email',
            'password' => 'required|confirmed|min:6',
        ]);

        $code = rand(100000, 999999);

        session([
            'registration_data' => [
                'name'     => $data['name'],
                'email'    => $data['email'],
                'password' => bcrypt($data['password']),
                'code'     => $code
            ]
        ]);

        try {
            Mail::raw("Codul tău de verificare este: $code", function ($message) use ($data) {
                $message->to($data['email'])
                        ->subject('Cod de verificare - Auditie Placuta');
            });
        } catch (\Exception $e) {
            Log::error('Eroare trimitere email: ' . $e->getMessage());
            return back()->withErrors([
                'email' => 'Eroare la trimiterea emailului. Încearcă din nou sau contactează-ne.'
            ]);
        }

        return view('auth.verify', ['email' => $data['email']]);
    }

    public function verify(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'verification_code' => 'required|string|max:6'
        ]);

        if ($validator->fails()) {
            return redirect()->route('verify.view')
                ->withErrors($validator)
                ->withInput();
        }

        $sessionData = session('registration_data');

        if (!$sessionData) {
            return redirect()->route('register')->withErrors([
                'email' => 'Datele au expirat. Te rugăm să încerci din nou.'
            ]);
        }

        if ($request->verification_code !== (string) $sessionData['code']) {
            return redirect()->route('verify.view')->withErrors([
                'verification_code' => 'Codul introdus este greșit.'
            ]);
        }

        User::create([
            'name'     => $sessionData['name'],
            'email'    => $sessionData['email'],
            'password' => $sessionData['password'],
        ]);

        session()->forget('registration_data');

        return redirect()->route('home')
            ->with('show_login_modal', true)
            ->with('success', '✅ Cont creat cu succes! Te poți autentifica.');
    }

    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        try {
            $credentials = $request->validate([
                'email'    => 'required|email',
                'password' => 'required',
            ]);

            if (Auth::attempt($credentials, $request->boolean('remember'))) {
                $request->session()->regenerate();
                return redirect()->route('home');
            }

            return back()
                ->withErrors(['email' => 'Email sau parolă incorectă.'])
                ->onlyInput('email');
        } catch (ThrottleRequestsException $e) {
            return back()
                ->withErrors(['email' => 'Prea multe încercări. Te rugăm să încerci din nou în câteva secunde.'])
                ->withInput();
        }
    }

    public function showVerifyForm()
    {
        $sessionData = session('registration_data');

        if (!$sessionData) {
            return redirect()->route('register')->withErrors([
                'email' => 'Nu ai completat formularul de înregistrare.'
            ]);
        }

        return view('auth.verify', ['email' => $sessionData['email']]);
    }

    
    public function changePassword(Request $request)
    {
        $request->validate([
            'current_password'      => 'required',
            'new_password'          => 'required|min:6|confirmed',
            'new_password_confirmation' => 'required',
        ]);

        $user = auth()->user();

        if (!Hash::check($request->current_password, $user->password)) {
            return back()->withErrors(['current_password' => 'Parola actuală este greșită.'])->withInput();
        }

        $user->update([
            'password' => bcrypt($request->new_password),
        ]);

        return back()->with('success', 'Parola a fost actualizata cu succes.');
    }

    public function showForgotPasswordForm()
    {
        return view('auth.forgot-password');
    }

    public function sendResetCode(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return back()->withErrors(['email' => 'Nu există niciun cont cu acest email.']);
        }

        $code = rand(100000, 999999);

        session([
            'reset_password' => [
                'email' => $user->email,
                'code'  => $code,
            ]
        ]);

        try {
            Mail::raw("Codul tău de resetare este: $code", function ($message) use ($user) {
                $message->to($user->email)
                        ->subject('Resetare parolă - Auditie Placuta');
            });
        } catch (\Exception $e) {
            Log::error('Eroare trimitere email resetare: ' . $e->getMessage());

            return back()->withErrors(['email' => 'Eroare la trimiterea emailului.']);
        }

        return redirect()->route('password.reset.view')->with('email', $user->email);
    }

    public function showResetForm()
    {
        $resetData = session('reset_password');

        if (!$resetData) {
            return redirect()->route('password.request')->withErrors([
                'email' => 'Trebuie să completezi mai întâi adresa de email.'
            ]);
        }

        return view('auth.reset-password', ['email' => $resetData['email']]);
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'code'                  => 'required|string|max:6',
            'new_password'          => 'required|min:6|confirmed',
            'new_password_confirmation' => 'required',
        ]);

        $resetData = session('reset_password');

        if (!$resetData || $request->code !== (string) $resetData['code']) {
            return back()->withErrors(['code' => 'Codul introdus este greșit sau a expirat.'])->withInput();
        }

        $user = User::where('email', $resetData['email'])->first();

        if (!$user) {
            return back()->withErrors(['email' => 'Utilizatorul nu există.']);
        }

        $user->update([
            'password' => bcrypt($request->new_password),
        ]);

        session()->forget('reset_password');

        return redirect()->route('home')
            ->with('show_login_modal', true)
            ->with('success', '✅ Parola a fost resetată cu succes. Te poți autentifica.');
    }
}
