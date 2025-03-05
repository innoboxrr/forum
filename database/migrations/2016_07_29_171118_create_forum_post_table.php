<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateForumPostTable extends Migration
{
    public function up()
    {
        Schema::create('forum_post', function (Blueprint $table) {
            $table->id('id');
            $table->foreignId('forum_discussion_id');
            $table->foreignId('user_id');
            $table->text('body');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::drop('forum_post');
    }
}
