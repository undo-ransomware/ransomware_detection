<?php

if (!defined('PHPUNIT_RUN')) {
    define('PHPUNIT_RUN', 1);
}

require_once __DIR__.'/../../../lib/base.php';

\OC::$loader->addValidRoot(OC::$SERVERROOT.'/tests');
\OC_App::loadApp('ransomware_detection');

if (!class_exists('\PHPUnit\Framework\TestCase')) {
    require_once 'PHPUnit/Autoload.php';
}

OC_Hook::clear();
