<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index(Request $request)
    {
        // Implementatie in blok 4.13.b
        return view('admin.users.index', [
            'users' => collect(),
        ]);
    }

    public function create()
    {
        // Implementatie in blok 4.13.c
        return view('admin.users.create');
    }

    public function store(Request $request)
    {
        // Implementatie in blok 4.13.c
        abort(501, 'Nog niet geïmplementeerd.');
    }

    public function edit(User $user)
    {
        // Implementatie in blok 4.13.d
        return view('admin.users.edit', ['user' => $user]);
    }

    public function update(Request $request, User $user)
    {
        // Implementatie in blok 4.13.d
        abort(501, 'Nog niet geïmplementeerd.');
    }
}
