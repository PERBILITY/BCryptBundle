<?php

/*
 * This file is part of the PerbilityBCryptBundle package.
*
* (c) PERBILITY GmbH <http://www.perbility.de>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/


namespace Perbility\Bundle\BCryptBundle\Tests\BCrypt;

use Perbility\Bundle\BCryptBundle\BCrypt\BCrypt;

/**
 * Test BCrypt class
 * 
 * @author Benjamin Zikarsky <benjamin.zikarsky@perbility.de>
 */
class BCryptTest extends \PHPUnit_Framework_TestCase
{   
    /**
     * @var Perbility\Bundle\BCryptBundle\BCrypt\BCrypt
     */
    protected $bcrypt = null;
    
    /**
     * {@inheritDoc}
     */
    public function setUp()
    {
        $this->bcrypt = new BCrypt("global_salt");
    }
    
    /**
     * {@inheritDoc}
     */
    public function tearDown()
    {
        $this->bcrypt = null;
    }
    
    /**
     * Tests that all valid values for cost-factor can be set and read accordingly
     */
	public function testValidCostFactor()
	{
	    $this->assertEquals(BCrypt::DEFAULT_COST_FACTOR, $this->bcrypt->getCostFactor());
	    
	    $this->bcrypt->setCostFactor(BCrypt::MIN_COST_FACTOR);
	    $this->assertEquals(BCrypt::MIN_COST_FACTOR, $this->bcrypt->getCostFactor());
	    
	    $value = intval((BCrypt::MIN_COST_FACTOR + BCrypt::MAX_COST_FACTOR) / 2);
	    $this->bcrypt->setCostFactor($value);
	    $this->assertEquals($value, $this->bcrypt->getCostFactor());
	    
	    $this->bcrypt->setCostFactor(BCrypt::MAX_COST_FACTOR);
	    $this->assertEquals(BCrypt::MAX_COST_FACTOR, $this->bcrypt->getCostFactor());  
	}
	
	/**
	 * Tests that an exception is thrown in case cost-factor is lower than MIN_COST_FACTOR
	 * 
	 * @expectedException \InvalidArgumentException
	 */
	public function testInvalidCostFactorMin()
	{
	    $this->bcrypt->setCostFactor(BCrypt::MIN_COST_FACTOR - 1);
	}
	
	/**
	 * Tests that an exception is thrown in case cost-factor is greater than MAX_COST_FACTOR
	 * 
	 * @expectedException \InvalidArgumentException
	 */
	public function testInvalidCostFactorMax()
	{
	    $this->bcrypt->setCostFactor(BCrypt::MAX_COST_FACTOR + 1);
	}
	
	/**
	 * Tests that an exception is thrown in case cost-factor is lower than MIN_COST_FACTOR 
	 * when specified on hash()-call
	 *
	 * @expectedException \InvalidArgumentException
	 */
	public function testInvalidIterationsMinSpecOnRuntime()
	{
	    $this->bcrypt->hash("test", null, BCrypt::MIN_COST_FACTOR - 1);
	}
	
	/**
	 * Tests that an exception is thrown in case cost-factor is greater than MAX_COST_FACTOR 
	 * when specified on hash-call
	 *
	 * @expectedException \InvalidArgumentException
	 */
	public function testInvalidCostFactorMaxSpecOnRuntime()
	{
	    $this->bcrypt->hash("test", null, BCrypt::MAX_COST_FACTOR + 1);
	}
	
	/**
	 * Tests that hashes are equal with the same global salt and different with different global salts
	 */
	public function testHashesGlobalSaltDependence()
	{
	    $this->bcrypt->setCostFactor(BCrypt::MIN_COST_FACTOR);
	    $hash = $this->bcrypt->hash("test", "userdata");
	    
	    $bcryptA = new BCrypt($this->bcrypt->getGlobalSalt(), $this->bcrypt->getCostFactor());
	    $bcryptB = new BCrypt(md5($this->bcrypt->getGlobalSalt()), $this->bcrypt->getCostFactor());
	    
	    $this->assertTrue($bcryptA->checkHash($hash, "test", "userdata"));
	    $this->assertFalse($bcryptB->checkHash($hash, "test", "userdata"));
	    
	    $this->assertTrue($this->bcrypt->checkHash($hash, "test", "userdata", $this->bcrypt->getGlobalSalt()));
	    $this->assertFalse($this->bcrypt->checkHash($hash, "test", "userdata", md5($this->bcrypt->getGlobalSalt())));
	}
	
	/**
	 * Tests that hashes change with different user-data
	 */
	public function testHashesUserDataDependence()
	{
	    $this->bcrypt->setCostFactor(BCrypt::MIN_COST_FACTOR);
		$hash = $this->bcrypt->hash("test", "userdata");
	   	
		$this->assertTrue($this->bcrypt->checkHash($hash, "test", "userdata"));
		$this->assertFalse($this->bcrypt->checkHash($hash, "test", "different-userdata"));
	}
	
	/**
	 * Tests common hash() uses
	 * 
	 * @dataProvider validHashUses
	 */
	public function testHash($password, $userdata, $costFactor, $globalSalt)
	{
	    $this->bcrypt->setCostFactor(BCrypt::MIN_COST_FACTOR);
	    $params = self::provideParameterArray($password, $userdata, $costFactor, $globalSalt);
	    
	    $hash = call_user_func_array(array($this->bcrypt, "hash"), $params);
	    
	    $this->assertTrue(is_string($hash));
	    $this->assertEquals(BCrypt::HASH_LENGTH, strlen($hash));
	    $this->assertEquals(1, preg_match(BCrypt::DEFINITION_REGEX, substr($hash, 0, BCrypt::DEFINITION_LENGTH), $matches));
	    $this->assertEquals(BCrypt::getAlgorithmId(), $matches[1]);
	    $this->assertEquals($costFactor ?: $this->bcrypt->getCostFactor(), intval($matches[2]));
	}
	
	/**
	 * Tests common checkHash() uses
	 *
	 * @dataProvider validHashUses
	 */
	public function testCheckHash($password, $userdata, $c /*ignored */, $globalSalt, $hash)
	{
	    $this->bcrypt->setCostFactor(BCrypt::MIN_COST_FACTOR);
	    $params = self::provideParameterArray($hash, $password, $userdata, $globalSalt);
	    $this->assertTrue(call_user_func_array(array($this->bcrypt, 'checkHash'), $params));
	}
	
	/**
	 * Test checkHash behaviour
	 * 
	 * @dataProvider invalidHashes
	 * @expectedException \InvalidArgumentException
	 */
	public function testCheckHashInvalidHash($invalidHash)
	{
	    $this->bcrypt->checkHash($invalidHash, 'test');
	}
	
	/**
	 * Tests that an invalid salt leads to an exception
	 * 
	 * @expectedException \InvalidArgumentException
	 */
	public function testInvalidConstructorArgument()
	{
	    new BCrypt(array());
	}
	
	/**
	 * Returns all given arguments as an array with all the trailing null values
	 * dropped
	 * 
	 * @return array
	 */
	private static function provideParameterArray()
	{
	    $params = array_reverse(func_get_args());
	    $result = array();
	    
	    foreach ($params as $param) {
	        if (!count($result) && null === $param) {
	            continue;
	        }
	        
	        $result[] = $param;
	    }
	    
	    return array_reverse($result);
	}
	
	/**
	 * Provides a collcection of valid hash()-calls
	 */
    public static function validHashUses()
    {
        return array(
			array("", null, null, null, '$2a$04$bdf828ba40bfd2ee7ccb2uGV9iTpRbughINtXjRdlOpyYG8XcNi5O'),
            array("test", "userdata", null, null, '$2a$04$ad4b7a3fb025e4ad48670eS8gTCFOaF1RsgQWVKVHi6LIh.1Nt35C'),
            array("test", array("user", "data"), null, null, '$2a$04$80b0665db3ab21554a5a2u271EqKEa7KoxvM53Gz6fJZrLUGKnUhm'),
            array("test", (object) array("user" => "data"), null, null, '$2a$04$86d917f4c0333402346e1uNXjnRiR/oVrWFHptQW1dmAjeCWMHyLG'),
            array("test", "userdata", BCrypt::MIN_COST_FACTOR, null, '$2a$04$933f563f0d6140f48c53aOfWX5BLAofb63zZYJdToE.my.9zCEq.2'),
            array("test", null, BCrypt::DEFAULT_COST_FACTOR, '', '$2a$12$fc1b34e8ef12949b8359de4jTlvkuqDIkpoI9agqlULs3t5MuAs1a'),
            array("test", null, null, "global-salt", '$2a$04$47f16f9a17cd49e17db30eESOUJT0rEXxUXLGAEAVAfZ1MsizW8Ja'),
            array("test", "userdata", BCrypt::DEFAULT_COST_FACTOR, "global-salt", '$2a$12$f857b9eb207ff3f5deaffuqGKVAbIQ6yN.VllJYCIDISd1u/pNhey'),
            array("very-long-user-data-1234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890"
                  . "123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890"
                  . "123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890"
                  . "123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890"
                  . "123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890"
                  . "123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890"
                  . "123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890", 
                    null, null, null, '$2a$04$9cb0636bf6f764ba61c2bel3VdhaOl3Nt2rPnIbt113eP0f6Iqygi')
		);
    }
    
    /**
     * Provides invalid hashes
     */
    public static function invalidHashes()
    {
        return array(
        	array(""),
            array(new \stdclass),
            array(array()),      
            array("*0"),
            array("abcdefgh")
		);
    }
	
}