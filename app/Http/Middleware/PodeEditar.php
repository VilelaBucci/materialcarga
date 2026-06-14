<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class PodeEditar
{
    public function handle(Request $request, Closure $next)
    {
        if (!session('pode_editar')) {
            return redirect()->back()->with('erro', 'Você está em modo somente leitura.');
        }
        return $next($request);
    }
}
