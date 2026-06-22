<?php

namespace App\Actions\Fortify;

use Laravel\Fortify\Contracts\LoginResponse;

class RedirectToHome implements LoginResponse
{
    public function toResponse($request): mixed
    {
        $user = $request->user();

        return redirect()->intended(
            $user->role === 'ADMIN'
                ? route('dashboard')
                : route('catalog')
        );
    }
}