<?php

/*
 * This file is part of the PerbilityBCryptBundle package. (c) PERBILITY GmbH
 * <http://www.perbility.de> For the full copyright and license information,
 * please view the LICENSE file that was distributed with this source code.
 */
namespace Perbility\Bundle\BCryptBundle\Security\Encoder;

use Perbility\Bundle\BCryptBundle\BCrypt\BCrypt;
use Symfony\Component\Security\Core\Encoder\BasePasswordEncoder;

/**
 * A password-encoder for the use in the symfony2-security-framework
 * CAVE-AT: The given salt is ignored
 * 
 * @author "Benjamin Zikarsky <benjamin.zikarsky@perbility.de>"
 */
class BCryptEncoder extends BasePasswordEncoder
{
	/**
	 * @var Perbility\Bundle\BCryptBundle\BCrypt\BCrypt
	 */
    protected $bcrypt = null;
    
    public function __construct(BCrypt $bcrypt)
    {
        $this->bcrypt = $bcrypt;
    }
    
    /**
     * @see \Symfony\Component\Security\Core\Encoder\PasswordEncoderInterface::encodePassword()
     * 
     * But salt is ignored and not required
     */
    public function encodePassword($raw, $salt)
    {
        return $this->bcrypt->hash($raw);
    }
    
    /**
     * @see \Symfony\Component\Security\Core\Encoder\PasswordEncoderInterface::isPasswordValid()
     * 
     * But salt is ignored and not required
     */
    public function isPasswordValid($encoded, $raw, $salt)
    {
        return $this->bcrypt->checkHash($encoded, $raw);
    }
}
