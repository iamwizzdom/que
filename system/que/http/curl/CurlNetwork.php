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
    private int $timeout = 60;

    /**
     * @var bool
     */
    private bool $verifySSL = false;

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
     * @param string $name
     * @param $value
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
        $this->headers[$name] = $value;
    }

    /**
     * @param array $headers
     */
    public function setHeaders(array $headers)
    {
        if (!Arr::isAssoc($headers)) {
            foreach ($headers as $k => $v) {
                list($key, $value) = explode(':', $v, 2);
                $headers[strtolower($key)] = str_strip_spaces($value);
                unset($headers[$k]);
            }
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
     * @return bool
     */
    public function isVerifySSL(): bool
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

    /**
     * @return array
     */
    protected function exec(): array
    {

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->getUrl());
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, $this->isVerifySSL());

        $post = $this->getPost();
        $files = $this->getPostFiles();
        $headers = $this->getHeaders();
        $headers = array_change_key_case($headers, CASE_LOWER);

        if (!empty($files)) {
            foreach ($files as $name => $file) {
                if (function_exists('curl_file_create')) $file = curl_file_create($file); // For PHP 5.5+
                else $file = '@' . realpath($file);
                $post[$name] = $file;
            }
            $headers["content-type"] ??= "multipart/form-data";
        }

        if ($headers["content-type"] ?? '' === mime_type_from_extension('json')) {
            $this->setIsBodyPost(true);
        }

        if (!empty($post)) {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $this->isBodyPost() ? json_encode($post) : $post);
        }

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        if (!empty($headers)) {
            Arr::callback($headers, function ($value, $key) {
                return "{$key}: {$value}";
            });
            curl_setopt($ch, CURLOPT_HTTPHEADER, array_values($headers));
        }

        curl_setopt($ch, CURLOPT_TIMEOUT, $this->getTimeout());
        if (!empty($this->getMethod())) curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $this->getMethod());

        $content = curl_exec($ch);

        if ($content === false) {
            $response = [
                'status' => false,
                'response' => curl_error($ch)
            ];
            curl_close($ch);
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
            return $response;
        }

        $info = curl_getinfo($ch);

        curl_close($ch);

        return [
            'status' => true,
            'info' => $info,
            'response' => $content
        ];
    }

}

