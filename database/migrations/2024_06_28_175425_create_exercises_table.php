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
        Schema::create('exercises', function (Blueprint $table) {
            $table->id();
            $table->string('muscle_name');
            $table->string('exercise_name');
            $table->text( 'description');
            $table->string('Reps_per_set');
            $table->integer('sets');
            $table->string('gif')->default('/image/exercises/default.jpg');
            $table->float( 'calories');
            $table->time( 'Duration per set');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('exercises');
    }
};
