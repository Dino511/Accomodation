<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // fields for the check-in and check-out process flow
        Schema::table('bookings', function (Blueprint $table) {
            $table->string('guest_type')->default('Visitor');   // Visitor or Contractor
            $table->boolean('verified')->default(false);        // check-in step 4
            $table->boolean('id_surrendered')->default(false);  // check-in step 5
            $table->integer('checkout_step')->default(0);       // last finished check-out step
            $table->string('inspection_notes')->nullable();     // check-out step 3
            $table->string('damage_notes')->nullable();         // check-out step 4
            $table->decimal('charges', 10, 2)->default(0);      // check-out step 4
            $table->boolean('charges_paid')->default(false);    // check-out step 5
            $table->boolean('id_returned')->default(false);     // check-out step 6
            $table->string('room_after')->nullable();           // Cleaning or Maintenance
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn(['guest_type', 'verified', 'id_surrendered', 'checkout_step', 'inspection_notes', 'damage_notes', 'charges', 'charges_paid', 'id_returned', 'room_after']);
        });
    }
};
