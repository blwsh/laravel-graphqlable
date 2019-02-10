<?php

use App\User;
use UniBen\LaravelGraphQLable\controllers\AuthController;
use UniBen\LaravelGraphQLable\controllers\GraphQLController;

// GraphQL
Route::redirect('/graphiql', '/graphql-playground');
Route::any('/graphql', GraphQLController::class . '@view');