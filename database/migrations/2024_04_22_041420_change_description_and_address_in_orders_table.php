<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeDescriptionAndAddressInOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('address',1000)->change();
            $table->renameColumn('description', 'note');
        });
    }

    /**
     * Reverse the migrations.
     *x 
     * @return void
     */
    public function down()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('address',500)->change();
            $table->renameColumn('note', 'description');
        });
    }
}
