<?php

/*
 * This file is part of the PerbilityBCryptBundle package.
 *
 * (c) PERBILITY GmbH <http://www.perbility.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Perbility\Bundle\BCryptBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;

/**
 * Abstract class for internal usage which provides some non-standard
 * interaction functionalities
 *
 * @author Benjamin Zikarsky <benjamin.zikarsky@perbility.de>
 */
abstract class AbstractCommand extends ContainerAwareCommand
{
    protected function readPasswordInteractively($prompt)
    {
        $bash = "/usr/bin/env bash -c \" %s\"";

        if (trim(shell_exec(sprintf($bash, "echo 'CHECK BASH'"))) !== 'CHECK BASH') {
            throw new \RuntimeException("Cannot invoke 'bash'. Required for interactive password-input");
        }

        $command = sprintf("read -s -p %s temp_pw", escapeshellarg($prompt)). ' && echo \\$temp_pw';
        $password = trim(shell_exec(sprintf($bash, $command)));
        
        return $password;
    }
}
