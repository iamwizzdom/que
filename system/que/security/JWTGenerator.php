<?php


namespace que\security;


use que\security\JWT\Exceptions\EmptyTokenException;
use que\security\JWT\Exceptions\InsecureTokenException;
use que\security\JWT\Exceptions\InvalidClaimTypeException;
use que\security\JWT\Exceptions\InvalidStructureException;
use que\security\JWT\Exceptions\SigningFailedException;
use que\security\JWT\Exceptions\UndefinedAlgorithmException;
use que\security\JWT\Exceptions\UnsupportedAlgorithmException;
use que\security\JWT\Exceptions\UnsupportedTokenTypeException;
use que\security\JWT\JWT;
use que\security\JWT\TokenDecoded;

class JWTGenerator
{
    /**
     * Encode algorithm
     * @var string
     */
    private string $algo;

    /**
     * Issued at
     * @var int
     */
    private int $iat;

    /**
     * JWT ID
     * @var false|string
     */
    private string $jti;

    /**
     * Issuer
     * @var string
     */
    private string $iss;

    /**
     * Not before
     * @var int
     */
    private int $nbf;

    /**
     * Expiration time
     * @var int
     */
    private int $exp;

    /**
     * Subject
     * @var string
     */
    private ?string $sub = null;

    /**
     * Audience
     * @var string|null
     */
    private ?string $aud = null;

    /**
     * Secret key used to encode token
     * @var string
     */
    private string $secret;

    /**
     * JWTGenerator constructor.
     */
    public function __construct()
    {
        $this->algo = JWT::ALGORITHM_HS256;
        $this->iat = APP_TIME;
        $this->jti = unique_id();
        $this->iss = 'Que/v' . QUE_VERSION;
        $this->nbf = $this->iat;
        $this->exp = ($this->iat + ((60 * 60) * 10));
        $this->secret = (string)config('auth.jwt.secret', '');
    }

    /**
     * @param string $algo
     */
    public function setAlgorithm(string $algo): void
    {
        $this->algo = $algo;
    }

    /**
     * @param int $iat
     */
    public function setIssuedAt(int $iat): void
    {
        $this->iat = $iat;
    }

    /**
     * @param false|string $jti
     */
    public function setID($jti): void
    {
        $this->jti = $jti;
    }

    /**
     * @param string $iss
     */
    public function setIssuer(string $iss): void
    {
        $this->iss = $iss;
    }

    /**
     * @param int $nbf
     */
    public function setNotBefore(int $nbf): void
    {
        $this->nbf = $nbf;
    }

    /**
     * @param int $exp
     */
    public function setExpiration(int $exp): void
    {
        $this->exp = $exp;
    }

    /**
     * @param string $sub
     */
    public function setSubject(string $sub): void
    {
        $this->sub = $sub;
    }

    /**
     * @param string|null $aud
     */
    public function setAudience(?string $aud): void
    {
        $this->aud = $aud;
    }

    /**
     * @param string $secret
     */
    public function setSecret(string $secret): void
    {
        $this->secret = $secret;
    }

    /**
     * @return string
     * @throws EmptyTokenException
     * @throws InsecureTokenException
     * @throws InvalidClaimTypeException
     * @throws InvalidStructureException
     * @throws SigningFailedException
     * @throws UndefinedAlgorithmException
     * @throws UnsupportedAlgorithmException
     * @throws UnsupportedTokenTypeException
     */
    public function generate()
    {
        $claims = [
            'iat' => $this->iat,
            'jti' => $this->jti,
            'iss' => $this->iss,
            'nbf' => $this->nbf,
            'exp' => $this->exp,
        ];

        if ($this->aud) $claims['aud'] = $this->aud;
        if ($this->sub) $claims['sub'] = $this->sub;

        $encode = new TokenDecoded([
            'alg' => $this->algo,
            'typ' => 'JWT'
        ], $claims);

        return $encode->encode($this->secret, $this->algo)->__toString();
    }
}