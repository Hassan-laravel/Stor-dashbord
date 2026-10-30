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
    Schema::create('setting_translations', function (Blueprint $table) {
        $table->id();
        $table->foreignId('setting_id')->constrained('settings')->cascadeOnDelete();
        $table->string('locale')->index();
        
        $table->string('site_name')->nullable(); // اسم الموقع
        $table->text('site_description')->nullable(); // وصف الموقع (SEO)
        $table->string('address')->nullable(); // العنوان الفعلي
        
        $table->unique(['setting_id', 'locale']);
    });
}
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('setting_translations');
    }
};
