<?php

declare(strict_types=1);

use App\Enums\OrderStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table): void {
            $table->id();
            $table->string('order_number', 8)->unique();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('branch_id')->constrained()->onDelete('cascade');
            $table->string('status')->default(OrderStatus::PENDING->value);
            $table->decimal('total_price', 10, 2);
            $table->integer('estimated_preparation_time')->nullable();
            $table->timestamp('ready_at')->nullable();

            $table->string('payment_method')->nullable();
            $table->string('payment_reference')->nullable();
            $table->string('payment_provider')->nullable();

            $table->text('customer_note')->nullable();

            $table->json('products_snapshot')->nullable();

            $table->timestamps();

            $table->index(['user_id', 'status']);
            $table->index(['branch_id', 'status']);
            $table->index('order_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
