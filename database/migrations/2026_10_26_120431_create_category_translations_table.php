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
        Schema::create('category_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained()->cascadeOnDelete();
            $table->string('locale')->index(); // اللغات: en, ar, nl, tr

            // الحقول النصية المراد ترجمتها (تطابق ما في الـ Controller الخاص بك)
            $table->string('name');
            $table->string('slug');
            $table->text('description')->nullable();

            // ضمان عدم تكرار الترجمة لنفس اللغة في نفس التصنيف
            $table->unique(['category_id', 'locale']);
        });
    }
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('category_translations');
    }
};
