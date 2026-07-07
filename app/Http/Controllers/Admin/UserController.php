<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', User::class);

        $search = trim((string) $request->query('search', ''));
        $roleFilter = (string) $request->query('role', 'all');
        $status = (string) $request->query('status', 'all');
        $sort = (string) $request->query('sort', 'created_at');
        $direction = $request->query('direction', 'desc') === 'asc' ? 'asc' : 'desc';

        $allowedSorts = ['name', 'email', 'created_at'];
        if (! in_array($sort, $allowedSorts, true)) {
            $sort = 'created_at';
        }

        $roleNames = Role::query()->orderBy('name')->pluck('name')->all();
        if ($roleFilter !== 'all' && ! in_array($roleFilter, $roleNames, true)) {
            $roleFilter = 'all';
        }

        $query = User::query()->with('roles');

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($roleFilter !== 'all') {
            $query->role($roleFilter);
        }

        match ($status) {
            'active' => $query->active(),
            'deactivated' => $query->deactivated(),
            default => null,
        };

        $users = $query->orderBy($sort, $direction)
            ->paginate(25)
            ->withQueryString();

        return view('admin.users.index', [
            'users' => $users,
            'search' => $search,
            'roleFilter' => $roleFilter,
            'status' => $status,
            'sort' => $sort,
            'direction' => $direction,
            'roleNames' => $roleNames,
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
