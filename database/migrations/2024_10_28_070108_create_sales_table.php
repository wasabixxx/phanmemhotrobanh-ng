<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('sales', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade'); // Sản phẩm
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');       // Người bán
            $table->foreignId('shift_id')->constrained('shifts')->onDelete('set null');    // Ca làm việc
            $table->integer('quantity');                // Số lượng bán
            $table->decimal('total_price', 10, 2);      // Tổng tiền bán
            $table->decimal('profit', 10, 2);           // Lợi nhuận
            $table->timestamps();
        });
    }
    

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sales');
    }
};
