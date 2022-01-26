<?php
/**
 * Created by PhpStorm.
 * User: Wisdom Emenike
 * Date: 2/2/2018
 * Time: 10:52 AM
 */

namespace que\mail;

class Mail {
    
    const ENCODING_7BIT = '7bit';
    const ENCODING_8BIT = '8bit';
    const ENCODING_BASE64 = 'base64';
    const ENCODING_BINARY = 'binary';
    const ENCODING_QUOTED_PRINTABLE = 'quoted-printable';

    /**
     * @var string
     */
    private $key = '';

    /**
     * @var array
     */
    private $from = [
        'name' => '',
        'email' => ''
    ];

    /**
     * @var array
     */
    private $replyTo = [];

    /**
     * @var array
     */
    private $recipient = [];

    /**
     * @var array
     */
    private $cc = [];

    /**
     * @var array
     */
    private $bcc = [];

    /**
     * @var string
     */
    private $subject = '';

    /**
     * @var string
     */
    private $body = '';

    /**
     * @var string
     */
    private $bodyPath = '';

    /**
     * @var string
     */
    private $htmlPath = '';

    /**
     * @var array
     */
    private $attachment = [];

    /**
     * @var array
     */
    private $stringAttachment = [];

    /**
     * @var array
     */
    private $data = [];

    /**
     * @var string
     */
    private $error = '';

    /**
     * Mail constructor.
     * @param string $key
     */
    public function __construct(string $key)
    {
        $this->key = $key;
    }

    /**
     * @return string
     */
    public function getKey(): string
    {
        return $this->key;
    }

    /**
     * @return array
     */
    public function getFrom(): array
    {
        return $this->from;
    }

    /**
     * @param string $email
     * @param string $name
     */
    public function setFrom(string $email, string $name = ''): void
    {
        $this->from['email'] = $email;
        $this->from['name'] = $name;
    }

    /**
     * @return array
     */
    public function getReplyTo(): array
    {
        return $this->replyTo;
    }

    /**
     * @param string $email
     * @param string $name
     */
    public function addReplyTo(string $email, string $name = ''): void
    {
        array_push($this->replyTo, [
            'email' => $email,
            'name' => $name
        ]);
    }

    /**
     * @return array
     */
    public function getRecipient(): array
    {
        return $this->recipient;
    }

    /**
     * @param string $email
     * @param string $name
     */
    public function addRecipient(string $email, string $name = ''): void
    {
        array_push($this->recipient, [
            'email' => $email,
            'name' => $name
        ]);
    }

    /**
     * @return array
     */
    public function getCc(): array
    {
        return $this->cc;
    }

    /**
     * @param string $email
     * @param string $name
     */
    public function addCc(string $email, string $name = ''): void
    {
        array_push($this->cc, [
            'email' => $email,
            'name' => $name
        ]);
    }

    /**
     * @return array
     */
    public function getBcc(): array
    {
        return $this->bcc;
    }

    /**
     * @param string $email
     * @param string $name
     */
    public function addBcc(string $email, string $name = ''): void
    {
        array_push($this->bcc, [
            'email' => $email,
            'name' => $name
        ]);
    }

    /**
     * @return string
     */
    public function getSubject(): string
    {
        return $this->subject;
    }

    /**
     * @param string $subject
     */
    public function setSubject(string $subject): void
    {
        $this->subject = $subject;
    }

    /**
     * @return string
     */
    public function getBody(): string
    {
        return $this->body;
    }

    /**
     * @param string $body
     */
    public function setBody(string $body): void
    {
        $this->body = $body;
    }

    /**
     * @return string
     */
    public function getBodyPath(): string
    {
        return $this->bodyPath;
    }

    /**
     * @param string $bodyPath
     */
    public function setBodyPath(string $bodyPath): void
    {
        $this->bodyPath = $bodyPath;
    }

    /**
     * @return string
     */
    public function getHtmlPath(): string
    {
        return $this->htmlPath;
    }

    /**
     * @param string $htmlPath
     */
    public function setHtmlPath(string $htmlPath): void
    {
        $this->htmlPath = $htmlPath;
    }

    /**
     * @return array
     */
    public function getAttachment(): array
    {
        return $this->attachment;
    }

    /**
     * @param string $path
     * @param string $name
     * @param string $encoding
     * @param string $type
     * @param string $disposition
     */
    public function addAttachment(string $path, string $name = '', $type = '', 
                                  $encoding = self::ENCODING_BASE64, 
                                  $disposition = 'attachment'): void
    {
        array_push($this->attachment, [
            'path' => $path,
            'name' => $name,
            'type' => $type,
            'encoding' => $encoding,
            'disposition' => $disposition
        ]);
    }

    /**
     * @return array
     */
    public function getStringAttachment(): array
    {
        return $this->stringAttachment;
    }

    /**
     * @param string $data
     * @param string $name
     * @param string $encoding
     * @param string $type
     * @param string $disposition
     */
    public function addStringAttachment(string $data, string $name = '', $type = '', 
                                        $encoding = self::ENCODING_BASE64, 
                                        $disposition = 'attachment'): void
    {
        array_push($this->stringAttachment, [
            'data' => $data,
            'name' => $name,
            'type' => $type,
            'encoding' => $encoding,
            'disposition' => $disposition
        ]);
    }

    /**
     * @return array
     */
    public function getData(): array
    {
        return $this->data;
    }

    /**
     * @param array $data
     */
    public function setData(array $data): void
    {
        $this->data = $data;
    }

    /**
     * @return string
     */
    public function getError(): string
    {
        return $this->error;
    }

    /**
     * @param string $error
     */
    public function setError(string $error): void
    {
        $this->error = $error;
    }

}