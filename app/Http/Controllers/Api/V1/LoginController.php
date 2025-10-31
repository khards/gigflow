<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    public function login(Request $request)
    {
        $login = $request->validate([
            'email' => 'required|email',
            'password' => 'string',
        ]);

//        $email = $request->get('email');
//        $password = $request->get('password');

        if (! Auth::attempt($login)) {
            return response(['message' => 'Invalid login credentials.']);
        }

        //All good - issue a personal access token.

        $user = Auth::user();
        $token = $user->createToken('Test tolken')->accessToken;

        return response(['user' => Auth::user(), 'token' => $token]);
    }

    public function test(Request $request)
    {
        return 'Yay, it works! via middleware auth:api';
    }
}
