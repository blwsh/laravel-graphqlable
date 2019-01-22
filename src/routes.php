<?php

use UniBen\LaravelGraphQLable\controllers\GraphQLController;

// GraphQL
Route::any('/graphql', GraphQLController::class . '@view');
