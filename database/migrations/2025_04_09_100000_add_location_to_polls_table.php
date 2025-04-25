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
        Schema::table('polls', function (Blueprint $table) {
            // Approximate location of the poll's creator, derived from their IP
            // at creation time. All nullable — a lookup may fail or be disabled.
            $table->string('city')->nullable()->after('slug');
            $table->string('region')->nullable()->after('city');
            $table->string('country_code', 2)->nullable()->after('region');
            $table->decimal('latitude', 10, 7)->nullable()->after('country_code');
            $table->decimal('longitude', 10, 7)->nullable()->after('latitude');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('polls', function (Blueprint $table) {
            $table->dropColumn(['city', 'region', 'country_code', 'latitude', 'longitude']);
        });
    }
};
