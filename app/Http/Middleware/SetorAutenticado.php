<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class SetorAutenticado
{
    public function handle(Request $request, Closure $next)
    {
        // Setor escolhido no login, ou admin que entrou direto em todos os setores
        if (!session('setor_id') && !session('ver_todos')) {
            return redirect()->route('login');
        }
        return $next($request);
    }
}
