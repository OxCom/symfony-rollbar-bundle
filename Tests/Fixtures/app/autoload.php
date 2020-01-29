<?php

use Doctrine\Common\Annotations\AnnotationRegistry;
use Composer\Autoload\ClassLoader;

/** @var ClassLoader $loader */
$loader = require __DIR__ . '/../../../vendor/autoload.php';

AnnotationRegistry::registerLoader([$loader, 'loadClass']);

if (class_exists('\PHPUnit\Framework\ExpectationFailedException') && !class_exists(('PHPUnit_Framework_ExpectationFailedException'))) {
    class_alias('\PHPUnit\Framework\ExpectationFailedException', 'PHPUnit_Framework_ExpectationFailedException');
}

return $loader;
