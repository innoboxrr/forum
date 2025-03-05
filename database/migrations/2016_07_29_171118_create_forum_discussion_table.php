<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateForumDiscussionTable extends Migration
{
    public function up()
    {
        Schema::create('forum_discussion', function (Blueprint $table) {
            $table->id('id');
            $table->foreignId('forum_category_id')->default('1');
            $table->string('title');
            $table->foreignId('user_id');
            $table->boolean('sticky')->default(false);
            $table->unsignedBigInteger('views')->default('0');
            $table->boolean('answered')->default(0);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::drop('forum_discussion');
    }
}
