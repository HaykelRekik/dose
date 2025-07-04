<?php

declare(strict_types=1);

use App\Enums\ProductOptionGroupType;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    public function up(): void
    {
        Schema::create('product_option_groups', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('product_id');
            $table->string('name_en');
            $table->string('name_ar');
            $table->string('type')->default(ProductOptionGroupType::SINGLE_SELECT->value);
            $table->boolean('is_required')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_option_groups');
    }
};
