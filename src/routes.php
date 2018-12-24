<?php

use UniBen\LaravelGraphQLable\Controllers\GraphQLController;

// GraphQL
Route::any('/graphql', GraphQLController::class . '@view');
