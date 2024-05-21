<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->string('username');
            $table->string('email');
            $table->string('seller_name');
            $table->string('seller_email');
            $table->json('items');
            // total price
            $table->decimal('total', 10, 2);
            $table->unsignedBigInteger('payment_id');
            $table->unsignedBigInteger('created_at');
            $table->unsignedBigInteger('updated_at');
            $table->softDeletesBigInteger();
            // foreign key payment_id
            $table->foreign('payment_id')->references('id')->on('payments')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
