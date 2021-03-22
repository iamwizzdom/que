<?php


namespace que\database\model;


use que\database\QueryResponse;

class ModelQueryResponse
{
    /**
     * @var QueryResponse
     */
    private QueryResponse $response;

    /**
     * ModelQueryResponse constructor.
     * @param QueryResponse $response
     */
    public function __construct(QueryResponse $response)
    {
        $this->response = $response;
    }

    /**
     * @return bool
     */
    public function isSuccessful(): bool {
        return $this->response->isSuccessful();
    }

    /**
     * @return string|null
     */
    public function getQueryError(): ?string {
        return $this->response->getQueryError();
    }

    /**
     * @return string
     */
    public function getQueryString(): string {
        return $this->response->getQueryString();
    }
}