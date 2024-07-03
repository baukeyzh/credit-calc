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
        Schema::table('calc_requests', function (Blueprint $table) {
            $table->string('patronymic')->nullable()->after('surname');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('calc_requests', function (Blueprint $table) {
            $table->dropColumn('patronymic');
        });
    }
};
