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
        Schema::create('courses', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->unsignedBigInteger('category_id');
            $table->foreign('category_id')->references('id')->on('categories')->onDelete('cascade');
            $table->text('description');
            $table->foreignId('added_by')->constrained('users')->onDelete('cascade');
            $table->unsignedBigInteger('language_id');
            $table->foreign('language_id')->references('id')->on('languages')->onDelete('cascade');
            $table->boolean('is_paid');
            $table->decimal('price', 8, 2)->nullable();
            $table->integer('discount')->nullable();
            $table->unsignedBigInteger('facilitator_id')->nullable();
            $table->foreign('facilitator_id')->references('id')->on('users')->onDelete('set null');
            $table->boolean('is_public')->default(true);
            $table->boolean('is_active')->default(false);
            $table->boolean('is_offline')->default(false);
            $table->boolean('has_forum')->default(false);
            $table->integer('teaching_type')->default(0);
            $table->string('link')->nullable();
            $table->unsignedBigInteger('start_time')->nullable();
            $table->unsignedBigInteger('end_time')->nullable();
            $table->string('latitude')->nullable();
            $table->string('longitude')->nullable();
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
        Schema::dropIfExists('courses');

    }
};
