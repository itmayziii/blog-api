<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWebpagesTable extends Migration
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
            $table->integer('category_id')->nullable()->unsigned();
            $table->string('slug');
            $table->unsignedInteger('type_id')->nullable();
            $table->boolean('is_live');
            $table->string('title', 200);
            $table->text('short_description')->nullable();
            $table->string('image_path_sm')->nullable();
            $table->string('image_path_md')->nullable();
            $table->string('image_path_lg')->nullable();
            $table->string('image_path_meta')->nullable();

            $table->unique(['slug', 'type_id']);
            $table->foreign('created_by')->references('id')->on('users');
            $table->foreign('last_updated_by')->references('id')->on('users');
            $table->foreign('category_id')->references('id')->on('categories');
            $table->foreign('type_id')->references('id')->on('webpage_types');
        });
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
