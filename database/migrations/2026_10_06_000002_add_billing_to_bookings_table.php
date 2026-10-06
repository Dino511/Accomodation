<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->string('billing_rate_type')->nullable();
            $table->decimal('billing_rate', 10, 2)->nullable();
            $table->timestamp('checked_in_at')->nullable();
            $table->timestamp('checkout_verified_at')->nullable();
            $table->json('additional_charges')->nullable();
            $table->decimal('amount_paid', 10, 2)->default(0);
        });

        DB::table('bookings')
            ->where('charges_paid', true)
            ->where('charges', '>', 0)
            ->update(['amount_paid' => DB::raw('charges')]);
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn([
                'billing_rate_type',
                'billing_rate',
                'checked_in_at',
                'checkout_verified_at',
                'additional_charges',
                'amount_paid',
            ]);
        });
    }
};
