<?php

/*
 * This file is part of the PerbilityBCryptBundle package.
 *
 * (c) PERBILITY GmbH <http://www.perbility.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Perbility\Bundle\BCryptBundle\DependencyInjection;

use Perbility\Bundle\BCryptBundle\BCrypt\BCrypt;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

/**
 * @author Benjamin Zikarsky <benjamin.zikarsky@perbility.de>
 */
class PerbilityBCryptExtension extends Extension
{
    /**
     * {@inheritDoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new Loader\XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.xml');

        $container->setParameter('perbility_bcrypt.cost_factor', $this->getCostFactor($config));
        $container->setParameter('perbility_bcrypt.global_salt', $config['global_salt']);
    }

    /**
     * {@inheritDoc}
     * 
     * @codeCoverageIgnore
     */
    public function getAlias()
    {
        return "perbility_bcrypt";
    }
    
    /**
     * Gets the correct cost-factor from config while also checking the deprecated
     * old "iterations" key
     * 
     * @param array $config
     * @return int
     */
    private function getCostFactor($config) 
    {
        if ($config['iterations'] < 0) {
           return $config['cost_factor'];
        }
        
        if ($config['cost_factor'] != BCrypt::DEFAULT_COST_FACTOR) {
            throw new \LogicException("There is a config value for both the deprecated perbility_bcrypt.iterations and the semantically identical perbility_bcrypt.cost_factor");
        }
        
        // @TODO Log deprecated warning?
        return $config['iterations'];
    }
}
