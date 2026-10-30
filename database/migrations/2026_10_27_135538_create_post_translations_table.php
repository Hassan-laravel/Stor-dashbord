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
    Schema::create('post_translations', function (Blueprint $table) {
        $table->id();
        $table->foreignId('post_id')->constrained('posts')->cascadeOnDelete();
        $table->string('locale')->index();
        $table->string('title');
        $table->string('slug');
        $table->longText('content')->nullable(); // محتوى الصفحة/المقال
        
        // حقول السيو (اختيارية ومفيدة جداً للمقالات)
        $table->string('meta_title')->nullable();
        $table->text('meta_description')->nullable();
        
        $table->unique(['post_id', 'locale']);
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('post_translations');
    }
};
