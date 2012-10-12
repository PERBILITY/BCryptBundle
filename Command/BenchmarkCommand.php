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

use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputInterface;
use Perbility\Bundle\BCryptBundle\BCrypt\BCrypt;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;

/**
 * This commands provides benchmark information for bcrypt
 * 
 * @author Benjamin Zikarsky <benjamin.zikarsky@perbility.de>
 * @codeCoverageIgnore
 */
class BenchmarkCommand extends ContainerAwareCommand
{
    /**
     * @see Command
     */
    protected function configure()
    {
        $this
        	->setName('bcrypt:benchmark')
        	->setDescription("Runs bcrypt with multiple iteration-configurations and shows benchmarking information")
        	->setHelp(
"The <info>bcrypt:benchmark</info> runs several bcrypt-configruations and displays benchmark information regarding the runtime required for each setup.
You can specify a minimum cost-factor which defines the lowest cost-factor to be tested, and a maximum factor accordingly. 
The later one is only reached if maximum-run-time (specified in ms) is not hit before.")
        	->addOption("min-cost-factor", null, InputOption::VALUE_OPTIONAL, "The minimum cost-factor to test", BCrypt::MIN_COST_FACTOR)
        	->addoption("max-cost-factor", null, InputOption::VALUE_OPTIONAL, "The maximum cost-factor to test (as long as max-run-time is not reached before)", BCrypt::MAX_COST_FACTOR)
        	->addOption("max-run-time",    null, InputOption::VALUE_OPTIONAL, "The maximum run-time per bcrypt-run in milliseconds", 1000)
    	;
    }
    
     /**
     * @see Command
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $maxTime = intval($input->getOption("max-run-time"));
        if ($maxTime < 0) {
            throw new \InvalidArgumentException("Maximum run time cannot be negative");
        }
        
        $output->writeln("Running bcrypt benchmark...");
        $output->writeln("");
        $output->writeln("<info>factor       time</info>");
        
        /* @var $bcrypt \Perbility\Bundle\BCryptBundle\BCrypt\BCrypt */
        $bcrypt = $this->getContainer()->get('perbility_bcrypt');
        for ($i=$input->getOption("min-cost-factor"); $i<$input->getOption("max-cost-factor"); $i++) {
            $start = microtime(true);
            $bcrypt->hash("password", "userdata", $i);
            $time = (microtime(true) - $start)*1000;
            
            $output->writeln(sprintf("    %02d % 8dms", $i, $time));
            
            if ($time > $maxTime) {
                break;
            }
        }
        
        $output->writeln("");
        $output->writeln("...finished!");
    }
}