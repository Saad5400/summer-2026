<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $connection = DB::connection()->getDriverName();

        if ($connection === 'sqlite') {
            DB::statement('DROP INDEX IF EXISTS transactions_user_id_is_anomaly_index');

            DB::statement('CREATE TABLE transactions_new (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                user_id INTEGER NOT NULL,
                category_id INTEGER,
                amount NUMERIC(12,2) NOT NULL,
                description TEXT,
                date DATE NOT NULL,
                type VARCHAR NOT NULL,
                created_at DATETIME,
                updated_at DATETIME,
                deleted_at DATETIME,
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
                FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL
            )');

            DB::statement('INSERT INTO transactions_new
                (id, user_id, category_id, amount, description, date, type, created_at, updated_at, deleted_at)
                SELECT id, user_id, category_id, amount, description, date, type, created_at, updated_at, deleted_at
                FROM transactions');

            DB::statement('DROP TABLE transactions');
            DB::statement('ALTER TABLE transactions_new RENAME TO transactions');

            DB::statement('CREATE INDEX transactions_user_id_date_index ON transactions(user_id, date)');
            DB::statement('CREATE INDEX transactions_user_id_type_date_index ON transactions(user_id, type, date)');
            DB::statement('CREATE INDEX transactions_user_id_category_id_date_index ON transactions(user_id, category_id, date)');
            DB::statement('CREATE INDEX transactions_user_id_type_category_id_date_index ON transactions(user_id, type, category_id, date)');
        } else {
            Schema::table('transactions', function (Blueprint $table) {
                $table->dropIndex(['user_id', 'is_anomaly']);
                $table->dropColumn(['ai_categorized_at', 'ai_category_confidence', 'is_anomaly']);
            });
        }
    }

    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->timestamp('ai_categorized_at')->nullable();
            $table->decimal('ai_category_confidence', 4, 2)->nullable();
            $table->boolean('is_anomaly')->default(false);
            $table->index(['user_id', 'is_anomaly']);
        });
    }
};
