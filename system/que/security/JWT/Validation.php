<?php
/**
 * Created by PhpStorm.
 * User: Wisdom Emenike
 * Date: 27/7/2019
 * Time: 12:21 AM
 */

namespace que\security\JWT;

use \DateTime;
use que\security\JWT\Exceptions\InsecureTokenException;
use que\security\JWT\Exceptions\MissingClaimException;
use que\security\JWT\Exceptions\UnsupportedAlgorithmException;
use que\security\JWT\Exceptions\TokenExpiredException;
use que\security\JWT\Exceptions\TokenInactiveException;
use que\security\JWT\Exceptions\InvalidClaimTypeException;
use que\security\JWT\Exceptions\UndefinedAlgorithmException;
use que\security\JWT\Exceptions\InvalidStructureException;
use que\security\JWT\Exceptions\UnsupportedTokenTypeException;

/**
 * This class contains methods used for validating JWT tokens.
 *
 */
class Validation
{

    /**
     * Checks if expiration date has been reached.
     *
     * @param int $exp Timestamp of expiration date
     * @param int|null $leeway Some optional period to avoid clock synchronization issues
     *
     * @throws TokenExpiredException
     */
    public static function checkExpirationDate(int $exp, ?int $leeway = null): void
    {
        $time = time() - ($leeway ? $leeway : 0);

        if ($time >= $exp) {
            throw new TokenExpiredException('Token has expired since: ' . date(DateTime::ISO8601, $exp));
        }
    }

    /**
     * Checks if not before date has been reached.
     *
     * @param int $nbf Timestamp of activation (not before) date
     * @param int|null $leeway Some optional period to avoid clock synchronization issues
     *
     * @throws TokenInactiveException
     */
    public static function checkNotBeforeDate(int $nbf, ?int $leeway = null): void
    {
        $time = time() + ($leeway ? $leeway : 0);
        if ($time < $nbf) {
            throw new TokenInactiveException('Token is not valid before: ' . date(DateTime::ISO8601, $nbf));
        }
    }

    /**
     * Checks if issued at date has been reached.
     *
     * @param int $iat Timestamp of issue (issued at) date
     * @param int|null $leeway Some optional period to avoid clock synchronization issues
     *
     * @throws TokenInactiveException
     */
    public static function checkIssuedAtDate(int $iat, ?int $leeway = null): void
    {
        $time = time() + ($leeway ? $leeway : 0);
        if ($time < $iat) {
            throw new TokenInactiveException("Token is not valid before it's issue time: " . date(DateTime::ISO8601, $iat));
        }
    }

    /**
     * Checks token structure.
     *
     * @param string $token Token
     *
     * @throws InvalidStructureException
     */
    public static function checkTokenStructure(string $token): void
    {
        $elements = explode('.', $token);

        if (count($elements) !== 3) {
            throw new InvalidStructureException('Wrong number of segments');
        }

        list($header, $payload, $signature) = $elements;

        if (null === json_decode(Base64Url::decode($header))) {
            throw new InvalidStructureException('Invalid header');
        }
        if (null === json_decode(Base64Url::decode($payload))) {
            throw new InvalidStructureException('Invalid payload');
        }
        if (false === Base64Url::decode($signature)) {
            throw new InvalidStructureException('Invalid signature');
        }
    }

    /**
     * @param array $header
     * @throws UndefinedAlgorithmException
     */
    public static function checkAlgorithmDefined(array $header)
    {
        if (!array_key_exists('alg', $header)) {
            throw new UndefinedAlgorithmException('Missing algorithm in token header');
        }
    }

    /**
     * Checks if algorithm has been provided and is supported.
     *
     * @param string $algorithm
     *
     * @throws InsecureTokenException
     * @throws UnsupportedAlgorithmException
     */
    public static function checkAlgorithmSupported(string $algorithm)
    {
        if (strtolower($algorithm) === 'none') {
            throw new InsecureTokenException('Insecure token are not supported: none algorithm provided');
        }

        if (!array_key_exists($algorithm, JWT::ALGORITHMS)) {
            throw new UnsupportedAlgorithmException('Invalid algorithm');
        }
    }

    /**
     * @param string $signature
     * @throws InsecureTokenException
     */
    public static function checkSignatureMissing(string $signature): void
    {
        if (strlen($signature) === 0) {
            throw new InsecureTokenException('Insecure token are not supported: signature is missing');
        }
    }

    /**
     * Checks if given key exists in the payload and if so, checks if it's of integer type.
     *
     * @param string $claim Claim name
     * @param array $payload Payload array
     *
     * @throws InvalidClaimTypeException
     */
    public static function checkClaimType(string $claim, string $type, array $payload): void
    {
        switch ($type) {
            case 'integer':
                if (array_key_exists($claim, $payload) && !is_int($payload[$claim])) {
                    throw new InvalidClaimTypeException(sprintf('Invalid %s claim - %s value required', $claim, $type));
                }
                break;
            case 'string':
            default:
                if (array_key_exists($claim, $payload) && !is_string($payload[$claim])) {
                    throw new InvalidClaimTypeException(sprintf('Invalid %s claim - %s value required', $claim, $type));
                }
                break;
        }
    }

    /**
     * Check if all required claims are present in payload
     *
     * @param array $claims
     * @param array $payload
     * @throws MissingClaimException
     */
    public static function checkRequiredClaims(array $claims, array $payload) {
        foreach ($claims as $claim) {
            if (!array_key_exists($claim, $payload)) {
                throw new MissingClaimException(sprintf('Missing %s claim - required', $claim));
            }
        }
    }

    /**
     * Checks if token is of JWT type.
     *
     * @param array $header Header array
     *
     * @throws UnsupportedTokenTypeException
     */
    public static function checkTokenType(array $header): void
    {
        if (!array_key_exists('typ', $header) || $header['typ'] !== 'JWT') {
            throw new UnsupportedTokenTypeException('Unsupported token type');
        }
    }

}
