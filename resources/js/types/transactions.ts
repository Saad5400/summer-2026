export type TransactionType = 'expense' | 'income';

export interface Category {
  id: number;
  name: string;
  icon: string;
  color: string;
  type: TransactionType;
}

export interface Transaction {
  id: number;
  amount: number;
  description: string;
  date: string;
  type: TransactionType;
  category: Category | null;
  created_at: string;
}

export interface TransactionSummary {
  total_expenses: number;
  total_income: number;
  balance: number;
  transaction_count: number;
}

export interface TransactionFormData {
  amount: number;
  description: string;
  date: string;
  type: TransactionType;
  category_id: number | null;
}

export const EXPENSE_CATEGORIES: Category[] = [
  { id: 1, name: 'طعام ومشروبات', icon: 'utensils', color: '#ef4444', type: 'expense' },
  { id: 2, name: 'مواصلات', icon: 'car', color: '#f97316', type: 'expense' },
  { id: 3, name: 'سكن', icon: 'home', color: '#eab308', type: 'expense' },
  { id: 4, name: 'فواتير', icon: 'receipt', color: '#22c55e', type: 'expense' },
  { id: 5, name: 'ترفيه', icon: 'gamepad', color: '#06b6d4', type: 'expense' },
  { id: 6, name: 'تسوق', icon: 'shopping-bag', color: '#8b5cf6', type: 'expense' },
  { id: 7, name: 'صحة', icon: 'heart', color: '#ec4899', type: 'expense' },
  { id: 8, name: 'تعليم', icon: 'book-open', color: '#6366f1', type: 'expense' },
  { id: 9, name: 'أخرى', icon: 'more-horizontal', color: '#6b7280', type: 'expense' },
];

export const INCOME_CATEGORIES: Category[] = [
  { id: 10, name: 'راتب', icon: 'briefcase', color: '#16a34a', type: 'income' },
  { id: 11, name: 'عمل حر', icon: 'laptop', color: '#0ea5e9', type: 'income' },
  { id: 12, name: 'استثمار', icon: 'trending-up', color: '#7c3aed', type: 'income' },
  { id: 13, name: 'هدية', icon: 'gift', color: '#d946ef', type: 'income' },
  { id: 14, name: 'أخرى', icon: 'more-horizontal', color: '#6b7280', type: 'income' },
];
