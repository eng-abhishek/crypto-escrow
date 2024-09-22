<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFeaturedProuctPurchasesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('featured_prouct_purchases', function (Blueprint $table) {
            $table->increments('id');
            $table->string('user_id',225);
            $table->string('product_id',225);
            $table->string('address',225);
            $table->string('coin',50);
            $table->enum('status',['0','1'])->default('0');
            $table->string('pay_amount',150)->nullable();
            $table->string('required_btc_amount',120)->nullable();
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
        Schema::dropIfExists('featured_prouct_purchases');
    }
}
