<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::create('category_product', function (Blueprint $table): void {
            $table->id();

            $table->foreignId('category_id');
            $table->foreignId('product_id');

            $table->unique(['category_id', 'product_id']);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('category_product');
    }
};
