<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class UserListController extends Controller
{
    /**
     * Show a simple list of all users.
     */
    public function index()
    {
        $users = User::all();
        return view('users', compact('users'));
    }
}

