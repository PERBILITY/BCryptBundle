<?php

/*
 * This file is part of the PerbilityBCryptBundle package.
 *
 * (c) PERBILITY GmbH <http://www.perbility.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Perbility\Bundle\BCryptBundle\Tests\DependencyInjection;

use Perbility\Bundle\BCryptBundle\BCrypt\BCrypt;
use Perbility\Bundle\BCryptBundle\DependencyInjection\PerbilityBCryptExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * This class tests the automatic setup of the service with 
 * the bundle's DIC extension
 * 
 * @author Benjamin Zikarsky <benjamin.zikarsky@perbility.de>
 */
class PerbilityBCryptExtensionTest extends \PHPUnit_Framework_TestCase
{
    
    /**
     * @var Perbility\Bundle\BCryptBundle\DependencyInjection\PerbilityBCryptExtension
     */
    private $extension;
    
    /**
     * @var Symfony\Component\DependencyInjection\ContainerBuilder;
     */
    private $container;
    
    /**
     * {@inheritDoc}
     */
    public function setUp()
    {
        $this->extension = new PerbilityBCryptExtension();
        $this->container = new ContainerBuilder();
    }
    
    /**
     * {@inheritDoc}
     */
    public function tearDown()
    {
        $this->extension = null;
        $this->container = null;
    }
    
    /**
     * Tests that an exception is thrown if no global salt is configured
     * 
     * @expectedException \Symfony\Component\Config\Definition\Exception\InvalidConfigurationException
     */
    public function testRequiredGlobalSalt()
    {
        $this->extension->load(
            array('perbility_bcrypt' => array()), 
			$this->container
        );
    }
    
    /**
     * Tests that the bcrypt-service is set-up correctly
     */
    public function testServiceSetup()
    {
        $this->extension->load(
            array('perbility_bcrypt' => array('global_salt' => 'global_salt', 'cost_factor' => BCrypt::MIN_COST_FACTOR)),
            $this->container
        );
        
        $this->assertNotNull($this->container->get('perbility_bcrypt'));
        $this->assertTrue($this->container->get('perbility_bcrypt') instanceof BCrypt);
        
        $this->assertEquals(BCrypt::MIN_COST_FACTOR, $this->container->getParameter('perbility_bcrypt.cost_factor'));
        $this->assertEquals(BCrypt::MIN_COST_FACTOR, $this->container->get('perbility_bcrypt')->getCostFactor());
        
        $this->assertEquals('global_salt', $this->container->getParameter('perbility_bcrypt.global_salt'));
        $this->assertEquals('global_salt', $this->container->get('perbility_bcrypt')->getGlobalSalt());
    }
    
    /**
     * Tests that an invalid iterations value leads to an exception
     * 
     * @expectedException Symfony\Component\Config\Definition\Exception\InvalidConfigurationException
     */
    public function testInvalidCostFactor()
    {
        $this->extension->load(
            array('perbility_bcrypt' => array('global_salt' => 'global_salt', 'cost_factor' => BCrypt::MIN_COST_FACTOR-1)),
            $this->container
        );
    }
    
    /**
     * Tests that the deprecated iterations config-entry still works
     */
    public function testDeprecatedIterations()
    {
        $this->extension->load(
                array('perbility_bcrypt' => array('global_salt' => 'global_salt', 'iterations' => BCrypt::MIN_COST_FACTOR)),
                $this->container
        );
        
        $this->assertNotNull($this->container->get('perbility_bcrypt'));
        $this->assertTrue($this->container->get('perbility_bcrypt') instanceof BCrypt);
        
        $this->assertEquals(BCrypt::MIN_COST_FACTOR, $this->container->getParameter('perbility_bcrypt.cost_factor'));
        $this->assertEquals(BCrypt::MIN_COST_FACTOR, $this->container->get('perbility_bcrypt')->getCostFactor());
    }
    
    /**
     * Tests that specifying both deprecated `iterations` and valid `cost_factor` leads to an exception
     * 
     * @expectedException \LogicException
     */
    public function testExceptionOnBothIterationsAndCostFactor()
    {
        $this->extension->load(
                array('perbility_bcrypt' => array(
                	'global_salt' => 'global_salt', 
                    'iterations' => BCrypt::MIN_COST_FACTOR, 
                    'cost_factor' => BCrypt::MIN_COST_FACTOR
                )),
                $this->container
        );
    }
}
