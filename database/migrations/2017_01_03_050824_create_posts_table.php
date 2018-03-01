<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePostsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('posts', function (Blueprint $table) {
            $table->increments('id');
            $table->string('slug')->unique()->index();
            $table->timestamps();
            $table->integer('user_id')->unsigned();
            $table->integer('category_id')->unsigned();
            $table->string('status', 20)->default('draft');
            $table->string('title', 200)->unique();
            $table->longText('content')->nullable();
            $table->string('preview')->nullable();
            $table->string('image_path')->nullable();

            $table->foreign('user_id')->references('id')->on('users');
            $table->foreign('category_id')->references('id')->on('categories');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('posts');
    }
}
