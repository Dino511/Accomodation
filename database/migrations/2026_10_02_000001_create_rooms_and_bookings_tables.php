<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rooms', function (Blueprint $table) {
            $table->id();
            $table->string('room_no');
            $table->string('location');
            $table->integer('capacity');
            $table->decimal('rate', 10, 2);
            $table->string('status')->default('Available'); // Available, Occupied, Cleaning
            $table->timestamps();
        });

        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->string('guest_name');
            $table->string('company')->nullable();
            $table->string('contact_no');
            $table->foreignId('room_id')->constrained('rooms');
            $table->integer('no_of_guests');
            $table->date('check_in');
            $table->date('check_out');
            $table->string('status')->default('Reserved'); // Reserved, Checked In, Checked Out, Cancelled
            $table->string('remarks')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bookings');
        Schema::dropIfExists('rooms');
    }
};
