<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('media', function (Blueprint $table) {
            $table->id();
            $table->string('model_type');
            $table->unsignedBigInteger('model_id');
            $table->string('file_name')->nullable();
            $table->string('mime_type')->nullable();
            $table->string('title')->nullable();
            $table->unsignedBigInteger('created_at')->default(now()->timestamp);
            $table->unsignedBigInteger('updated_at')->default(now()->timestamp);
            $table->index(['model_type', 'model_id']);
            $table->softDeletesBigInteger();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('media');
    }
};
