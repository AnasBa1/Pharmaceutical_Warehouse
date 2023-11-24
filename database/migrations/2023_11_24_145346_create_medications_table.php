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
        Schema::create('medications', function (Blueprint $table) {
            $table->id();
            $table->string('scientific_name')->nullable();
            $table->string('trade_name');
            $table->foreignId('medical_classification_id')
                ->constrained('medical_classifications')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();
            $table->string('manufacturer')->nullable();
            $table->integer('available_quantity');
            $table->date('expiration_date');
            $table->integer('price');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('medications');
    }
};
