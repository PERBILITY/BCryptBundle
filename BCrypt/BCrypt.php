<?php

/*
 * This file is part of the PerbilityBCryptBundle package.
 *
 * (c) PERBILITY GmbH <http://www.perbility.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Perbility\Bundle\BCryptBundle\BCrypt;

/**
 * This class provides bcrypt-hashing functionality
 *
 * @author Benjamin Zikarsky <benjamin.zikarsky@perbility.de>
 */
class BCrypt
{
    /**
     * aAgorithm-identifier for PHP's crypt-function for PHP versions after 5.3.6
     */
    const ALGO_ID = "2y";

    /**
     * Algorithm-identifier for PHP's crypt-function for PHP versions before 5.3.7
     */
    const ALGO_ID_PRE_5_3_7 = "2a";

    /**
     * Maximum bcrypt cost-factor
     */
    const MAX_COST_FACTOR = 31;

    /**
     * Minimum bcrypt cost-factor
     */
    const MIN_COST_FACTOR = 4;

    /**
     * Default bcrypt cost-factor
     */
    const DEFAULT_COST_FACTOR = 12;

    /**
     * Number of bytes for the becrypt-salt
     */
    const SALT_LENGTH = 22;

    /**
     * Length of the crypt()-definition-strength (Format $__$__$)
     */
    const DEFINITION_LENGTH = 7; 
    
    /**
     * Length of a full hash
     */
    const HASH_LENGTH = 60;
    
    /**
     * Regular expression which matches the hash's definition part
     */
    const DEFINITION_REGEX = '/\\$([a-z0-9]{2})\\$(\\d{2})\\$/';

    /**
     * bcrypt cost factor
     *
     * @var int
     */
    private $costFactor;

    /**
     * Global salt to be used in every hash
     *
     * @var string
     */
    private $globalSalt;

    /**
     * Class construtor - sets config options
     *
     * @param string $globalSalt
     * @param int    $costFactor
     */
    public function __construct($globalSalt='', $costFactor=self::DEFAULT_COST_FACTOR)
    {
        if (!is_scalar($globalSalt)) {
            throw new \InvalidArgumentException("Global salt has to be a string");
        }
        
        $this->globalSalt = (string) $globalSalt;
        $this->setCostFactor($costFactor);
    }

    /**
     * Returns the correct crypt()-algorithm-id for the current PHP version
     *
     * @return string
     */
     public static final function getAlgorithmId($version=PHP_VERSION)
     {
         return version_compare($version, "5.3.7", ">=")
             ? self::ALGO_ID
             : self::ALGO_ID_PRE_5_3_7;
     }
     
     /**
     * Checks for possible constraints of the given algorithm
     *
     * @param string $algoId
     * @param string $password
     * @throws \RuntimeException if using 8-bit password with PHP version <= 5.3.7
     */
     public static function checkAlgorithmConstraints($algoId, $password)
     {
         if (self::ALGO_ID PRE_5_3_7 === $algoId && preg_match('/[\x80-\xFF]/', $password)) {
                throw new \RuntimeException(
                    'The bcrypt implementation used by PHP can contains a security flaw ' .
                    'using password with 8-bit character. ' .
                    'We suggest to upgrade to PHP 5.3.7+ or use passwords with only 7-bit characters'
                );
        }
     }

    /**
     * Gets the bcrypt cost-factor
     *
     * @return int
     */
    public function getCostFactor()
    {
        return $this->costFactor;
    }

    /**
     * Sets the bcrypt cost-factor
     *
     * @param int $costFactor
     */
    public function setCostFactor($costFactor)
    {
        $costFactor = intval($costFactor);
        $this->validateCostFactor($costFactor);

        $this->costFactor = $costFactor;
    }

    /**
     * Gets the current global salt
     *
     * @return string
     */
    public function getGlobalSalt()
    {
        return $this->globalSalt;
    }

    /**
     * Hashes a given string/password under consideration of $userData
     *
     * Allows overriding class configuration like cost-factor and globalSalt
     *
     * @param string $password
     * @param mixed  $userData defaults to an empty string
     * @param int    $costFactor defaults to BCrypt::getCostFactor()
     * @param string $globalSalt defaults to BCrypt::getGlobalSalt()
     */
    public function hash($password, $userData='', $costFactor=null, $globalSalt=null)
    {
        $algoId = self::getAlgorithmId();
        self::checkAlgorithmConstraints($algoId, $password);
        
        if (is_null($costFactor)) {
            $costFactor = $this->costFactor;
        } else {
            // check parameter constraints
            $this->validateCostFactor($costFactor);
        }

        if (is_null($globalSalt)) {
            $globalSalt = $this->globalSalt;
        }

        $string = $this->prepareHashString($password, $userData, $globalSalt);
        $salt = self::makeSalt(self::SALT_LENGTH);

        $saltDefinition = sprintf('$%s$%02d$%s',
           $algoId, $costFactor, $salt
        );

        return crypt($string, $saltDefinition);
    }

    /**
     * Checks a hash against a password/String
     *
     * @param string $hash
     * @param string $password
     * @param mixed  $userData defaults to an empty string
     * @param string $globalSalt defaults to BCrypt::getGlobalSalt()
     */
    public function checkHash($hash, $password, $userData='', $globalSalt=null)
    {
        if (is_null($globalSalt)) {
            $globalSalt = $this->globalSalt;
        }
        
        if (!is_scalar($hash)) {
            throw new \InvalidArgumentException('$hash is expected to be a string');
        }
        
        $hash = (string) $hash;
        $definition = substr($hash, 0, self::DEFINITION_LENGTH);
        
        if (strlen($hash) != BCrypt::HASH_LENGTH || !preg_match(self::DEFINITION_REGEX, $definition)) {
            throw new \InvalidArgumentException('$hash is not a valid bcrypt-hash');
        }

        $checkHash = crypt(
            $this->prepareHashString($password, $userData, $globalSalt),
            substr($hash, 0, self::SALT_LENGTH + self::DEFINITION_LENGTH)
        );

        return $hash == $checkHash;
    }

    /**
     * Returns an hashable string for crypt()
     * which considers optional $userData and the global salt
     *
     * @param string $password
     * @param mixed  $userData
     * @param string $globalSalt
     *
     * @return string
     */
    protected function prepareHashString($password, $userData, $globalSalt)
    {
        if (!is_string($userData)) {
            $userData = serialize($userData);
        }

        // hash password+userdata with global salt
        return hash_hmac(
            "whirlpool",
            // pad password with scrambled (hashed) parts of userdata
            str_pad($password, strlen($password) * 4, sha1($userData), STR_PAD_BOTH),
            $globalSalt,
            false
        );
    }

    /**
     * Creates and returns random string (salt) with given length
     *
     * @param int $length defaults to BCrypt::SALT_LENGTH
     *
     * @retun string
     */
    public static function makeSalt($length=self::SALT_LENGTH)
    {
        $rand = '';
        do {
            $rand .= sha1(uniqid());
        } while (strlen($rand) < $length);

        return substr($rand, 0, $length);
    }
    
    /**
     * Returns whether a costfactor is valid
     * 
     * @param int $costFactor
     * @return boolean
     */
    public static function isValidCostFactor($costFactor)
    {
        return $costFactor >= self::MIN_COST_FACTOR && $costFactor <= self::MAX_COST_FACTOR;
    }
    
    /**
     * Validates an integer against the cost-factor constraints, and throws an exception
     * on violations
     *
     * @param int $costFactor
     */
    public static function validateCostFactor($costFactor)
    {
        if (!self::isValidCostFactor($costFactor)) {
            throw new \InvalidArgumentException(sprintf(
                    "\$costFactor must be an int greater than %d and lower than %d",
                    self::MIN_COST_FACTOR-1,
                    self::MAX_COST_FACTOR+1
            ));
        }
    }
}
