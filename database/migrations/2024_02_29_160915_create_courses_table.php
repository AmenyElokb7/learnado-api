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
            $table->string('category');
            $table->text('description');
            $table->text('prerequisites')->nullable();
            $table->text('course_for')->nullable();
            $table->timestamp('added_at')->useCurrent();
            $table->foreignId('added_by')->constrained('users')->onDelete('cascade');
            $table->string('language');
            $table->boolean('is_paid');
            $table->decimal('price', 8, 2)->nullable();
            $table->integer('discount')->nullable();
            $table->unsignedBigInteger('facilitator_id')->nullable();
            $table->foreign('facilitator_id')->references('id')->on('users')->onDelete('set null');
            $table->boolean('isPublic')->default(true);
            $table->boolean('sequential')->default(false);
            $table->softDeletes();
            $table->timestamps();
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
