<?php

use UniBen\LaravelGraphQLable\controllers\GraphQLController;

// GraphQL
Route::redirect('/graphiql', '/graphql-playground');
Route::middleware('api')->any('/graphql', GraphQLController::class . '@view');
Route::middleware('api')->get('/graphql', GraphQLController::class . '@view');

