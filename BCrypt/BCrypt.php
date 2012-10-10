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
     * Maximum number of bcrypt-iterations
     */
    const MAX_ITERATIONS = 31;

    /**
     * Minimum number of bcrypt-iterations
     */
    const MIN_ITERATIONS = 4;

    /**
     * Default number of bcrypt-iterations
     */
    const DEFAULT_ITERATIONS = 12;

    /**
     * Number of bytes for the becrypt-salt
     */
    const SALT_LENGTH = 22;

    /**
     * Length of the crypt()-definition-strength (Format $__$__$)
     */
    const DEFINITION_LENGTH = 7; //

    /**
     * Number of bcrypt iterations
     *
     * @var int
     */
    private $iterations;

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
     * @param int    $iterations
     */
    public function __construct($globalSalt='', $iterations=self::DEFAULT_ITERATIONS)
    {
        $this->globalSalt = $globalSalt;
        $this->setIterations($iterations);
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
     * Gets the number of bcrypt-iterations
     *
     * @return int
     */
    public function getIterations()
    {
        return $this->iterations;
    }

    /**
     * Sets the number of bcrypt-iterations
     *
     * @param int $iterations
     */
    public function setIterations($iterations)
    {
        $iterations = intval($iterations);
        $this->validateIterations($iterations);

        $this->iterations = $iterations;
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
     * Allows overriding class configuration like iterations and globalSalt
     *
     * @param string $password
     * @param mixed  $userData defaults to an empty string
     * @param int    $iterations defaults to BCrypt::getIterations()
     * @param string $globalSalt defaults to BCrypt::getGlobalSalt()
     */
    public function hash($password, $userData='', $iterations=null, $globalSalt=null)
    {
        if (is_null($iterations)) {
            $iterations = $this->iterations;
        } else {
            // check parameter constraints
            $this->validateIterations($iterations);
        }

        if (is_null($globalSalt)) {
            $globalSalt = $this->globalSalt;
        }

        $string = $this->prepareHashString($password, $userData, $globalSalt);
        $salt = self::makeSalt(self::SALT_LENGTH);

        $saltDefinition = sprintf('$%s$%02d$%s',
           self::getAlgorithmId(), $iterations, $salt
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

        $checkHash = crypt(
            $this->prepareHashString($password, $userData, $globalSalt),
            substr($hash, 0, self::SALT_LENGTH + self::DEFINITION_LENGTH)
        );

        return $hash == $checkHash;
    }

    /**
     * Validates an integer against the iteration constraints, and throws an exception
     * on violations
     *
     * @param int $iterations
     */
    private function validateIterations($iterations)
    {
        if ($iterations < self::MIN_ITERATIONS || $iterations > self::MAX_ITERATIONS) {
            throw new \InvalidArgumentException(sprintf(
                "\$iterations must be an int greater than %d and lower than %d",
                self::MIN_ITERATIONS-1,
                self::MAX_ITERATIONS+1
            ));
        }
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
            true
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
}
