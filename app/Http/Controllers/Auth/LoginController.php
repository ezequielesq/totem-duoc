<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;

class LoginController extends Controller
{
    use AuthenticatesUsers;

    protected function redirectTo(): string
    {
        $user = auth()->user();

        if ($user->hasRole('directora')) {
            return '/dashboard';
        }

        return '/asesor';
    }
}
