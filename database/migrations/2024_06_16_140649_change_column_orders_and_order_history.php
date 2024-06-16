<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeColumnOrdersAndOrderHistory extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('order_history', function (Blueprint $table) {
            $table->string('no_hp',12)->change();
            $table->dropUnique('order_history_no_hp_unique');
        });
        Schema::table('orders', function (Blueprint $table) {
            $table->string('no_hp',12)->change();
            $table->dropUnique('orders_no_hp_unique');
        });
    }
    
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('order_history', function (Blueprint $table) {
            $table->string('no_hp',12)->unique()->change();
        });
        Schema::table('orders', function (Blueprint $table) {
            $table->string('no_hp',12)->unique()->change();
        });
    }
}
