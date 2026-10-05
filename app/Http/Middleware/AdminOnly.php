<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class AdminOnly
{
    // only the admin can open the admin pages; reception has their own side
    public function handle(Request $request, Closure $next)
    {
        if ($request->user()->role != 'admin') {
            return redirect('/dashboard')->with('error', 'Only the admin can open that page.');
        }

        return $next($request);
    }
}
