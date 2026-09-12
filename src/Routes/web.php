<?php

/**
 * Helpers.
 */

// Route helper.
$route = function ($accessor, $default = '') {
    return $this->app->config->get('forum.routes.'.$accessor, $default);
};

// Middleware helper.
$middleware = function ($accessor, $default = []) {
    return $this->app->config->get('forum.middleware.'.$accessor, $default);
};

// Authentication middleware helper.
$authMiddleware = function ($accessor) use ($middleware) {
    return array_unique(
        array_merge((array) $middleware($accessor), ['auth'])
    );
};

/*
 * Forum routes.
 */
Route::group([
    'as'         => 'forum.',
    'prefix'     => $route('home'),
    'middleware' => $middleware('global', 'web'),
    'namespace'  => 'Innoboxrr\Forum\Controllers',
], function () use ($route, $middleware, $authMiddleware) {

    // Home view.
    Route::get('/', [
        'as'         => 'home',
        'uses'       => 'ForumController@index',
        'middleware' => $middleware('home'),
    ]);

    // Single category view.
    Route::get($route('category').'/{slug}', [
        'as'         => 'category.show',
        'uses'       => 'ForumController@index',
        'middleware' => $middleware('category.show'),
    ]);

    /*
     * Auth routes.
     */

    // Login view.
    Route::get('login', [
        'as'   => 'login',
        'uses' => 'ForumController@login',
    ]);

    // Register view.
    Route::get('register', [
        'as'   => 'register',
        'uses' => 'ForumController@register',
    ]);

    /*
     * Discussion routes.
     */
    Route::group([
        'as'     => 'discussion.',
        'prefix' => $route('discussion'),
    ], function () use ($middleware, $authMiddleware) {

        // All discussions view.
        Route::get('/', [
            'as'         => 'index',
            'uses'       => 'ForumDiscussionController@index',
            'middleware' => $middleware('discussion.index'),
        ]);

        // Create discussion view.
        Route::get('create', [
            'as'         => 'create',
            'uses'       => 'ForumDiscussionController@create',
            'middleware' => $authMiddleware('discussion.create'),
        ]);

        // Store discussion action.
        Route::post('/', [
            'as'         => 'store',
            'uses'       => 'ForumDiscussionController@store',
            'middleware' => $authMiddleware('discussion.store'),
        ]);

        // Single discussion view.
        Route::get('{category}/{slug}', [
            'as'         => 'showInCategory',
            'uses'       => 'ForumDiscussionController@show',
            'middleware' => $middleware('discussion.show'),
        ]);

        // Add user notification to discussion
        Route::post('{category}/{slug}/email', [
            'as'         => 'email',
            'uses'       => 'ForumDiscussionController@toggleEmailNotification',
        ]);

        /*
         * Specific discussion routes.
         */
        Route::group([
            'prefix' => '{discussion}',
        ], function () use ($middleware, $authMiddleware) {

            // Single discussion view.
            Route::get('/', [
                'as'         => 'show',
                'uses'       => 'ForumDiscussionController@show',
                'middleware' => $middleware('discussion.show'),
            ]);

            // Edit discussion view.
            Route::get('edit', [
                'as'         => 'edit',
                'uses'       => 'ForumDiscussionController@edit',
                'middleware' => $authMiddleware('discussion.edit'),
            ]);

            // Update discussion action.
            Route::match(['PUT', 'PATCH'], '/', [
                'as'         => 'update',
                'uses'       => 'ForumDiscussionController@update',
                'middleware' => $authMiddleware('discussion.update'),
            ]);

            // Destroy discussion action.
            Route::delete('/', [
                'as'         => 'destroy',
                'uses'       => 'ForumDiscussionController@destroy',
                'middleware' => $authMiddleware('discussion.destroy'),
            ]);
        });
    });

    /*
     * Post routes.
     */
    Route::group([
        'as'     => 'posts.',
        'prefix' => $route('post', 'posts'),
    ], function () use ($middleware, $authMiddleware) {

        // All posts view.
        Route::get('/', [
            'as'         => 'index',
            'uses'       => 'ForumPostController@index',
            'middleware' => $middleware('post.index'),
        ]);

        // Store post action.
        //
        // No hay vista de alta, de detalle ni de edicion de un post: el
        // controlador no tiene create(), show() ni edit(), y esas tres rutas
        // devolvian un 500. Un post se crea, edita y borra desde la discusion.
        Route::post('/', [
            'as'         => 'store',
            'uses'       => 'ForumPostController@store',
            'middleware' => $authMiddleware('post.store'),
        ]);

        /*
         * Specific post routes.
         */
        Route::group([
            'prefix' => '{post}',
        ], function () use ($middleware, $authMiddleware) {

            // Update post action.
            Route::match(['PUT', 'PATCH'], '/', [
                'as'         => 'update',
                'uses'       => 'ForumPostController@update',
                'middleware' => $authMiddleware('post.update'),
            ]);

            // Destroy post action.
            Route::delete('/', [
                'as'         => 'destroy',
                'uses'       => 'ForumPostController@destroy',
                'middleware' => $authMiddleware('post.destroy'),
            ]);
        });
    });
});

/*
 * Atom routes
 */
Route::get($route('home').'.atom', [
    'as'         => 'forum.atom',
    'uses'       => 'Innoboxrr\Forum\Controllers\ForumAtomController@index',
    'middleware' => $middleware('home'),
]);
