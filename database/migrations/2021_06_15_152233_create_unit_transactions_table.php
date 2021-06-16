<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUnitTransactionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('unit_transactions', function (Blueprint $table) {
            $table->id();
            $table->integer('member_id');
            $table->enum('type', ['topup', 'withdraw']);
            $table->decimal('amount_rupiah', 65, 2);
            $table->decimal('amount_unit', 65, 4);
            $table->decimal('total_amount_rupiah', 65, 2);
            $table->decimal('total_amount_unit', 65, 4);
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
        Schema::dropIfExists('unit_transactions');
    }
}
