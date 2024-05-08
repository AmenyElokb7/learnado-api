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
        Schema::create('questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quiz_id')->constrained('quizzes')->onDelete('cascade');
            $table->text('question');
            $table->enum('type', ['BINARY', 'QCM', 'OPEN']);
            $table->boolean('is_valid')->nullable();
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
        Schema::dropIfExists('questions');
    }
};
