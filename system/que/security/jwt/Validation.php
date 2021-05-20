<?php
/**
 * Created by PhpStorm.
 * User: Wisdom Emenike
 * Date: 27/7/2019
 * Time: 12:21 AM
 */

namespace que\security\jwt;

use \DateTime;
use que\security\jwt\Exceptions\InsecureTokenException;
use que\security\jwt\Exceptions\MissingClaimException;
use que\security\jwt\Exceptions\UnsupportedAlgorithmException;
use que\security\jwt\Exceptions\TokenExpiredException;
use que\security\jwt\Exceptions\TokenInactiveException;
use que\security\jwt\Exceptions\InvalidClaimTypeException;
use que\security\jwt\Exceptions\UndefinedAlgorithmException;
use que\security\jwt\Exceptions\InvalidStructureException;
use que\security\jwt\Exceptions\UnsupportedTokenTypeException;

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
        $time = time() - ($leeway ?: 0);

        if ($time >= $exp) {
            throw new TokenExpiredException('Token has expired since: ' . date(DATE_FORMAT_REPORT_TIME, $exp));
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
        $time = time() + ($leeway ?: 0);
        if ($time < $nbf) {
            throw new TokenInactiveException('Token is not valid before: ' . date(DATE_FORMAT_REPORT_TIME, $nbf));
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
        $time = time() + ($leeway ?: 0);
        if ($time < $iat) {
            throw new TokenInactiveException("Token is not valid before it's issue time: " . date(DATE_FORMAT_REPORT_TIME, $iat));
        }
    }

    /**
     * @param string $token
     * @return array Token segments
     * @throws InvalidStructureException
     */
    public static function checkTokenStructure(string $token): array
    {
        $elements = explode('.', $token);

        if (count($elements) !== 3) {
            throw new InvalidStructureException('Wrong number of token segments');
        }

        list($header, $payload, $signature) = $elements;

        if (null === ($h = json_decode(Base64Url::decode($header), true))) {
            throw new InvalidStructureException('Invalid token header');
        }

        $elements[] = $h;

        if (null === ($p = json_decode(Base64Url::decode($payload), true))) {
            throw new InvalidStructureException('Invalid token payload');
        }

        $elements[] = $p;

        if (false === Base64Url::decode($signature)) {
            throw new InvalidStructureException('Invalid token signature');
        }

        return $elements;
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
            throw new UnsupportedAlgorithmException('Invalid token algorithm');
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
     * @param string $type
     * @param array $payload Payload array
     *
     * @throws InvalidClaimTypeException
     */
    public static function checkClaimType(string $claim, string $type, array $payload): void
    {
        switch ($type) {
            case 'integer':
                if (array_key_exists($claim, $payload) && !is_int($payload[$claim])) {
                    throw new InvalidClaimTypeException(sprintf('Invalid token %s claim - %s value required, %s given', $claim, $type, gettype($payload[$claim])));
                }
                break;
            case 'mixed':
                if (array_key_exists($claim, $payload) && !(is_string($payload[$claim]) || is_int($payload[$claim]))) {
                    throw new InvalidClaimTypeException(sprintf('Invalid token %s claim - integer or string value required, %s given', $claim, gettype($payload[$claim])));
                }
                break;
            case 'string':
            default:
                if (array_key_exists($claim, $payload) && !is_string($payload[$claim])) {
                    throw new InvalidClaimTypeException(sprintf('Invalid token %s claim - %s value required, %s given', $claim, $type, gettype($payload[$claim])));
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
                throw new MissingClaimException(sprintf('Missing token %s claim - required', $claim));
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
