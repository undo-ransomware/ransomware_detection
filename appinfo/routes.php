<?php

/**
 * Create your routes in here. The name is the lowercase name of the controller
 * without the controller part, the stuff after the hash is the method.
 * e.g. page#index -> OCA\RansomwareDetection\Controller\PageController->index().
 *
 * The controller class has to be registered in the application.php file since
 * it's instantiated in there
 */
return [
    'routes' => [
        ['name' => 'recover#index', 'url' => '/', 'verb' => 'GET'],
        // File operation controller
        ['name' => 'fileOperation#findAll', 'url' => '/api/{apiVersion}/file-operation', 'verb' => 'GET', 'requirements' => ['apiVersion' => 'v1']],
        // Basic controller
        ['name' => 'basic#changeColorMode', 'url' => '/api/{apiVersion}/change-color-mode/{colorMode}', 'verb' => 'GET', 'requirements' => ['apiVersion' => 'v1']],
        ['name' => 'basic#getColorMode', 'url' => '/api/{apiVersion}/get-color-mode', 'verb' => 'GET', 'requirements' => ['apiVersion' => 'v1']],
        ['name' => 'basic#getDebugMode', 'url' => '/api/{apiVersion}/get-debug-mode', 'verb' => 'GET', 'requirements' => ['apiVersion' => 'v1']],
    ],
];
