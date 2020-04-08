<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateExamsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('exams', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('title');
            $table->date('exam_date');
            $table->integer('exam_duration')->comment('in Seconds');
            $table->integer('marks');
            $table->text('subjects')->nullable()->comment('jsonFormat as {"SubjectName":"NoOfQuestion"}');
            $table->integer('positive_marking')->comment('Positive Marks for Every Right Answer');
            $table->double('negative_marking')->comment('Negative Marks for Every Wrong Answer');
            $table->boolean('live_scoring')->default(false);
            $table->boolean('status')->default(false);
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
        Schema::dropIfExists('exams');
    }
}