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
    Schema::create('post_category_translations', function (Blueprint $table) {
        $table->id();
        $table->foreignId('post_category_id')->constrained('post_categories')->cascadeOnDelete();
        $table->string('locale')->index();
        $table->string('name');
        $table->string('slug');
        
        $table->unique(['post_category_id', 'locale']);
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('post_category_translations');
    }
};
