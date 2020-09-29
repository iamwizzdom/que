<?php


namespace que\security;


use Exception;
use que\security\jwt\Exceptions\EmptyTokenException;
use que\security\jwt\Exceptions\InsecureTokenException;
use que\security\jwt\Exceptions\InvalidClaimTypeException;
use que\security\jwt\Exceptions\InvalidStructureException;
use que\security\jwt\Exceptions\MissingClaimException;
use que\security\jwt\Exceptions\SigningFailedException;
use que\security\jwt\Exceptions\UndefinedAlgorithmException;
use que\security\jwt\Exceptions\UnsupportedAlgorithmException;
use que\security\jwt\Exceptions\UnsupportedTokenTypeException;
use que\security\jwt\JWT;
use que\security\jwt\TokenDecoded;

class JWTGenerator
{
    /**
     * Encode algorithm
     * @var string
     */
    private string $algo;

    /**
     * Expiration time
     * @var int
     */
    private ?int $exp = null;

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

    private array $claims = [
        'iat' => null, // Issued at
        'jti' => null, // JWT ID
        'iss' => null, // Issuer
        'nbf' => null // Not before
    ];

    /**
     * JWTGenerator constructor.
     */
    public function __construct()
    {
        $this->algo = JWT::DEFAULT_ALGORITHM;
        $this->claims['iat'] = APP_TIME;
        $this->claims['jti'] = unique_id();
        $this->claims['iss'] = 'Que/v' . QUE_VERSION;
        $this->claims['nbf'] = $this->claims['iat'];
        $ttl = config('auth.jwt.ttl', TIMEOUT_ONE_HOUR);
        if ($ttl) $this->exp = ($this->claims['iat'] + $ttl);
        $this->secret = (string) config('auth.jwt.secret', '');
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
        $this->claims['iat'] = $iat;
    }

    /**
     * @param false|string $jti
     */
    public function setID($jti): void
    {
        $this->claims['jti'] = $jti;
    }

    /**
     * @param string $iss
     */
    public function setIssuer(string $iss): void
    {
        $this->claims['iss'] = $iss;
    }

    /**
     * @param int $nbf
     */
    public function setNotBefore(int $nbf): void
    {
        $this->claims['nbf'] = $nbf;
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
     * @param $claim
     * @param $value
     * @throws Exception
     */
    public function addCustomClaim($claim, $value) {

        // check for standard claim
        if (in_array($claim, [
            'iat',
            'jti',
            'iss',
            'nbf',
            'exp',
            'aud',
            'sub'
        ])) throw new Exception("Cannot override standard claim here, use designated method");

        $this->claims[$claim] = $value;
    }

    /**
     * @return string
     * @throws EmptyTokenException
     * @throws InsecureTokenException
     * @throws InvalidClaimTypeException
     * @throws InvalidStructureException
     * @throws MissingClaimException
     * @throws SigningFailedException
     * @throws UndefinedAlgorithmException
     * @throws UnsupportedAlgorithmException
     * @throws UnsupportedTokenTypeException
     */
    public function generate()
    {

        if ($this->exp) $this->claims['exp'] = $this->exp;
        if ($this->aud) $this->claims['aud'] = $this->aud;
        if ($this->sub) $this->claims['sub'] = $this->sub;

        $encode = new TokenDecoded([
            'alg' => $this->algo,
            'typ' => 'JWT'
        ], $this->claims);

        return $encode->encode($this->secret, $this->algo)->__toString();
    }
}