<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $now = now();
        $currentMonth = $now->month;
        $currentYear = $now->year;

        $selectedMonth = $request->input('month', $currentMonth);
        $selectedYear = $request->input('year', $currentYear);

        // Expense by category for selected month/year (Pie chart data)
        $expenseByCategory = $user->transactions()
            ->where('type', 'expense')
            ->whereMonth('date', $selectedMonth)
            ->whereYear('date', $selectedYear)
            ->whereNotNull('category_id')
            ->selectRaw('category_id, SUM(amount) as total')
            ->groupBy('category_id')
            ->with('category:id,name,icon,color')
            ->get()
            ->map(fn ($t) => [
                'name' => $t->category?->name ?? 'بدون تصنيف',
                'icon' => $t->category?->icon,
                'color' => $t->category?->color ?? '#6b7280',
                'total' => (float) $t->total,
            ])
            ->values();

        // Income by category for selected month/year (Pie chart data)
        $incomeByCategory = $user->transactions()
            ->where('type', 'income')
            ->whereMonth('date', $selectedMonth)
            ->whereYear('date', $selectedYear)
            ->whereNotNull('category_id')
            ->selectRaw('category_id, SUM(amount) as total')
            ->groupBy('category_id')
            ->with('category:id,name,icon,color')
            ->get()
            ->map(fn ($t) => [
                'name' => $t->category?->name ?? 'بدون تصنيف',
                'icon' => $t->category?->icon,
                'color' => $t->category?->color ?? '#6b7280',
                'total' => (float) $t->total,
            ])
            ->values();

        // Single query for last 12 months to power both monthly comparison and spending trends
        $twelveMonthsAgo = $now->copy()->subMonths(12)->startOfMonth();
        $transactionsByMonth = $user->transactions()
            ->where('date', '>=', $twelveMonthsAgo)
            ->get(['date', 'type', 'amount'])
            ->groupBy(fn ($t) => $t->date->format('Y-m'));

        // Monthly comparison (last 6 months) - Bar chart data
        $monthlyComparison = collect();
        for ($i = 5; $i >= 0; $i--) {
            $date = $now->copy()->subMonths($i);
            $key = $date->format('Y-m');
            $monthData = $transactionsByMonth->get($key, collect());

            $monthlyComparison->push([
                'month' => $date->translatedFormat('F Y'),
                'key' => $date->format('Y-m'),
                'expenses' => (float) $monthData->where('type', 'expense')->sum('amount'),
                'income' => (float) $monthData->where('type', 'income')->sum('amount'),
            ]);
        }

        // Spending trends (last 12 months for line chart)
        $spendingTrends = collect();
        for ($i = 11; $i >= 0; $i--) {
            $date = $now->copy()->subMonths($i);
            $key = $date->format('Y-m');
            $monthData = $transactionsByMonth->get($key, collect());

            $spendingTrends->push([
                'month' => $date->translatedFormat('F Y'),
                'key' => $date->format('Y-m'),
                'expenses' => (float) $monthData->where('type', 'expense')->sum('amount'),
                'income' => (float) $monthData->where('type', 'income')->sum('amount'),
            ]);
        }

        // Current month total
        $currentMonthExpenses = $user->transactions()
            ->where('type', 'expense')
            ->whereMonth('date', $selectedMonth)
            ->whereYear('date', $selectedYear)
            ->sum('amount');

        $currentMonthIncome = $user->transactions()
            ->where('type', 'income')
            ->whereMonth('date', $selectedMonth)
            ->whereYear('date', $selectedYear)
            ->sum('amount');

        // Available months for filter dropdown (database-agnostic — no strftime)
        $availableMonths = $user->transactions()
            ->orderBy('date', 'desc')
            ->pluck('date')
            ->map(fn ($date) => [
                'year' => (int) $date->year,
                'month' => (int) $date->month,
            ])
            ->unique(fn ($m) => $m['year'].'-'.str_pad((string) $m['month'], 2, '0', STR_PAD_LEFT))
            ->values()
            ->map(fn ($m) => [
                'year' => $m['year'],
                'month' => $m['month'],
                'label' => Carbon::create($m['year'], $m['month'], 1)->translatedFormat('F Y'),
            ]);

        // Categories list
        $categories = Category::whereNull('user_id')
            ->orWhere('user_id', $user->id)
            ->orderBy('type')
            ->orderBy('sort_order')
            ->get();

        return Inertia::render('reports/Index', [
            'expenseByCategory' => $expenseByCategory,
            'incomeByCategory' => $incomeByCategory,
            'monthlyComparison' => $monthlyComparison->values(),
            'spendingTrends' => $spendingTrends->values(),
            'summary' => [
                'expenses' => (float) $currentMonthExpenses,
                'income' => (float) $currentMonthIncome,
            ],
            'selectedMonth' => (int) $selectedMonth,
            'selectedYear' => (int) $selectedYear,
            'availableMonths' => $availableMonths,
            'categories' => $categories,
        ]);
    }
}
