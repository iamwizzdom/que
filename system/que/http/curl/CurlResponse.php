<?php
/**
 * Created by PhpStorm.
 * User: Wisdom Emenike
 * Date: 9/15/2018
 * Time: 4:06 PM
 */

namespace que\http\curl;


class CurlResponse
{

    /**
     * @var array
     */
    private array $response = [];

    /**
     * Response constructor.
     * @param array $response
     */
    public function __construct(array $response)
    {
        $this->setResponse($response);
    }

    /**
     * @param array $response
     */
    private function setResponse(array $response)
    {
        $this->response = $response;
    }

    /**
     * @return bool
     */
    public function isSuccessful(): bool
    {
        return $this->response['status'] ?? false;
    }

    /**
     * @param $key
     * @return mixed|null
     */
    public function getCurlInfo($key)
    {
        return $this->response['info'][$key] ?? null;
    }

    /**
     * @return array
     */
    public function getResponse(): array
    {
        return $this->response;
    }

    /**
     * @return array
     */
    public function getResponseArray(): array
    {
        return json_decode($this->getResponseString(), true) ?: [];
    }

    /**
     * @return string
     */
    public function getResponseString(): string
    {
        return $this->response['response'] ?? "";
    }

}