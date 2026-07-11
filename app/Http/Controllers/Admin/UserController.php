<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Users\BulkDeactivateUsersAction;
use App\Actions\Users\BulkReactivateUsersAction;
use App\Actions\Users\SendUserInvitationAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Users\BulkDeactivateUsersRequest;
use App\Http\Requests\Admin\Users\BulkReactivateUsersRequest;
use App\Http\Requests\Admin\Users\DeactivateUserRequest;
use App\Http\Requests\Admin\Users\StoreUserRequest;
use App\Http\Requests\Admin\Users\UpdateUserRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
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
        $this->authorize('create', User::class);

        return view('admin.users.create', [
            'roleNames' => Role::query()->orderBy('name')->pluck('name')->all(),
            'defaultRoles' => ['lid'],
        ]);
    }

    public function store(StoreUserRequest $request, SendUserInvitationAction $sendInvitation)
    {
        $validated = $request->validated();

        // Random wachtwoord (64 tekens) - user zet 'm zelf via invite-link
        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => bcrypt(Str::random(64)),
        ]);

        if (! empty($validated['roles'] ?? [])) {
            $user->syncRoles($validated['roles']);
        }

        $sendInvitation->execute($user);

        return redirect()
            ->route('admin.users.index')
            ->with('success', __('Uitnodiging verstuurd naar :email.', ['email' => $user->email]));
    }

    public function edit(User $user)
    {
        $this->authorize('update', $user);

        return view('admin.users.edit', [
            'user' => $user,
            'roleNames' => Role::query()->orderBy('name')->pluck('name')->all(),
            'currentRoles' => $user->roles->pluck('name')->all(),
        ]);
    }

    public function update(UpdateUserRequest $request, User $user, SendUserInvitationAction $sendInvitation)
    {
        $validated = $request->validated();

        $emailChanged = $user->email !== $validated['email'];

        $user->name = $validated['name'];
        $user->email = $validated['email'];

        if ($emailChanged) {
            $user->email_verified_at = null;
        }

        $user->save();

        $user->syncRoles($validated['roles'] ?? []);

        if ($emailChanged) {
            $sendInvitation->execute($user);

            $message = __('Gebruiker bijgewerkt. Nieuwe activatie-mail verstuurd naar :email.', [
                'email' => $user->email,
            ]);
        } else {
            $message = __('Gebruiker bijgewerkt.');
        }

        return redirect()
            ->route('admin.users.index')
            ->with('success', $message);
    }

    public function deactivate(DeactivateUserRequest $request, User $user)
    {
        $user->update([
            'deactivated_at' => now(),
            'deactivation_reason' => $request->validated('reason'),
        ]);

        return redirect()
            ->route('admin.users.index')
            ->with('success', __(':name is gedeactiveerd.', ['name' => $user->name]));
    }

    public function reactivate(Request $request, User $user)
    {
        $this->authorize('update', $user);

        $user->update([
            'deactivated_at' => null,
            'deactivation_reason' => null,
        ]);

        return redirect()
            ->route('admin.users.index')
            ->with('success', __(':name is opnieuw geactiveerd.', ['name' => $user->name]));
    }

    public function sendPasswordReset(Request $request, User $user, SendUserInvitationAction $sendInvitation)
    {
        $this->authorize('update', $user);

        $sendInvitation->execute($user);

        return redirect()
            ->route('admin.users.edit', $user)
            ->with('success', __('Wachtwoord-reset-mail verstuurd naar :email.', ['email' => $user->email]));
    }

    public function disableTwoFactor(Request $request, User $user)
    {
        $this->authorize('update', $user);

        $user->forceFill([
            'two_factor_secret' => null,
            'two_factor_recovery_codes' => null,
            'two_factor_confirmed_at' => null,
        ])->save();

        return redirect()
            ->route('admin.users.edit', $user)
            ->with('success', __('Tweestapsverificatie uitgezet voor :name.', ['name' => $user->name]));
    }

    public function bulkDeactivate(BulkDeactivateUsersRequest $request, BulkDeactivateUsersAction $action)
    {
        $ids = $request->validated('ids');
        $affected = $action->execute($ids);

        return redirect()
            ->route('admin.users.index')
            ->with('success', trans_choice(
                '{0} Geen gebruikers gedeactiveerd.|{1} :count gebruiker gedeactiveerd.|[2,*] :count gebruikers gedeactiveerd.',
                $affected,
                ['count' => $affected]
            ));
    }

    public function bulkReactivate(BulkReactivateUsersRequest $request, BulkReactivateUsersAction $action)
    {
        $ids = $request->validated('ids');
        $affected = $action->execute($ids);

        return redirect()
            ->route('admin.users.index')
            ->with('success', trans_choice(
                '{0} Geen gebruikers gereactiveerd.|{1} :count gebruiker gereactiveerd.|[2,*] :count gebruikers gereactiveerd.',
                $affected,
                ['count' => $affected]
            ));
    }
}
