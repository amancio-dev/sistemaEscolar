<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
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
    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ], [], [
            'email' => 'e-mail',
            'password' => 'senha',
        ]);

        $remember = $request->boolean('remember');

        if (! Auth::attempt($credentials, $remember)) {
            return back()
                ->withErrors(['email' => 'E-mail ou senha incorretos.'])
                ->onlyInput('email');
        }

        $request->session()->regenerate();

        $user = Auth::user();

        if ($user->situacao !== 'ativo') {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return back()
                ->withErrors(['email' => 'Este cadastro está inativo. Procure a secretaria acadêmica.'])
                ->onlyInput('email');
        }

        return redirect()->intended(route('inicio'))
            ->with('success', 'Bem-vindo(a) de volta, '.$user->name.'!');
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
        $tipo = $request->input('tipo_usuario');
        $usesCpfAsPassword = in_array($tipo, ['aluno', 'professor'], true);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'tipo_usuario' => ['required', 'in:administrador,professor,aluno'],
            'cpf' => [
                Rule::requiredIf($usesCpfAsPassword),
                'nullable',
                'string',
                'regex:/^\d{3}\.?\d{3}\.?\d{3}-?\d{2}$/',
                'unique:users,cpf',
            ],
            'password' => [
                Rule::requiredIf(! $usesCpfAsPassword),
                'nullable',
                'confirmed',
                Password::min(8)->letters()->numbers(),
            ],
        ], [
            'cpf.regex' => 'Informe um CPF válido, no formato 000.000.000-00.',
            'password.letters' => 'A senha deve conter pelo menos uma letra.',
            'password.numbers' => 'A senha deve conter pelo menos um número.',
        ], [
            'name' => 'nome',
            'email' => 'e-mail',
            'tipo_usuario' => 'perfil',
            'cpf' => 'CPF',
            'password' => 'senha',
        ]);

        $cpfDigits = $usesCpfAsPassword ? preg_replace('/\D/', '', $validated['cpf']) : null;
        $cpfFormatted = $usesCpfAsPassword ? preg_replace('/(\d{3})(\d{3})(\d{3})(\d{2})/', '$1.$2.$3-$4', $cpfDigits) : null;

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'cpf' => $cpfFormatted,
            'tipo_usuario' => $validated['tipo_usuario'],
            'situacao' => 'ativo',
            'password' => Hash::make($usesCpfAsPassword ? $cpfDigits : $validated['password']),
        ]);

        Auth::login($user);

        $request->session()->regenerate();

        $message = $usesCpfAsPassword
            ? 'Cadastro realizado com sucesso! Sua senha de acesso é o seu CPF (somente números).'
            : 'Cadastro realizado com sucesso! Bem-vindo(a), '.$user->name.'.';

        return redirect()->route('inicio')->with('success', $message);
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
