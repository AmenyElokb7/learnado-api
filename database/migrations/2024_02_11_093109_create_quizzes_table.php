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
        Schema::create('quizzes', function (Blueprint $table) {
            $table->id();
            // nullable when the quiz is  an exam
            $table->foreignId('step_id')->nullable()->constrained('steps')->onDelete('cascade');
            // nullable when the quiz is not an exam
            $table->foreignId('learning_path_id')->nullable()->constrained('learning_paths')->onDelete('cascade');
            $table->boolean('is_exam')->default(false);
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
        Schema::dropIfExists('quizzes');
    }
};
