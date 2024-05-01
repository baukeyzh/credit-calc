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
        Schema::create('calc_requests', function (Blueprint $table) {
            $table->id();
            $table->string('phone_number');
            $table->string('iin');
            $table->string('name');
            $table->string('surname');
            $table->bigInteger('price')->unsigned();
            $table->bigInteger('initial_payment')->unsigned();
            $table->bigInteger('additional_income')->nullable()->unsigned();
            $table->bigInteger('partner_income')->nullable()->unsigned();
            $table->integer('children_count')->nullable()->unsigned();
            $table->integer('ads_id');
            $table->integer('user_id');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('calc_requests');
    }
};
