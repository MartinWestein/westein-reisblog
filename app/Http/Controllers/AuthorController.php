<?php

namespace App\Http\Controllers;

use App\Models\FamilyMember;
use App\Models\Page;
use App\Models\Post;
use Illuminate\View\View;

class AuthorController extends Controller
{
    /**
     * Over ons: de over-ons-Page als bewerkbare intro + de FamilyMembers-grid (F5-110).
     */
    public function overview(): View
    {
        $page = Page::published()->where('slug', 'over-ons')->first();

        $members = FamilyMember::query()->ordered()->get();

        return view('authors.overview', [
            'page' => $page,
            'members' => $members,
        ]);
    }

    /**
     * Auteur-pagina (F5-109): naam + rol + bio + initialen-avatar; bij een aan-User
     * gekoppeld familielid ook de volledige gepagineerde verhalenlijst (F5-115),
     * tips uitgesloten (F5-94). Niet-gekoppelde leden krijgen dezelfde pagina zonder strook.
     */
    public function show(FamilyMember $familyMember): View
    {
        $familyMember->load('user');

        $posts = null;

        if ($familyMember->user) {
            $posts = Post::query()
                ->where('user_id', $familyMember->user->id)
                ->published()
                ->whereDoesntHave('categories', fn ($q) => $q->where('slug', 'tips'))
                ->with(['author', 'destination', 'location', 'categories', 'media'])
                ->orderByDesc('published_at')
                ->paginate(12)
                ->withQueryString();
        }

        return view('authors.show', [
            'member' => $familyMember,
            'posts' => $posts,
        ]);
    }
}
