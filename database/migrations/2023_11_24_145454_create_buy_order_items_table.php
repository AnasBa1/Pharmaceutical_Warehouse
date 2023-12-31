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
        Schema::create('buy_order_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('ordered_quantity');
            $table->foreignId('medication_id')
                ->constrained('medications')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();
            $table->foreignId('buy_order_id')
                ->constrained('buy_orders')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('buy_order_items');
    }
};
