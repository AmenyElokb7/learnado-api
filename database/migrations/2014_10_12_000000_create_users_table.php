<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use \App\Enum\UserRoleEnum;
return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('email')->unique();
            /** ENUM */
            $table->unsignedBigInteger('role')->default(UserRoleEnum::USER->value);
            /** end ENUM */
            $table->string('password')->nullable();
            $table->boolean('is_valid')->default(false);
            $table->unsignedBigInteger('created_at');
            $table->unsignedBigInteger('updated_at');
            $table->softDeletesBigInteger();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
