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
 * This command hashes a given value with the configured bcrypt configuration
 *
 * @author Benjamin Zikarsky <benjamin.zikarsky@perbility.de>
 */
class HashCommand extends AbstractCommand
{
    /**
     * @see Command
     */
    protected function configure()
    {
        $this
            ->setName('bcrypt:hash')
            ->setDescription('Hashes a given string')
            ->setHelp("The <info>bcrypt:hash</info> hashes the given string. You can override parameters like iteration-count and the global salt")
            ->addOption("value",       null, InputOption::VALUE_OPTIONAL, "The value to be hashed. If ommitted you will be prompted interactively", null)
            ->addOption("iterations",  "i",  InputOption::VALUE_OPTIONAL, "Number of becrypt iterations. Defaults to application-config", null)
            ->addOption("global-salt", "gs", InputOption::VALUE_OPTIONAL, "The global salt to be used. Defaults to application-config", null)
            ->addOption("userdata",    null, InputOption::VALUE_OPTIONAL, "Additional user-data to be added into the hash", "")
            ->addoption("silent",      null, InputOption::VALUE_NONE,     "Only returns the actual result without any additional output");
    }

    /**
     * @see Command
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $bcrypt = $this->getContainer()->get('perbility_bcrypt');
        $iterations = $input->getOption("iterations") ?: $bcrypt->getIterations();
        $salt = $input->getOption("global-salt");
        $userData = $input->getOption("userdata");
        $value = $input->getOption("value");
        $silent = $input->getOption("silent");

        if ($value == null) {
            $value = $this->readPasswordInteractively("Please enter the value to be hashed: ");
        }

        $start = microtime(true);
        $result = $bcrypt->hash($value, $userData, $iterations, $salt);
        $time = microtime(true) - $start;

        if ($silent) {
            $output->writeln($result);
            return;
        }

        $output->writeln(sprintf("<info>Result:</info>     %s", $result));
        $output->writeln(sprintf("<info>Iterations:</info> %d", $iterations));
        $output->writeln(sprintf("<info>Comp.-Time:</info> %ss", number_format($time, 3)));
    }
}
