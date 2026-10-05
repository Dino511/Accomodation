<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->string('email')->nullable();
            $table->string('check_in_time')->default('14:00');
            $table->string('check_out_time')->default('12:00');
            $table->string('id_type')->nullable();
            $table->string('id_number')->nullable();
            $table->date('actual_check_out')->nullable();
            $table->string('checkout_notes')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn(['email', 'check_in_time', 'check_out_time', 'id_type', 'id_number', 'actual_check_out', 'checkout_notes']);
        });
    }
};
