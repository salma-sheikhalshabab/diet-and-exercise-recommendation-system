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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->date('age');
            $table->enum('gender',['female','male']);
            $table->float('height');
            $table->float('weight');
            $table->enum('activity_level',['no exercise','lightly active ','moderately active','very active', 'super active']);
            $table->json('disease')->nullable();
            $table->json('allergy')->nullable();
            $table->float('target_weight')->default(0.0);
            $table->rememberToken();
            $table->timestamps();
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
