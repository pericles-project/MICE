<?php

namespace App\Http\Controllers;

use App\User;
use App\Http\Controllers\Controller;

class MainController extends Controller
{
    /**
     * Get data
     *
     * @return Response
     */
    public function getData()
    {
        return view('user.profile', ['user' => User::findOrFail($id)]);
    }
}
