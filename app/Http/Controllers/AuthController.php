<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class AuthController extends Controller
{
    /**
     * Show the login form.
     */
    public function showLogin(): View
    {
        return view('auth.login');
    }

    /**
     * Handle a login attempt.
     */
    public function login(LoginRequest $request): RedirectResponse
    {
        if (! $this->attemptLogin($request)) {
            return back()
                ->withErrors(['email' => 'E-mail, credencial ou perfil incorretos.'])
                ->onlyInput('email', 'tipo_usuario', 'remember');
        }

        $request->session()->regenerate();

        $user = Auth::user();

        if ($user->situacao !== 'ativo') {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return back()
                ->withErrors(['email' => 'Este cadastro está inativo. Procure a secretaria acadêmica.'])
                ->onlyInput('email', 'tipo_usuario', 'remember');
        }

        return redirect()->intended(route('inicio'))
            ->with('success', 'Bem-vindo(a) de volta, '.$user->name.'!');
    }

    private function attemptLogin(LoginRequest $request): bool
    {
        if ($request->userType() === 'administrador') {
            return Auth::attempt([
                'email' => $request->string('email')->toString(),
                'password' => (string) $request->input('password'),
                'tipo_usuario' => 'administrador',
            ], $request->boolean('remember'));
        }

        $userType = $request->userType();
        $submittedEmail = $request->string('email')->toString();

        $user = User::query()
            ->with($userType)
            ->where('tipo_usuario', $userType)
            ->where('email', $submittedEmail)
            ->first();

        $user ??= User::query()
            ->with($userType)
            ->where('tipo_usuario', $userType)
            ->whereHas($userType, fn ($relation) => $relation->where('email', $submittedEmail))
            ->first();

        $registeredCpf = $user?->{$userType}?->cpf ?? $user?->cpf;
        $registeredCpfDigits = preg_replace('/\D/', '', (string) $registeredCpf);

        if (! $user || strlen($registeredCpfDigits) !== 11
            || ! hash_equals($registeredCpfDigits, $request->cpfDigits())) {
            return false;
        }

        if ($user->situacao !== 'ativo') {
            Auth::login($user);

            return true;
        }

        $credentials = [
            'email' => $user->email,
            'password' => $request->cpfDigits(),
            'tipo_usuario' => $userType,
        ];

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            return true;
        }

        return false;
    }

    /**
     * Show the registration form.
     */
    public function showRegister(): View
    {
        return view('auth.register');
    }

    /**
     * Handle a new user registration.
     */
    public function register(Request $request): RedirectResponse
    {
        $submittedCpfDigits = preg_replace('/\D/', '', (string) $request->input('cpf'));

        if (strlen($submittedCpfDigits) === 11) {
            $request->merge([
                'cpf' => preg_replace('/(\d{3})(\d{3})(\d{3})(\d{2})/', '$1.$2.$3-$4', $submittedCpfDigits),
            ]);
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'cpf' => ['required', 'string', 'regex:/^\d{3}\.?\d{3}\.?\d{3}-?\d{2}$/', 'unique:users,cpf'],
        ], [
            'cpf.regex' => 'Informe um CPF válido, no formato 000.000.000-00.',
        ], [
            'name' => 'nome',
            'email' => 'e-mail',
            'cpf' => 'CPF',
        ]);

        $cpfDigits = preg_replace('/\D/', '', $validated['cpf']);
        $cpfFormatted = preg_replace('/(\d{3})(\d{3})(\d{3})(\d{2})/', '$1.$2.$3-$4', $cpfDigits);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'cpf' => $cpfFormatted,
            // O cadastro público nunca concede perfil administrativo ou docente.
            'tipo_usuario' => 'aluno',
            'situacao' => 'ativo',
            'password' => Hash::make($cpfDigits),
        ]);

        Auth::login($user);

        $request->session()->regenerate();

        return redirect()->route('inicio')->with(
            'success',
            'Cadastro de aluno realizado! Sua senha de acesso é o seu CPF (somente números).'
        );
    }

    /**
     * Log the user out of the application.
     */
    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('success', 'Sessão encerrada com segurança.');
    }
}
