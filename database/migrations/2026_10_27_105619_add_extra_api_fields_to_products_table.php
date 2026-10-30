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
    Schema::table('products', function (Blueprint $table) {
        $table->string('rating')->nullable()->after('price');
        $table->integer('reviews_count')->nullable()->after('rating');
        $table->string('sales_volume')->nullable()->after('reviews_count');
        $table->boolean('is_amazon_choice')->default(false)->after('sales_volume');
        $table->string('availability')->nullable()->after('is_amazon_choice');
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            //
        });
    }
};
