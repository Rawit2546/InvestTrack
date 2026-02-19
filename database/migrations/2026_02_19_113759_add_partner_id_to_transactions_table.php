<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPartnerIdToTransactionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
   public function up()
{
    Schema::table('transactions', function (Blueprint $table) {
        // เพิ่มคอลัมน์ partner_id และยอมให้เป็น null ได้ (สำหรับปันผลเข้าพอร์ตหลัก)
        $table->foreignId('partner_id')->nullable()->after('business_id')->constrained('partners')->onDelete('cascade');
    });
}

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('transactions', function (Blueprint $table) {
            //
        });
    }
}
