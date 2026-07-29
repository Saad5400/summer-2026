<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('category_id')->nullable()->constrained()->nullOnDelete();
            $table->decimal('amount', 12, 2);
            $table->text('description');
            $table->date('date');
            $table->string('type');
            $table->timestamp('ai_categorized_at')->nullable();
            $table->decimal('ai_category_confidence', 4, 2)->nullable();
            $table->boolean('is_anomaly')->default(false);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['user_id', 'date']);
            $table->index(['user_id', 'type', 'date']);
            $table->index(['user_id', 'category_id', 'date']);
            $table->index(['user_id', 'type', 'category_id', 'date']);
            $table->index(['user_id', 'is_anomaly']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
