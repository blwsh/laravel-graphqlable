<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class GraphQLTests extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('test_graphql', function(Blueprint $table) {
            $table->binary('binary')->nullable();
            $table->boolean('boolean')->nullable();
            $table->char('char', 100)->nullable();
            $table->date('date')->nullable();
            $table->dateTime('dateTime')->nullable();
            $table->decimal('decimal', 8, 2)->nullable();
            $table->double('double', 8, 2)->nullable();
            $table->enum('enum', ['easy', 'hard'])->nullable();
            $table->float('float', 8, 2)->nullable();
            $table->increments('increments')->nullable();
            $table->integer('integer')->nullable();
            $table->ipAddress('ipAddress')->nullable();
            $table->longText('longText')->nullable();
            $table->macAddress('macAddress')->nullable();
            $table->mediumInteger('mediumInteger')->nullable();
            $table->mediumText('mediumText')->nullable();
            $table->rememberToken()->nullable();
            $table->softDeletes()->nullable();
            $table->text('text')->nullable();
            $table->time('time')->nullable();
            $table->timestamps();
            $table->tinyInteger('tinyInteger')->nullable();
            $table->unsignedBigInteger('unsignedBigInteger')->nullable();
            $table->unsignedDecimal('unsignedDecimal', 8, 2)->nullable();
            $table->unsignedInteger('unsignedInteger')->nullable();
            $table->unsignedMediumInteger('unsignedMediumInteger')->nullable();
            $table->unsignedSmallInteger('unsignedSmallInteger')->nullable();
            $table->unsignedTinyInteger('unsignedTinyInteger')->nullable();
            $table->uuid('uuid')->nullable();
            $table->year('year')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('test_graphql');
    }
}
