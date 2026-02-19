<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBusinessesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('businesses', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->decimal('investment', 15, 2);
            $table->string('dividend_rate')->nullable(); // Changed to string for flexibility (e.g. "5%")
            $table->date('contract_date');
            $table->string('pay_date')->nullable(); // Changed to string (e.g. "Every 5th")
            $table->string('duration')->nullable(); // Changed to string (e.g. "1 Year")
            $table->text('note')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('businesses');
    }
}
