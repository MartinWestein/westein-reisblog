<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Categories\StoreCategoryRequest;
use App\Http\Requests\Admin\Categories\UpdateCategoryRequest;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', Category::class);

        $query = Category::query()->withCount('posts');

        if ($search = $request->string('search')->trim()->value()) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $sort = $request->string('sort', 'order')->value();
        $direction = $request->string('direction', 'asc')->value() === 'desc' ? 'desc' : 'asc';

        if (! in_array($sort, ['name', 'order', 'posts_count', 'created_at'])) {
            $sort = 'order';
        }

        $categories = $query->orderBy($sort, $direction)
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        return view('admin.categories.index', compact('categories', 'sort', 'direction'));
    }

    public function create()
    {
        $this->authorize('create', Category::class);

        return view('admin.categories.create');
    }

    public function store(StoreCategoryRequest $request)
    {
        // Authorization gebeurt al in StoreCategoryRequest::authorize()
        $data = $request->validated();

        if (empty($data['slug'])) {
            $data['slug'] = Str::slug($data['name']);
        }

        $data['order'] ??= 0;

        Category::create($data);

        return redirect()
            ->route('admin.categories.index')
            ->with('success', 'Categorie aangemaakt.');
    }

    public function edit(Category $category)
    {
        $this->authorize('update', $category);

        return view('admin.categories.edit', compact('category'));
    }

    public function update(UpdateCategoryRequest $request, Category $category)
    {
        // Authorization gebeurt al in UpdateCategoryRequest::authorize()
        $data = $request->validated();
        $data['order'] ??= 0;

        $category->update($data);

        return redirect()
            ->route('admin.categories.index')
            ->with('success', 'Categorie bijgewerkt.');
    }

    public function destroy(Category $category)
    {
        $this->authorize('delete', $category);

        if ($category->posts()->exists()) {
            return back()->with('error', "Kan '{$category->name}' niet verwijderen — er zijn nog posts aan gekoppeld.");
        }

        $category->delete();

        return redirect()
            ->route('admin.categories.index')
            ->with('success', 'Categorie verwijderd.');
    }
}
