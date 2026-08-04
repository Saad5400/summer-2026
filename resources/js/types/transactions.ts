export type TransactionType = 'expense' | 'income';

export interface Category {
  id: number;
  name: string;
  icon: string | null;
  color: string | null;
  type: TransactionType;
  user_id: number | null;
}

export interface Transaction {
  id: number;
  amount: number;
  description: string;
  date: string;
  type: TransactionType;
  category_id: number | null;
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

