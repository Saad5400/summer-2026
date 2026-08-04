<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $now = now();
        $currentMonth = $now->month;
        $currentYear = $now->year;

        // Current month totals
        $currentMonthExpenses = $user->transactions()
            ->where('type', 'expense')
            ->whereMonth('date', $currentMonth)
            ->whereYear('date', $currentYear)
            ->sum('amount');

        $currentMonthIncome = $user->transactions()
            ->where('type', 'income')
            ->whereMonth('date', $currentMonth)
            ->whereYear('date', $currentYear)
            ->sum('amount');

        $balance = $currentMonthIncome - $currentMonthExpenses;

        // Previous month for comparison
        $previousMonth = $now->copy()->subMonth();
        $previousMonthExpenses = $user->transactions()
            ->where('type', 'expense')
            ->whereMonth('date', $previousMonth->month)
            ->whereYear('date', $previousMonth->year)
            ->sum('amount');

        // Expense change percentage
        $expenseChange = $previousMonthExpenses > 0
            ? round((($currentMonthExpenses - $previousMonthExpenses) / $previousMonthExpenses) * 100, 1)
            : 0;

        // Current month expense categories breakdown
        $expenseByCategory = $user->transactions()
            ->where('type', 'expense')
            ->whereMonth('date', $currentMonth)
            ->whereYear('date', $currentYear)
            ->whereNotNull('category_id')
            ->selectRaw('category_id, SUM(amount) as total')
            ->groupBy('category_id')
            ->with('category:id,name,icon,color')
            ->get()
            ->map(fn ($t) => [
                'category_id' => $t->category_id,
                'category_name' => $t->category?->name,
                'icon' => $t->category?->icon,
                'color' => $t->category?->color,
                'total' => (float) $t->total,
            ]);

        // Current month income categories breakdown
        $incomeByCategory = $user->transactions()
            ->where('type', 'income')
            ->whereMonth('date', $currentMonth)
            ->whereYear('date', $currentYear)
            ->whereNotNull('category_id')
            ->selectRaw('category_id, SUM(amount) as total')
            ->groupBy('category_id')
            ->with('category:id,name,icon,color')
            ->get()
            ->map(fn ($t) => [
                'category_id' => $t->category_id,
                'category_name' => $t->category?->name,
                'icon' => $t->category?->icon,
                'color' => $t->category?->color,
                'total' => (float) $t->total,
            ]);

        // Recent transactions (last 10)
        $recentTransactions = $user->transactions()
            ->with('category:id,name,icon,color,type')
            ->latest('date')
            ->limit(10)
            ->get()
            ->map(fn ($t) => [
                'id' => $t->id,
                'amount' => (float) $t->amount,
                'description' => $t->description,
                'date' => $t->date->format('Y-m-d'),
                'type' => $t->type,
                'category_name' => $t->category?->name,
                'icon' => $t->category?->icon,
                'color' => $t->category?->color,
            ]);

        return Inertia::render('Dashboard', [
            'summary' => [
                'expenses' => (float) $currentMonthExpenses,
                'income' => (float) $currentMonthIncome,
                'balance' => (float) $balance,
                'expenseChange' => $expenseChange,
            ],
            'expenseByCategory' => $expenseByCategory,
            'incomeByCategory' => $incomeByCategory,
            'recentTransactions' => $recentTransactions,
            'categories' => Category::whereNull('user_id')
                ->orWhere('user_id', $user->id)
                ->orderBy('type')
                ->orderBy('sort_order')
                ->get(),
            'recentCategories' => $user->transactions()
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
}
