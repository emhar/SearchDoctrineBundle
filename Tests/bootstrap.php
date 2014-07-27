<?php

/**
 * @author Emhar
 */
$loader = require __DIR__ . '/../vendor/autoload.php';

Doctrine\Common\Annotations\AnnotationRegistry::registerLoader(function ($class)
{
	return class_exists($class);
});


return $loader;
