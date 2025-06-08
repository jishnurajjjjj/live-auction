<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;
use Closure; 

class Authenticate extends Middleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     */

    public function handle($request, Closure $next, ...$guards)
    {
        $user = auth()->user();

        if ($user && $user->is_blocked) {
            auth()->logout();

            return redirect()->route('login')->withErrors(['error' => 'Your account is blocked.']);
        }

        return parent::handle($request, $next, ...$guards);
    }

    protected function redirectTo(Request $request): ?string
    {
        return $request->expectsJson() ? null : route('login');
    }
}
