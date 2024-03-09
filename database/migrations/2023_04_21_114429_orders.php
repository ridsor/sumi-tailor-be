<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class Orders extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('orders', function(Blueprint $table) {
            $table->string('item_code',10)->primary();
            $table->string('name',100);
            $table->string('email',100)->unique();
            $table->string('no_hp',12)->unique();
            $table->string('address',500);
            $table->text('description');
            $table->integer('price')->nullable();
            $table->string('status',10)->default('isProcess');
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
        Schema::dropIfExists('orders');
    }
}
