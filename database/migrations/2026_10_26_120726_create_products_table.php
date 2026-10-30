<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
public function up()
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')->constrained()->cascadeOnDelete();
            $table->foreignId('category_id')->constrained()->cascadeOnDelete();
            
            // بيانات الـ API الثابتة
            $table->string('product_code'); // ASIN لـ Amazon أو EAN لـ Bol
            $table->decimal('price', 10, 2)->nullable();
            $table->string('image_url')->nullable();
            $table->json('gallery_images')->nullable(); // للصور الفرعية
            $table->boolean('in_stock')->default(true);
            $table->string('affiliate_url')->nullable();
            
            // منع إضافة نفس المنتج لنفس المتجر مرتين
            $table->unique(['store_id', 'product_code']);
            $table->timestamps();
        });
    }
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
