<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateForumUserDiscussionPivotTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('forum_user_discussion', function (Blueprint $table) {
            $table->foreignId('user_id')->index();
            $table->foreignId('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreignId('discussion_id')->index();
            $table->foreignId('discussion_id')->references('id')->on('forum_discussion')->onDelete('cascade');
            $table->primary(['user_id', 'discussion_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('forum_user_discussion');
    }
}
