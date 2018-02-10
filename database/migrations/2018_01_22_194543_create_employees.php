<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEmployees extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('employees', function( Blueprint $table) {
            $table->increments('id');   // добавить unique
            $table->tinyInteger('deep_level')->unsigned()->comment('Уровень иерархии');
            $table->integer('employee_id')->unsigned()->unique()->comment('ID работника');
            $table->integer('boss_id')->unsigned()->comment('ID начальника');
            $table->string('f')->comment('Фамилия');
            $table->string('i')->comment('Имя');
            $table->string('o')->comment('Отчество');
            $table->string('position')->comment('Должность');
            $table->smallInteger('salary')->unsigned()->comment('Зарплата');
            $table->integer('hiring_date')->unsigned()->comment('Дата приёма на работу');
            $table->longText('subordinates')->comment('Массив ID подчиненных');
            $table->string('img')->nullable()->comment('Имя изображения');
            // $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('employees');
    }
}
