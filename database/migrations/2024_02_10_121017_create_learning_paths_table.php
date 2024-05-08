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
        Schema::create('learning_paths', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('description');
            $table->foreignId('added_by')->constrained('users')->onDelete('cascade');
            $table->decimal('price', 8, 2)->nullable();
            $table->boolean('is_public')->default(true);
            $table->boolean('is_active')->default(false);
            $table->boolean('is_offline')->default(false);
            $table->unsignedBigInteger('created_at')->default(now()->timestamp);
            $table->unsignedBigInteger('updated_at')->default(now()->timestamp);
            $table->softDeletesBigInteger();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('learning_paths');
    }
};
