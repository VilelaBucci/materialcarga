<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class SetorAutenticado
{
    public function handle(Request $request, Closure $next)
    {
        if (!session('setor_id')) {
            return redirect()->route('login');
        }
        return $next($request);
    }
}
