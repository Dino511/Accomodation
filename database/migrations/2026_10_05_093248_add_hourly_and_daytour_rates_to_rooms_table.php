<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('rooms', function (Blueprint $table) {
            $table->decimal('rate_hourly', 10, 2)->nullable()->after('rate');
            $table->decimal('rate_daytour', 10, 2)->nullable()->after('rate_hourly');
        });
    }

    public function down(): void
    {
        Schema::table('rooms', function (Blueprint $table) {
            $table->dropColumn(['rate_hourly', 'rate_daytour']);
        });
    }
};
