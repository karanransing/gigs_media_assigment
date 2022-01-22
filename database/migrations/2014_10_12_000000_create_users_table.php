<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id('u_id');
            $table->string('firstname',255)->nullable(false);
			$table->string('lastname',255)->nullable(false);
			$table->bigInteger('mobile')->length(10)->unique()->nullable(false);
            $table->string('email')->unique()->nullable(false);
            $table->string('password',255);
			$table->tinyInteger('age')->length(10)->nullable(false);
			$table->enum('gender',['m','f','o'])->nullable(false);
			$table->string('city',255)->nullable(false);
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
        Schema::dropIfExists('users');
    }
}
