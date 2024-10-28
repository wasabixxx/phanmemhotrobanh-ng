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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('username')->unique();      // Tên đăng nhập
            $table->string('password');                // Mật khẩu
            $table->string('name');                    // Tên nhân viên
            $table->string('phone')->nullable();       // Số điện thoại
            $table->string('email')->unique();         // Email
            $table->foreignId('role_id')->constrained('roles')->onDelete('cascade'); // Vai trò
            $table->foreignId('shift_id')->constrained('shifts')->onDelete('set null'); // Ca làm việc
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
