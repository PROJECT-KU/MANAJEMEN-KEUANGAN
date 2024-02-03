<?php

namespace App\Http\Middleware;

use Closure;

class CheckUpdateSuccess
{
    public function handle($request, Closure $next)
    {
        // Check if the update was successful
        $updateSuccess = $request->session()->get('updateSuccess', false);

        if (!$updateSuccess) {
            // If not successful, redirect to account.peserta.form
            return redirect()->route('account.peserta.form');
        }

        return $next($request);
    }
}
