<?php

/**
 * Startup file for phpunit tests, providing autoloader and fixture loading.
 *
 * This file does not contain tests.
 * Include this file in the phpunit.xml.dist to be run by all tests.
 *
 * You cannot run Code Sniffer on this file because it uses define(), exit() and function
 *
 * Originally fixtures were saved gzip'ed to save space, but they are small and there seems to be a problem
 * with running tests on large gzip json after extracting it from github, they keep getting corrupted. git
 * will internally compress text anyways, and the delta may save space in the end. The largest file is blacksmith
 * at 4MB which is not that bad really.
 *
 * @see phpunit.xml.dist
 * @internal {@see https://github.com/pwnraid/bnet/blob/master/test/Bootstrap.php pwnraid fork reference}
 * @since 0.1.0 2015-04-29
 */

if (!@include_once __DIR__ . '/../vendor/autoload.php') {
    exit('You must set up the project dependencies, run the following commands:'.PHP_EOL
        .'> wget http://getcomposer.org/composer.phar\n> php composer.phar install'.PHP_EOL
    );
}
