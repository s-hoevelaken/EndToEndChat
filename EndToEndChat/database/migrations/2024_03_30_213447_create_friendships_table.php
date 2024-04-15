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
        Schema::create('friendships', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('requester_id');
            $table->unsignedBigInteger('addressee_id');

            $table->foreign('requester_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('addressee_id')->references('id')->on('users')->onDelete('cascade');
            
            $table->string('status')->default('pending');

            $table->timestamps();

            $table->index(['requester_id', 'addressee_id']);

            $table->unique(['requester_id', 'addressee_id'], 'unique_friendships');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('friendships');
    }
};
