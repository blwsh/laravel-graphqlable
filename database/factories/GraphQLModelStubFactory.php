<?php

use Faker\Generator as Faker;

/*
|--------------------------------------------------------------------------
| Model Factories
|--------------------------------------------------------------------------
|
| This directory should contain each of the model factory definitions for
| your application. Factories provide a convenient way to generate new
| model instances for testing / seeding your application's database.
|
*/

$factory->define(App\GraphQLModelStub::class, function (Faker $faker) {
    return [
        'boolean' => $faker->boolean,
        'char' => $faker->word,
        'date' => $faker->date(),
        'dateTime' => $faker->dateTime(),
        'ipAddress' => $faker->ipv4,
        'text' => json_encode($faker->sentences()),
        'longText' => $faker->text,
        'macAddress' => $faker->macAddress,
        'mediumInteger' => 1,
        'mediumText' => $faker->text,
        'time' => $faker->time(),
        'year' => $faker->year,
        'enum' => 'easy',
        'binary' => 10101,
        'decimal' => 1.0,
        'double' => 1.0,
        'float' => 1.0,
        'integer' => 1,
        'tinyInteger' => 1,
        'unsignedBigInteger' => 1,
        'unsignedDecimal' => 1,
        'unsignedInteger' => 1,
        'unsignedMediumInteger' => 1,
        'unsignedSmallInteger' => 1,
        'unsignedTinyInteger' => 1,
        'uuid' => 1,
    ];
});
