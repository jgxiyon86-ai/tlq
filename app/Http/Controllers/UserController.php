<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index()
    {
        // Get users with the count of their activated licenses
        $users = User::withCount('licenses')->latest()->paginate(15);
        return view('admin.users.index', compact('users'));
    }

    public function show(User $user)
    {
        // Show specific user and all their Jars/Licenses
        $user->load('licenses.series');
        return view('admin.users.show', compact('user'));
    }
}
