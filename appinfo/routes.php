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
        ['name' => 'recover#scan', 'url' => '/scan', 'verb' => 'GET'],
    ],
    'ocs' => [
        // Basic controller
        ['name' => 'basic#changeColorMode', 'url' => '/api/{apiVersion}/change-color-mode/{colorMode}', 'verb' => 'GET', 'requirements' => ['apiVersion' => 'v1']],
        ['name' => 'basic#getColorMode', 'url' => '/api/{apiVersion}/get-color-mode', 'verb' => 'GET', 'requirements' => ['apiVersion' => 'v1']],
        ['name' => 'basic#getDebugMode', 'url' => '/api/{apiVersion}/get-debug-mode', 'verb' => 'GET', 'requirements' => ['apiVersion' => 'v1']],
        // Monitoring controller
        ['name' => 'monitoring#listFileOperations', 'url' => '/api/{apiVersion}/list', 'verb' => 'GET', 'requirements' => ['apiVersion' => 'v1']],
        ['name' => 'monitoring#export', 'url' => '/api/{apiVersion}/export', 'verb' => 'GET', 'requirements' => ['apiVersion' => 'v1']],
        ['name' => 'monitoring#deleteSequence', 'url' => '/api/{apiVersion}/delete-sequence/{sequence}', 'verb' => 'GET', 'requirements' => ['apiVersion' => 'v1']],
        ['name' => 'monitoring#recover', 'url' => '/api/{apiVersion}/recover', 'verb' => 'POST', 'requirements' => ['apiVersion' => 'v1']],
        // Scan controller
        ['name' => 'scan#recover', 'url' => '/api/{apiVersion}/scan-recover', 'verb' => 'POST', 'requirements' => ['apiVersion' => 'v1']],
        ['name' => 'scan#filesToScan', 'url' => '/api/{apiVersion}/files-to-scan', 'verb' => 'GET', 'requirements' => ['apiVersion' => 'v1']],
        ['name' => 'scan#scanSequence', 'url' => '/api/{apiVersion}/scan-sequence', 'verb' => 'POST', 'requirements' => ['apiVersion' => 'v1']],
    ],
];
