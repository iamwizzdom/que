<?php
/**
 * Created by PhpStorm.
 * User: Wisdom Emenike
 * Date: 27/7/2019
 * Time: 12:21 AM
 */

namespace que\security\jwt;

use \Exception;
use que\security\jwt\Exceptions\SigningFailedException;
use que\security\jwt\Exceptions\IntegrityViolationException;
use que\security\jwt\Exceptions\UnsupportedAlgorithmException;
use que\security\JWTGenerator;
use que\user\User;

/**
 * This class contains basic set of methods for handling JSON Web Tokens (JWT).
 */
class JWT
{

    /**
     * List of available algorithm keys.
     */
    const ALGORITHM_HS256 = 'HS256';
    const ALGORITHM_HS384 = 'HS384';
    const ALGORITHM_HS512 = 'HS512';
    const ALGORITHM_RS256 = 'RS256';
    const ALGORITHM_RS384 = 'RS384';
    const ALGORITHM_RS512 = 'RS512';
    
    /**
     * Default algorithm key that will be used when encoding token in case no algorithm was provided in token's header nor as parameter to encode method.
     */
    const DEFAULT_ALGORITHM = self::ALGORITHM_HS256;
    
    /**
     * Mapping of available algorithm keys with their types and target algorithms.
     */
    const ALGORITHMS = [
        self::ALGORITHM_HS256 => ['hash_hmac', 'SHA256'],
        self::ALGORITHM_HS384 => ['hash_hmac', 'SHA384'],
        self::ALGORITHM_HS512 => ['hash_hmac', 'SHA512'],
        self::ALGORITHM_RS256 => ['openssl', 'SHA256'],
        self::ALGORITHM_RS384 => ['openssl', 'SHA384'],
        self::ALGORITHM_RS512 => ['openssl', 'SHA512'],
    ];

    /**
     * Decodes encoded token.
     * 
     * @param TokenEncoded  $tokenEncoded   Encoded token
     * 
     * @return TokenDecoded
     */
    public static function decode(TokenEncoded $tokenEncoded): TokenDecoded
    {
        return new TokenDecoded(json_decode(Base64Url::decode($tokenEncoded->getHeader()), true),
            json_decode(Base64Url::decode($tokenEncoded->getPayload()), true));
    }

    /**
     * Encodes decoded token.
     *
     * @param TokenDecoded $tokenDecoded Decoded token
     * @param string $secret Secret Key used to sign the token
     * @param string|null $algorithm Force algorithm even if defined in token's header
     * @param int|null $leeway
     * @return TokenEncoded Encoded token
     * @throws Exceptions\EmptyTokenException
     * @throws Exceptions\InsecureTokenException
     * @throws Exceptions\InvalidClaimTypeException
     * @throws Exceptions\InvalidStructureException
     * @throws Exceptions\MissingClaimException
     * @throws Exceptions\TokenExpiredException
     * @throws Exceptions\TokenInactiveException
     * @throws Exceptions\UndefinedAlgorithmException
     * @throws Exceptions\UnsupportedTokenTypeException
     * @throws IntegrityViolationException
     * @throws SigningFailedException
     * @throws UnsupportedAlgorithmException
     */
    public static function encode(TokenDecoded $tokenDecoded, string $secret, ?string $algorithm = null, ?int $leeway = null): TokenEncoded
    {
        $header = $tokenDecoded->getHeader();
        $header = array_merge($header, [
            'typ' => array_key_exists('typ', $header) ? $header['typ'] : 'JWT',
            'alg' => $algorithm ?: (array_key_exists('alg', $header) ? $header['alg'] : self::DEFAULT_ALGORITHM),
            'imt' => array_key_exists('imt', $header) ? $header['imt'] : false
        ]);

        $elements = [];
        $elements[] = Base64Url::encode(json_encode($header));
        $elements[] = Base64Url::encode(json_encode($tokenDecoded->getPayload()));

        $signature = self::sign(implode('.', $elements), $secret, $header['alg']);
        $elements[] = Base64Url::encode($signature);

        return new TokenEncoded(implode('.', $elements), $secret, $algorithm, $leeway, config('auth.jwt.required_claims', []));
    }


    /**
     *
     * Generates signature for given message.
     *
     * @param string $message Message to sign, which is base64 encoded values of header and payload separated by dot
     * @param string $secret Secret Key used to sign the token
     * @param string $algorithm Algorithm to use for signing the token
     * @return string
     * @throws SigningFailedException
     * @throws UnsupportedAlgorithmException
     * @throws Exceptions\InsecureTokenException
     */
    protected static function sign(string $message, string $secret, string $algorithm): string
    {
        list($function, $type) = self::getAlgorithmData($algorithm);

        switch ($function) {
            case 'hash_hmac':
                try {
                    $signature = hash_hmac($type, $message, $secret, true);
                } catch (Exception $e) {
                    throw new SigningFailedException(sprintf('Signing failed: %s', $e->getMessage()));
                }
                if ($signature === false) {
                    throw new SigningFailedException('Signing failed');
                }
                return $signature;
                break;
            case 'openssl':
                $signature = '';
                
                try {
                    $sign = openssl_sign($message, $signature, $secret, $type);
                } catch (Exception $e) {
                    throw new SigningFailedException(sprintf('Signing failed: %s', $e->getMessage()));
                }
                
                if (! $sign) {
                    throw new SigningFailedException('Signing failed');
                }
                
                return $signature;
                break;
            default:
                throw new UnsupportedAlgorithmException('Invalid function');
                break;
        }
    }

    /**
     *
     * Validates token's using provided key.
     *
     * This method should be used to check if given token is valid.
     *
     * Following things should be verified:
     * - if token contains algorithm defined in its header
     * - if token integrity is met using provided key
     * - if token contains expiration date (exp) in its payload - current time against this date
     * - if token contains not before date (nbf) in its payload - current time against this date
     * - if token contains issued at date (iat) in its payload - current time against this date
     *
     * @param TokenEncoded $tokenEncoded Encoded token
     * @param string $secret Key used to signature verification
     * @param string|null $algorithm Force algorithm to signature verification (recommended)
     * @param int|null $leeway Some optional period to avoid clock synchronization issues
     * @param array|null $requiredClaims Claims to be required for validation
     * @throws Exceptions\InsecureTokenException
     * @throws Exceptions\MissingClaimException
     * @throws Exceptions\TokenExpiredException
     * @throws Exceptions\TokenInactiveException
     * @throws IntegrityViolationException
     * @throws UnsupportedAlgorithmException
     */
    public static function validate(TokenEncoded $tokenEncoded, string $secret, ?string $algorithm,
                                    ?int $leeway = null, ?array $requiredClaims = null): void
    {
        $tokenDecoded = self::decode($tokenEncoded);

        $signature = Base64Url::decode($tokenEncoded->getSignature());
        $header = $tokenDecoded->getHeader();
        $payload = $tokenDecoded->getPayload();

        list($function, $type) = self::getAlgorithmData($algorithm ?? $header['alg']);

        switch ($function) {
            case 'hash_hmac':
                if (hash_equals($signature, hash_hmac($type, $tokenEncoded->getMessage(), $secret, true)) !== true) {
                    throw new IntegrityViolationException('Invalid signature');
                }
                break;
            case 'openssl':
                if (openssl_verify($tokenEncoded->getMessage(), $signature, $secret, $type) !== 1) {
                    throw new IntegrityViolationException('Invalid signature');
                }
                break;
            default:
                throw new UnsupportedAlgorithmException('Unsupported algorithm type');
                break;
        }

        if ($requiredClaims === null) $requiredClaims = config('auth.jwt.required_claims', []);

        //check if is immortal token, if yes, escape 'exp' claim
        if ($requiredClaims && $header['imt']) unset($requiredClaims[array_search('exp', $requiredClaims)]);

        Validation::checkRequiredClaims($requiredClaims, $payload);

        if ($leeway === null) $leeway = config('auth.jwt.leeway');

        if (array_key_exists('iat', $payload)) {
            Validation::checkIssuedAtDate($payload['iat'], $leeway);
        }
           
        if (array_key_exists('exp', $payload)) {
            Validation::checkExpirationDate($payload['exp'], $leeway);
        }
        
        if (array_key_exists('nbf', $payload)) {
            Validation::checkNotBeforeDate($payload['nbf'], $leeway);
        }
    }

    /**
     *
     * Transforms algorithm key into array containing its type and target algorithm.
     *
     * @param string $algorithm Algorithm key
     * @return array
     * @throws Exceptions\InsecureTokenException
     * @throws UnsupportedAlgorithmException
     */
    public static function getAlgorithmData(string $algorithm): array
    {
        Validation::checkAlgorithmSupported($algorithm);

        return self::ALGORITHMS[$algorithm];
    }

    /**
     * @param User $user
     * @param int|null $expire
     * @return string|null
     */
    public static function fromUser(User $user, int $expire = null, bool $throwException = false)
    {
        $generator = new JWTGenerator();
        $generator->setAlgorithm(JWT::ALGORITHM_HS512);
        $generator->setID($user->getValue(config('database.tables.user.primary_key', 'id')));
        $generator->setSubject(config('template.app.header.name') . " User JWT");
        if ($expire) $generator->setExpiration($expire);
        try {
            return $generator->generate();
        } catch (Exception $e) {
            if ($throwException) throw $e;
        }
        return null;
    }


    /**
     * @param string $token
     * @param bool $throwException
     * @return User|null
     */
    public static function toUser(string $token, bool $throwException = false)
    {
        try {

            $tokenEncoded = new TokenEncoded($token, (string) config('auth.jwt.secret', ''),
                JWT::ALGORITHM_HS512, null, null);
            $tokenDecoded = $tokenEncoded->decode();
            $payload = $tokenDecoded->getPayload();
            $config = config('database.tables.user', []);
            $user = db()->find($config['name'] ?? 'users', $payload['jti'] ?? 0,
                $config['primary_key'] ?? 'id');

            if (!$user->isSuccessful()) {
                if (!$throwException) return null;
                throw new Exception("Login failed, no record found with the given token.");
            }

            User::login($user->getFirst());
            return User::getInstance();

        } catch (Exception $e) {
            if ($throwException) throw $e;
        }
        return null;
    }

}
