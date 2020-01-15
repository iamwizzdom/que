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
    private $response = array();

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
        return $this->response['status'];
    }

    /**
     * @return mixed
     */
    public function getResponse()
    {
        return $this->response;
    }

    /**
     * @return array
     */
    public function getResponseArray(): array
    {
        return json_decode($this->response['response'], true) ?: [];
    }

    /**
     * @return string
     */
    public function getResponseString(): string
    {
        return $this->response['response'];
    }

}