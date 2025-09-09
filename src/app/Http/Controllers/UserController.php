<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class UserController extends Controller
{
    public function notice()
    {
        return view('auth.notice');
    }

    public function profile()
    {
        return view('profile');
    }
}
