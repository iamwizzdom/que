<?php
/**
 * Created by PhpStorm.
 * User: Wisdom Emenike
 * Date: 27/7/2019
 * Time: 12:21 AM
 */

namespace que\security\JWT;

use que\security\JWT\Exceptions\EmptyTokenException;

/**
 * This class is representation of encoded JSON Web Token (JWT).
 *
 */
class TokenEncoded
{
    /**
     * String representation of encoded token.
     */
    protected $token;
    
    /**
     * Base64 url encoded representation of JSON encoded token's header.
     */
    protected $header;
    
    /**
     * Base64 url encoded representation of JSON encoded token's payload.
     */
    protected $payload;
    
    /**
     * Base64 url encoded representation of token's signature.
     */
    protected $signature;

    /**
     * TokenEncoded constructor.
     * @param string|null $token
     * @param array|null $requiredClaims
     * @throws EmptyTokenException
     * @throws Exceptions\InsecureTokenException
     * @throws Exceptions\InvalidClaimTypeException
     * @throws Exceptions\InvalidStructureException
     * @throws Exceptions\UndefinedAlgorithmException
     * @throws Exceptions\UnsupportedAlgorithmException
     * @throws Exceptions\UnsupportedTokenTypeException
     * @throws Exceptions\MissingClaimException
     */
    public function __construct(?string $token = null, ?array $requiredClaims = null)
    {
        if ($token === null || $token === '') {
            throw new EmptyTokenException('Token not provided');
        }

        Validation::checkTokenStructure($token);
        
        $elements = explode('.', $token);
        list($header, $payload, $signature) = $elements;
        
        $headerArray = json_decode(Base64Url::decode($header), true);
        $payloadArray = json_decode(Base64Url::decode($payload), true);
        
        Validation::checkTokenType($headerArray);
        Validation::checkAlgorithmDefined($headerArray);
        Validation::checkAlgorithmSupported($headerArray['alg']);
        Validation::checkSignatureMissing($signature);

        if ($requiredClaims === null) $requiredClaims = config('auth.jwt.required_claims', []);

        Validation::checkRequiredClaims($requiredClaims, $payloadArray);
        
        Validation::checkClaimType('nbf', 'integer', $payloadArray);
        Validation::checkClaimType('exp', 'integer', $payloadArray);
        Validation::checkClaimType('iat', 'integer', $payloadArray);
        
        Validation::checkClaimType('iss', 'string', $payloadArray);
        Validation::checkClaimType('sub', 'string', $payloadArray);
        Validation::checkClaimType('aud', 'string', $payloadArray);
        Validation::checkClaimType('jti', 'string', $payloadArray);
        
        $this->token = $token;
        $this->payload = $payload;
        $this->header = $header;
        $this->signature = $signature;
    }

    /**
     * Gets message part of the token.
     * 
     * @return string
     */
    public function getMessage(): string
    {
        return sprintf('%s.%s', $this->getHeader(), $this->getPayload());
    }

    /**
     * Gets payload part of the token.
     * 
     * @return string
     */
    public function getPayload(): string
    {
        return $this->payload;
    }

    /**
     * Gets header part of the token.
     * 
     * @return string
     */
    public function getHeader(): string
    {
        return $this->header;
    }

    /**
     * Get signature part of the token.
     * 
     * @return string
     */
    public function getSignature(): string
    {
        return $this->signature;
    }

    /**
     * Performs auto decoding.
     *
     * @return TokenDecoded
     */
    public function decode(): TokenDecoded
    {
        return JWT::decode($this);
    }

    /**
     *
     * Performs auto validation using given key.
     *
     * @param string $secret Key
     * @param string|null $algorithm Force algorithm to signature verification (recommended)
     * @param int|null $leeway Optional leeway
     * @throws Exceptions\InsecureTokenException
     * @throws Exceptions\IntegrityViolationException
     * @throws Exceptions\MissingClaimException
     * @throws Exceptions\TokenExpiredException
     * @throws Exceptions\TokenInactiveException
     * @throws Exceptions\UnsupportedAlgorithmException
     */
    public function validate(string $secret, ?string $algorithm = null, ?int $leeway = null): void
    {
        JWT::validate($this, $secret, $algorithm, $leeway);
    }

    /**
     * Returns string representation of token.
     * 
     * @return string
     */
    public function __toString(): string
    {
        return $this->token;
    }
}
