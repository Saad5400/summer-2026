<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCategoryRequest;
use App\Http\Requests\UpdateCategoryRequest;
use App\Models\Category;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class CategoryController extends Controller
{
    public function index(): Response
    {
        $categories = Category::query()
            ->whereNull('user_id')
            ->orWhere('user_id', auth()->id())
            ->with('children')
            ->orderBy('type')
            ->orderBy('sort_order')
            ->get();

        return Inertia::render('categories/Index', [
            'categories' => $categories,
            'expenseCategories' => $categories->where('type', 'expense')->values(),
            'incomeCategories' => $categories->where('type', 'income')->values(),
        ]);
    }

    public function store(StoreCategoryRequest $request): RedirectResponse
    {
        $request->user()->categories()->create($request->validated());

        Inertia::flash('toast', ['type' => 'success', 'message' => 'تم إضافة الفئة بنجاح']);

        return to_route('categories.index');
    }

    public function update(UpdateCategoryRequest $request, Category $category): RedirectResponse
    {
        if ($category->user_id === null || $category->user_id !== auth()->id()) {
            abort(403);
        }

        $category->update($request->validated());

        Inertia::flash('toast', ['type' => 'success', 'message' => 'تم تحديث الفئة بنجاح']);

        return to_route('categories.index');
    }

    public function destroy(Category $category): RedirectResponse
    {
        if ($category->user_id === null || $category->user_id !== auth()->id()) {
            abort(403);
        }

        $category->delete();

        Inertia::flash('toast', ['type' => 'success', 'message' => 'تم حذف الفئة بنجاح']);

        return to_route('categories.index');
    }
}
