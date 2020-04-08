<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateQuestionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('questions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('subject_id');
            $table->integer('exam_id')->comment('Can have multiple ids seperated by coma(,)')->nullable();
            $table->integer('topic_id')->nullable();
            $table->enum('type', ['MC'])->default('MC');
            $table->string('title');
            $table->text('available_answers')->comment('Json Format');
            $table->string('right_answer')->value(1)->comment('Accept Single Character such as A/B/C/D. Modify later, if type will increase');
            $table->boolean('status')->default(true);
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
        Schema::dropIfExists('questions');
    }
}
