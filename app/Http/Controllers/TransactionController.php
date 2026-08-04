<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTransactionRequest;
use App\Http\Requests\UpdateTransactionRequest;
use App\Models\Category;
use App\Models\Transaction;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class TransactionController extends Controller
{
    public function index(Request $request): Response
    {
        $query = $request->user()->transactions()->with('category');

        if ($request->filled('type')) {
            $query->where('type', $request->input('type'));
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->input('category_id'));
        }

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where('description', 'like', "%{$search}%");
        }

        if ($request->filled('date_from')) {
            $query->whereDate('date', '>=', $request->input('date_from'));
        }

        if ($request->filled('date_to')) {
            $query->whereDate('date', '<=', $request->input('date_to'));
        }

        return Inertia::render('transactions/Index', [
            'transactions' => $query->orderBy('date', 'desc')->paginate(15),
            'categories' => Category::whereNull('user_id')
                ->orWhere('user_id', $request->user()->id)
                ->orderBy('type')
                ->orderBy('sort_order')
                ->get(),
            'filters' => $request->only(['type', 'category_id', 'search', 'date_from', 'date_to']),
            'recentCategories' => $request->user()->transactions()
                ->whereNotNull('category_id')
                ->select('category_id')
                ->selectRaw('MAX(date) as last_used')
                ->groupBy('category_id')
                ->orderBy('last_used', 'desc')
                ->limit(6)
                ->with('category:id,name,icon,color,type')
                ->get()
                ->map(fn ($t) => $t->category)
                ->filter()
                ->values(),
        ]);
    }

    public function store(StoreTransactionRequest $request): RedirectResponse
    {
        $request->user()->transactions()->create($request->validated());

        Inertia::flash('toast', ['type' => 'success', 'message' => 'تمت إضافة المعاملة بنجاح.']);

        return to_route('transactions.index');
    }

    public function update(UpdateTransactionRequest $request, Transaction $transaction): RedirectResponse
    {
        if ($transaction->user_id !== $request->user()->id) {
            abort(403);
        }

        $transaction->update($request->validated());

        Inertia::flash('toast', ['type' => 'success', 'message' => 'تم تحديث المعاملة بنجاح.']);

        return to_route('transactions.index');
    }

    public function destroy(Request $request, Transaction $transaction): RedirectResponse
    {
        if ($transaction->user_id !== $request->user()->id) {
            abort(403);
        }

        $transaction->delete();

        Inertia::flash('toast', ['type' => 'success', 'message' => 'تم حذف المعاملة بنجاح.']);

        return to_route('transactions.index');
    }
}
