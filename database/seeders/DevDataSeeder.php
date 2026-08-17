<?php

namespace Database\Seeders;

use App\Models\Transaction;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DevDataSeeder extends Seeder
{
    public function run(): void
    {
        $u = User::firstOrCreate(
            ['email' => 'test@example.com'],
            ['name' => 'سعد', 'password' => Hash::make('password'), 'email_verified_at' => now()]
        );

        $expenseCats = [1, 2, 3, 4, 5, 6, 7, 8, 9];
        $expDesc = ['غداء في مطعم', 'قهوة الصباح', 'بقالة أسبوعية', 'بنزين', 'فاتورة كهرباء', 'اشتراك نتفلكس', 'ملابس جديدة', 'صيدلية', 'كتب دراسية', 'فاتورة جوال', 'وجبة عشاء', 'مشتريات سوبرماركت', 'تكسي', 'صيانة سيارة', 'قهوة', 'حلويات'];

        Transaction::where('user_id', $u->id)->forceDelete();
        $rows = [];
        for ($m = 0; $m < 5; $m++) {
            $base = now()->subMonths($m);
            $rows[] = ['user_id' => $u->id, 'category_id' => 10, 'amount' => 12000, 'description' => 'راتب شهري', 'date' => $base->copy()->startOfMonth()->addDays(1)->toDateString(), 'type' => 'income', 'created_at' => now(), 'updated_at' => now()];
            if ($m % 2 === 0) {
                $rows[] = ['user_id' => $u->id, 'category_id' => 11, 'amount' => rand(800, 3500), 'description' => 'مشروع عمل حر', 'date' => $base->copy()->addDays(rand(3, 20))->toDateString(), 'type' => 'income', 'created_at' => now(), 'updated_at' => now()];
            }
            $n = rand(18, 28);
            for ($i = 0; $i < $n; $i++) {
                $cat = $expenseCats[array_rand($expenseCats)];
                $amt = [1 => rand(25, 120), 2 => rand(30, 200), 3 => 2500, 4 => rand(150, 600), 5 => rand(40, 90), 6 => rand(80, 700), 7 => rand(50, 300), 8 => rand(100, 450), 9 => rand(20, 150)][$cat] ?? rand(20, 300);
                $rows[] = ['user_id' => $u->id, 'category_id' => $cat, 'amount' => $amt, 'description' => $expDesc[array_rand($expDesc)], 'date' => $base->copy()->addDays(rand(0, 27))->toDateString(), 'type' => 'expense', 'created_at' => now(), 'updated_at' => now()];
            }
        }
        foreach (array_chunk($rows, 50) as $c) {
            Transaction::insert($c);
        }
        $this->command->info('Dev user test@example.com (password) with '.Transaction::where('user_id', $u->id)->count().' transactions.');
    }
}
