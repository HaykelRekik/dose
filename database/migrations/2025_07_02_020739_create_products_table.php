<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table): void {
            $table->id();
            $table->string('name_en');
            $table->string('name_ar');
            $table->text('description_en')->nullable();
            $table->text('description_ar')->nullable();
            $table->decimal('price', 10, 2)
                ->comment('The starting price in SAR before options.');
            $table->unsignedInteger('estimated_preparation_time')
                ->default(5);
            $table->string('image_url')->nullable();
            $table->boolean('is_active')
                ->default(true)
                ->comment('Toggles product visibility in the app.');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
