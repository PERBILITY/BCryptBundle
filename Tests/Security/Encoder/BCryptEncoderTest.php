<?php

/*
 * This file is part of the PerbilityBCryptBundle package.
 *
 * (c) PERBILITY GmbH <http://www.perbility.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Perbility\Bundle\BCryptBundle\Tests\Security\Encoder;

use Perbility\Bundle\BCryptBundle\Security\Encoder\BCryptEncoder;
use Perbility\Bundle\BCryptBundle\BCrypt\BCrypt;

/**
 * Tests BCryptEncoder
 * 
 * @author Benjamin Zikarsky <benjamin.zikarsky@perbility.de>
 */
class BCryptEncoderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Symfony\Component\Security\Core\Encoder\PasswordEncoderInterface
     */
    private $encoder = null;
    
    /**
     * @var Perbility\Bundle\BCryptBundle\BCrypt\BCrypt
     */
    private $bcrypt = null;
    
    /**
     * {@inheritDoc}
     */
    public function setUp()
    {
        $this->bcrypt = new BCrypt("global_salt", BCrypt::MIN_COST_FACTOR);
        $this->encoder = new BCryptEncoder($this->bcrypt);
    }
    
    /**
     * {@inheritDoc}
     */
    public function tearDown()
    {
        $this->bcrypt = null;
        $this->encoder = null;
    }
    
    /**
     * Tests that password valid works as expected
     */
    public function testPasswordValid()
    {
        $this->assertTrue($this->encoder->isPasswordValid(
			$this->bcrypt->hash("test"), 
			"test"
        ));
        
        
        $this->assertFalse($this->encoder->isPasswordValid(
			$this->bcrypt->hash("test"), 
			"not-test"
        ));
    }
    
    /**
     * Tests that password encode works as expected
     */
    public function testEncodePassword()
    {
        $this->assertTrue($this->bcrypt->checkHash(
        	$this->encoder->encodePassword("test"),        
            "test"
		));
        
        $this->assertFalse($this->bcrypt->checkHash(
            $this->encoder->encodePassword("test"),
            "not-test"
        ));
    }
}
