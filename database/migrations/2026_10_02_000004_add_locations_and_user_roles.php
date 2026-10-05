<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // the admin sets up locations; each location has rooms
        Schema::create('locations', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('description')->nullable();
            $table->timestamps();
        });

        Schema::table('rooms', function (Blueprint $table) {
            $table->foreignId('location_id')->nullable()->constrained('locations');
        });

        // move the old location text of each room into the new table
        foreach (DB::table('rooms')->distinct()->pluck('location') as $name) {
            $id = DB::table('locations')->insertGetId(['name' => $name, 'created_at' => now(), 'updated_at' => now()]);
            DB::table('rooms')->where('location', $name)->update(['location_id' => $id]);
        }

        Schema::table('rooms', function (Blueprint $table) {
            $table->dropColumn('location');
        });

        // accounts: admin or reception, and can be turned off
        Schema::table('users', function (Blueprint $table) {
            $table->string('role')->default('reception');
            $table->boolean('active')->default(true);
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['role', 'active']);
        });

        Schema::table('rooms', function (Blueprint $table) {
            $table->string('location')->default('');
            $table->dropConstrainedForeignId('location_id');
        });

        Schema::dropIfExists('locations');
    }
};
