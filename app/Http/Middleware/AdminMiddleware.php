<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class AdminMiddleware
{
    public function handle($request, Closure $next)
    {
        Log::info('AdminMiddleware: Checking if user is authenticated');
        if (Auth::check()) {
            Log::info('AdminMiddleware: User is authenticated');
            if (Auth::user()->role == 'admin') {
                Log::info('AdminMiddleware: User is admin');
                return $next($request);
            }
            Log::info('AdminMiddleware: User is not admin');
        } else {
            Log::info('AdminMiddleware: User is not authenticated');
        }

        return redirect('/home');
    }
}
