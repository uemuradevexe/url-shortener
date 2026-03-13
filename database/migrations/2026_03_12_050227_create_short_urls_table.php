<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('short_urls', function (Blueprint $table) {
            $table->id();
            $table->string('short_code', 32)->nullable()->unique();
            $table->text('original_url');
            $table->timestamp('expires_at')->nullable();
            $table->unsignedBigInteger('click_count')->default(0);
            $table->timestamp('last_access_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('short_urls');
    }
};
