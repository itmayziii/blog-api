<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ModifyCategoryIdOnPostsTableToBeNullable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->integer('category_id')->nullable()->unsigned()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;'); // Truncating table will not work because of a foreign key constraint in table "taggables"
        Schema::table('posts', function (Blueprint $table) {
            $table->integer('category_id')->nullable(false)->unsigned()->change();
        });
        DB::statement('SET FOREIGN_KEY_CHECKS=1;'); // Truncating table will not work because of a foreign key constraint in table "taggables"
    }
}
