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
    ],
    'ocs' => [
        ['name' => 'api#listFileOperations', 'url' => '/api/{apiVersion}/list', 'verb' => 'GET', 'requirements' => ['apiVersion' => 'v1']],
        ['name' => 'api#export', 'url' => '/api/{apiVersion}/export', 'verb' => 'GET', 'requirements' => ['apiVersion' => 'v1']],
        ['name' => 'api#deleteSequence', 'url' => '/api/{apiVersion}/delete-sequence/{sequence}', 'verb' => 'GET', 'requirements' => ['apiVersion' => 'v1']],
        ['name' => 'api#recover', 'url' => '/api/{apiVersion}/recover', 'verb' => 'POST', 'requirements' => ['apiVersion' => 'v1']],
        ['name' => 'api#changeColorMode', 'url' => '/api/{apiVersion}/change-color-mode/{colorMode}', 'verb' => 'GET', 'requirements' => ['apiVersion' => 'v1']],
        ['name' => 'api#getColorMode', 'url' => '/api/{apiVersion}/get-color-mode', 'verb' => 'GET', 'requirements' => ['apiVersion' => 'v1']],
        ['name' => 'api#getDebugMode', 'url' => '/api/{apiVersion}/get-debug-mode', 'verb' => 'GET', 'requirements' => ['apiVersion' => 'v1']],
        ['name' => 'analyzer#analyze', 'url' => '/analyzer/{apiVersion}/analyze/{operationId}/{userId}', 'verb' => 'GET', 'requirements' => ['apiVersion' => 'v1']],
    ],
];
