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

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * This command checks a given hash and value for equality
 *
 * @codeCoverageIgnore
 * @author Benjamin Zikarsky <benjamin.zikarsky@perbility.de>
 */
class CheckCommand extends AbstractCommand
{
    /**
     * @see Command
     */
    protected function configure()
    {
        $this
            ->setName('bcrypt:check')
            ->setDescription('Hashes a given string')
            ->setHelp("The <info>bcrypt:hash</info> hashes the given string. You can override parameters like iteration-count and the global salt")
            ->addOption("hash",        null, InputOption::VALUE_REQUIRED, "The hash to check against")
            ->addOption("value",       null, InputOption::VALUE_OPTIONAL, "The value to be checked against the hash. If ommitted you will be prompted interactively", null)
            ->addOption("global-salt", "gs", InputOption::VALUE_OPTIONAL, "The global salt to be used. Defaults to application-config", null)
            ->addOption("userdata",    null, InputOption::VALUE_OPTIONAL, "Additional user-data to be added into the hash", "")
            ->addoption("silent",      null, InputOption::VALUE_NONE,     "Only returns the actual result without any additional output");
    }

    /**
     * @see Command
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        /* @var $bcrypt \Perbility\Bundle\BCryptBundle\BCrypt\BCrypt */
    	$bcrypt = $this->getContainer()->get('perbility_bcrypt');
        
        
        $salt = $input->getOption("global-salt");
        $userData = $input->getOption("userdata");
        $value = $input->getOption("value");
        $hash = $input->getOption("hash");
        $silent = $input->getOption("silent");

        if ($value == null) {
            $value = $this->readPasswordInteractively("Please enter the value to be checked: ");
        }

        $result = $bcrypt->checkHash($hash, $value, $userData, $salt);

        if ($silent) {
            $output->writeln(intval($result));
            return;
        }

        $modifier = $result ? "" : " not";
        $output->writeln("The hash <info>does$modifier match</info> the given string. (" . intval($result) . ")");
    }
}
