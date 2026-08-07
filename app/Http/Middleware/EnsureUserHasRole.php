<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasRole
{
    /**
     * Impede que um perfil acesse rotas reservadas a outros perfis,
     * inclusive quando a URL é informada diretamente no navegador.
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        abort_unless($user && in_array($user->tipo_usuario, $roles, true), 403,
            'Seu perfil não possui permissão para acessar esta área.'
        );

        return $next($request);
    }
}
