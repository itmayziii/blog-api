<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schema;

class CreateWebpageTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('webpages', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamps();
            $table->integer('created_by')->unsigned();
            $table->integer('last_updated_by')->unsigned();
            $table->integer('category_id')->unsigned();
            $table->string('path')->unique();
            $table->boolean('is_live');
            $table->string('title', 200);
            $table->longText('content')->nullable();
            $table->string('preview')->nullable();
            $table->string('image_path_sm')->nullable();
            $table->string('image_path_md')->nullable();
            $table->string('image_path_lg')->nullable();
            $table->string('image_path_meta')->nullable();

            $table->foreign('created_by')->references('id')->on('users');
            $table->foreign('last_updated_by')->references('id')->on('users');
            $table->foreign('category_id')->references('id')->on('categories');
        });

        Artisan::call('db:sql', ['file' => 'copy-posts-to-webpages.sql']);
        Schema::dropIfExists('posts');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('webpages');
    }
}
