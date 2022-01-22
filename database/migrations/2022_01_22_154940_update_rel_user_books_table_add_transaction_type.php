<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateRelUserBooksTableAddTransactionType extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {   
        Schema::table('rel_user_books', function (Blueprint $table) {
            $table->enum('type',['rent','return'])->nullable(false)->after('book_id');
            $table->date('rent_date')->nullable(true)->after('remark');
            $table->date('return_date')->nullable(true)->after('rent_date');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('rel_user_books', function($table) {
            $table->dropColumn('type');
            $table->dropColumn('rent_date');
            $table->dropColumn('return_date');
        });
    }
}
