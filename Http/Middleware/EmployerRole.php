<?php

namespace App\Http\Middleware;

use Auth;
use Closure;
use App\User;

class EmployerRole {
	public function handle($request, Closure $next)
    {
        if (!Auth::check()) {
            return redirect('/');
        }

        $user = User::where('id',Auth::user()->id)->first();
        if (Auth::user() &&  Auth::user()->role == 3) {
            return $next($request);
	    }

	    return redirect('/');
    }
}
