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

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Perbility\Bundle\BCryptBundle\BCrypt\BCrypt;

/**
 * This is the class that validates and merges configuration from the app/config files
 *
 * @author Benjamin Zikarsky <benjamin.zikarsky.de>
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritDoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('perbility_bcrypt')
            ->children()
                ->scalarNode('cost_factor')->defaultValue(BCrypt::DEFAULT_COST_FACTOR)->end()
                ->scalarNode('iterations')->defaultValue(-1)->end()
                ->scalarNode('global_salt')->isRequired()->cannotBeEmpty()->end()
            ->end();

        return $treeBuilder;
    }
}
