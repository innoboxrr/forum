<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateForeignKeys extends Migration
{
    public function up()
    {
        Schema::table('forum_discussion', function (Blueprint $table) {
            // Agregar claves foráneas SIN intentar recrear las columnas
            $table->foreign('forum_category_id')
                ->references('id')->on('forum_categories')
                ->onDelete('cascade')
                ->onUpdate('cascade');

            $table->foreign('user_id')
                ->references('id')->on('users')
                ->onDelete('cascade')
                ->onUpdate('cascade');
        });

        Schema::table('forum_post', function (Blueprint $table) {
            $table->foreign('forum_discussion_id')
                ->references('id')->on('forum_discussion')
                ->onDelete('cascade')
                ->onUpdate('cascade');

            $table->foreign('user_id')
                ->references('id')->on('users')
                ->onDelete('cascade')
                ->onUpdate('cascade');
        });
    }

    public function down()
    {
        Schema::table('forum_discussion', function (Blueprint $table) {
            $table->dropForeign(['forum_category_id']);
            $table->dropForeign(['user_id']);
        });

        Schema::table('forum_post', function (Blueprint $table) {
            $table->dropForeign(['forum_discussion_id']);
            $table->dropForeign(['user_id']);
        });
    }
}
