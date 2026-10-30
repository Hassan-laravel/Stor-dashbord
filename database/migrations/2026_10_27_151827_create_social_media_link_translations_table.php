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
    Schema::create('social_media_link_translations', function (Blueprint $table) {
        $table->id();
        $table->foreignId('social_media_link_id')->constrained('social_media_links')->cascadeOnDelete();
        $table->string('locale')->index();
        
        $table->string('name')->nullable(); // مثال: Facebook أو فيسبوك
        $table->string('url')->nullable();  // الرابط الخاص بهذه اللغة
        
        $table->unique(['social_media_link_id', 'locale'], 'sml_trans_unique');
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('social_media_link_translations');
    }
};
