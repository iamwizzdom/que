<?php
/**
 * Created by PhpStorm.
 * User: tolujimoh
 * Date: 30/11/2017
 * Time: 8:24 AM
 */

namespace que\http\curl;

use que\support\Arr;

abstract class CurlNetwork
{

    /**
     * @var string
     */
    private string $url = "";

    /**
     * @var string
     */
    private string $method = "";

    /**
     * @var array
     */
    private array $post = [];

    /**
     * @var array
     */
    private array $postFiles = [];

    /**
     * @var array
     */
    private array $headers = [];

    /**
     * @var bool
     */
    private bool $isBodyPost = false;

    /**
     * @var int
     */
    private int $timeout = 0;

    /**
     * @var bool
     */
    private ?bool $verifySSL = null;

    /**
     * @var int
     */
    private int $httpVersion = CURL_HTTP_VERSION_NONE;

    /**
     * @return string
     */
    public function getUrl(): string
    {
        return $this->url;
    }

    /**
     * @param string $url
     */
    public function setUrl(string $url)
    {
        $this->url = $url;
    }

    /**
     * @return string
     */
    public function getMethod(): string
    {
        return $this->method;
    }

    /**
     * @param string $method
     */
    public function setMethod(string $method): void
    {
        $this->method = $method;
    }

    /**
     * @return array
     */
    public function getPost(): array
    {
        return $this->post;
    }

    /**
     * @param string $name
     * @param $value
     */
    public function setPost(string $name, $value): void
    {
        $this->post[$name] = $value;
    }

    /**
     * @param array $post
     */
    public function setPosts(array $post): void
    {
        $this->post = $post;
    }

    /**
     * @return array
     */
    public function getPostFiles(): array
    {
        return $this->postFiles;
    }

    /**
     * @param string $name
     * @param string $filename
     */
    public function setPostFiles(string $name, string $filename): void
    {
        $this->postFiles[$name] = $filename;
    }

    /**
     * @return array
     */
    public function getHeaders(): array
    {
        return $this->headers;
    }

    /**
     * @param string $name
     * @param $value
     */
    public function setHeader(string $name, $value): void
    {
        $this->headers[] = "{$name}: {$value}";
    }

    /**
     * @param array $headers
     */
    public function setHeaders(array $headers)
    {
        if (Arr::isAssoc($headers)) {
            foreach ($headers as $key => $value) {
                $this->headers[] = "{$key}: {$value}";
            }
            return;
        }

        $this->headers = $headers;
    }

    /**
     * @return bool
     */
    public function isBodyPost(): bool
    {
        return $this->isBodyPost;
    }

    /**
     * @param bool $isBodyPost
     */
    public function setIsBodyPost(bool $isBodyPost): void
    {
        $this->isBodyPost = $isBodyPost;
    }

    /**
     * @return int
     */
    public function getHttpVersion(): int
    {
        return $this->httpVersion;
    }

    /**
     * @param int $httpVersion
     */
    public function setHttpVersion(int $httpVersion): void
    {
        $this->httpVersion = $httpVersion;
    }

    /**
     * @return int
     */
    public function getTimeout(): int
    {
        return $this->timeout;
    }

    /**
     * @param int $seconds
     */
    public function setTimeout(int $seconds)
    {
        $this->timeout = $seconds;
    }

    /**
     * @return bool|null
     */
    public function isVerifySSL(): ?bool
    {
        return $this->verifySSL;
    }

    /**
     * @param bool $verifySSL
     */
    public function setVerifySSL(bool $verifySSL): void
    {
        $this->verifySSL = $verifySSL;
    }

    private function _flush() {
        $this->url = "";
        $this->method = "";
        $this->post = [];
        $this->postFiles = [];
        $this->headers = [];
        $this->isBodyPost = false;
        $this->timeout = 0;
        $this->verifySSL = null;
        $this->httpVersion = CURL_HTTP_VERSION_NONE;
    }

    /**
     * @return array
     */
    protected function exec(): array
    {

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->getUrl());

        $post = $this->getPost();
        $files = $this->getPostFiles();
        $headers = $this->getHeaders();

        $content_type = array_find($headers, function ($value) {
            return str__starts_with($value, "content-type", true);
        });

        if (!empty($files)) {

            foreach ($files as $name => $file) {
                if (function_exists('curl_file_create')) $file = curl_file_create($file); // For PHP 5.5+
                else $file = '@' . realpath($file);
                $post[$name] = $file;
            }

            if ($content_type == null) $headers[] = $content_type = "Content-Type: multipart/form-data";

        }

        if (!empty($post)) curl_setopt($ch, CURLOPT_POST, true);

        if (!empty($headers)) curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $mime_type = mime_type_from_extension('json');

        if ($content_type && strcmp(str_start_from(strtolower($content_type), 'content-type: '), $mime_type) == 0) $this->setIsBodyPost(true);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        if (!empty($post)) curl_setopt($ch, CURLOPT_POSTFIELDS, ($this->isBodyPost() ? http_build_query($post) : $post));

        if ($this->getTimeout() > 0) curl_setopt($ch, CURLOPT_TIMEOUT, $this->getTimeout());

        if (!empty($this->getMethod())) curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $this->getMethod());

        if ($this->getHttpVersion() != CURL_HTTP_VERSION_NONE) curl_setopt($ch, CURLOPT_HTTP_VERSION, $this->getHttpVersion());

        if ($this->isVerifySSL() !== null) curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, $this->isVerifySSL());


        $content = curl_exec($ch);

        if ($content === false) {
            $response = [
                'status' => false,
                'response' => curl_error($ch)
            ];
            curl_close($ch);
            $this->_flush();
            return $response;
        }

        // Initiate Retry
        $retry = 0;

        // Try again if it fails/times out
        while (curl_errno($ch) == CURLE_OPERATION_TIMEDOUT && $retry < MAX_RETRY) {
            $content = curl_exec($ch);
            $retry++;
            sleep(2);
        }

        if (curl_errno($ch)) {
            $response = [
                'status' => false,
                'response' => curl_error($ch)
            ];
            curl_close($ch);
            $this->_flush();
            return $response;
        }

        $info = curl_getinfo($ch);

        curl_close($ch);
        $this->_flush();

        return [
            'status' => true,
            'info' => $info,
            'response' => $content
        ];
    }

}

