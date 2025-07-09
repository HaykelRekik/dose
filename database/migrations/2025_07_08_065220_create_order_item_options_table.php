<?php

declare(strict_types=1);

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
        Schema::create('order_item_options', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('order_item_id')->constrained()->onDelete('cascade');
            $table->foreignId('product_option_group_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('product_option_id')->nullable()->constrained()->nullOnDelete();

            // Snapshot fields
            $table->string('group_name');
            $table->string('group_type');
            $table->boolean('group_is_required');
            $table->string('option_name');
            $table->text('option_description')->nullable();
            $table->decimal('option_extra_price', 10, 2)->default(0);

            $table->timestamps();

            $table->index('order_item_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_item_options');
    }
};
