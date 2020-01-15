<?php
/**
 * Created by PhpStorm.
 * User: Wisdom Emenike
 * Date: 27/7/2019
 * Time: 12:21 AM
 */

namespace que\security\JWT;

/**
 * This class is representation of decoded JSON Web Token (JWT).
 *
 */
class TokenDecoded
{

    /**
     * Array containing token's header elements.
     */
    protected $header;
    
    /**
     * Array containing token's payload elements.
     */
    protected $payload;

    /**
     * @param array|null $header
     * @param array|null $payload
     */
    public function __construct(?array $header = [], ?array $payload = [])
    {
        $this->payload = $payload;
        $this->header = $header;
    }

    /**
     * Gets array with token's payload.
     * 
     * @return array
     */
    public function getPayload(): array
    {
        return $this->payload;
    }

    /**
     * Sets array with token's payload.
     * 
     * @param array $payload
     */
    public function setPayload(array $payload): void
    {
        $this->payload = $payload;
    }

    /**
     * Gets array with token's header.
     * 
     * @return array
     */
    public function getHeader(): array
    {
        return $this->header;
    }

    /**
     * Sets array with token's header.
     * 
     * @param array $header
     */
    public function setHeader(array $header): void
    {
        $this->header = $header;
    }

    /**
     * Performs auto encoding.
     *
     * @param string $key Key used to signing token.
     * @param string|null $algorithm Optional algorithm to be used when algorithm is not yet defined in token's header.
     * @return TokenEncoded
     * @throws Exceptions\EmptyTokenException
     * @throws Exceptions\InsecureTokenException
     * @throws Exceptions\InvalidClaimTypeException
     * @throws Exceptions\InvalidStructureException
     * @throws Exceptions\SigningFailedException
     * @throws Exceptions\UndefinedAlgorithmException
     * @throws Exceptions\UnsupportedAlgorithmException
     * @throws Exceptions\UnsupportedTokenTypeException
     */
    public function encode(string $key, ?string $algorithm = null) : TokenEncoded
    {
        return JWT::encode($this, $key, $algorithm);
    }
}
