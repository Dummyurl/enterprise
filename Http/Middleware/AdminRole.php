<?php

namespace App\Http\Middleware;

use Auth;
use Closure;
use App\User;

class AdminRole {
	public function handle($request, Closure $next)
    {
        if (!Auth::check()) {
            return redirect('/admin');
        }

        $user = User::where('id',Auth::user()->id)->first();
        if (Auth::user() &&  Auth::user()->role == 1) {
            return $next($request);
	    }

	    return redirect('/admin');
    }
}
