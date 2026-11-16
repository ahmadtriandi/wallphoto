<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class DashboardAuth
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = config('wall.dashboard_user', 'admin');
        $pass = config('wall.dashboard_password');

        if (! $pass) {
            abort(403, 'DASHBOARD_PASSWORD belum diisi di file .env');
        }

        $cocok = hash_equals((string) $user, (string) $request->getUser())
              && hash_equals((string) $pass, (string) $request->getPassword());

        if (! $cocok) {
            return response('Login diperlukan.', 401, [
                'WWW-Authenticate' => 'Basic realm="Dashboard Wall"',
            ]);
        }

        return $next($request);
    }
}
