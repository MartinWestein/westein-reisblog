<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\FamilyMemberRequest;
use App\Models\FamilyMember;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FamilyMemberController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', FamilyMember::class);

        $sort = $request->string('sort', 'order')->toString();
        $direction = $request->string('direction', 'asc')->toString();
        $search = $request->string('search')->toString();

        $allowedSorts = ['order', 'name', 'role', 'created_at'];

        if (! in_array($sort, $allowedSorts, true)) {
            $sort = 'order';
        }

        if (! in_array($direction, ['asc', 'desc'], true)) {
            $direction = 'asc';
        }

        $members = FamilyMember::query()
            ->with(['user', 'media'])
            ->when($search !== '', fn ($q) => $q->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('role', 'like', "%{$search}%");
            }))
            ->orderBy($sort, $direction)
            ->when($sort === 'order', fn ($q) => $q->orderBy('name'))
            ->paginate(24)
            ->withQueryString();

        return view('admin.family-members.index', [
            'members' => $members,
            'sort' => $sort,
            'direction' => $direction,
            'search' => $search,
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', FamilyMember::class);

        return view('admin.family-members.create', [
            'member' => new FamilyMember,
            'users' => $this->availableUsers(),
        ]);
    }

    public function store(FamilyMemberRequest $request): RedirectResponse
    {
        $member = FamilyMember::create($request->safe()->except(['portrait', 'remove_portrait']));

        if ($request->hasFile('portrait')) {
            $member->addMediaFromRequest('portrait')
                ->toMediaCollection('portrait');
        }

        return redirect()
            ->route('admin.family-members.index')
            ->with('success', __(':name is toegevoegd aan de familie.', ['name' => $member->name]));
    }

    public function edit(FamilyMember $familyMember): View
    {
        $this->authorize('update', $familyMember);

        return view('admin.family-members.edit', [
            'member' => $familyMember,
            'users' => $this->availableUsers(),
        ]);
    }

    public function update(FamilyMemberRequest $request, FamilyMember $familyMember): RedirectResponse
    {
        $familyMember->update($request->safe()->except(['portrait', 'remove_portrait']));

        if ($request->boolean('remove_portrait')) {
            $familyMember->clearMediaCollection('portrait');
        }

        if ($request->hasFile('portrait')) {
            $familyMember->clearMediaCollection('portrait');
            $familyMember->addMediaFromRequest('portrait')
                ->toMediaCollection('portrait');
        }

        return redirect()
            ->route('admin.family-members.index')
            ->with('success', __(':name is bijgewerkt.', ['name' => $familyMember->name]));
    }

    public function destroy(FamilyMember $familyMember): RedirectResponse
    {
        $this->authorize('delete', $familyMember);

        $name = $familyMember->name;
        $familyMember->delete();

        return redirect()
            ->route('admin.family-members.index')
            ->with('success', __(':name is verwijderd.', ['name' => $name]));
    }

    /**
     * Users die nog niet aan een ander FamilyMember-record hangen,
     * plus de momenteel gekoppelde user (indien edit-context).
     */
    protected function availableUsers()
    {
        return User::query()
            ->orderBy('name')
            ->get(['id', 'name', 'email']);
    }
}
