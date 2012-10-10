<?php

/*
 * This file is part of the PerbilityBCryptBundle package.
 *
 * (c) PERBILITY GmbH <http://www.perbility.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

$filename = __DIR__ .'/../vendor/autoload.php';

if (!file_exists($filename)) {
    throw new Exception("You need to execute `composer install` before running the tests. (vendors are required for test execution)");
}

require_once $filename;

