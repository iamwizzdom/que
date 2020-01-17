<?php
/**
 * Created by PhpStorm.
 * User: Wisdom Emenike
 * Date: 8/10/2018
 * Time: 8:36 PM
 */

namespace que\common\exception;


use Exception;
use Throwable;

class AlertException extends Exception
{

    /**
     * @var string
     */
    private $title;

    /**
     * @var int
     */
    private $type;

    /**
     * @var string
     */
    private $buttonTitle;

    /**
     * @var string
     */
    private $buttonUrl;

    /**
     * @var int
     */
    private $buttonOption;

    /**
     * AlertException constructor.
     * @param string $message
     * @param string $title
     * @param int $type
     * @param string $buttonTitle
     * @param string $buttonUrl
     * @param int $buttonOption
     * @param Throwable|null $previous
     */
    public function __construct(string $message = "", string $title = "", int $type = 0, string $buttonTitle = "",
                                string $buttonUrl = "", int $buttonOption = 0, Throwable $previous = null)
    {
        parent::__construct($message, $type, $previous);
        $this->setTitle($title);
        $this->setType($type);
        $this->setButtonTitle($buttonTitle);
        $this->setButtonUrl($buttonUrl);
        $this->setButtonOption($buttonOption);
    }

    /**
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * @param string $title
     */
    private function setTitle(string $title): void
    {
        $this->title = $title;
    }

    /**
     * @return int
     */
    public function getType(): int
    {
        return $this->type;
    }

    /**
     * @param int $type
     */
    private function setType(int $type): void
    {
        $this->type = $type;
    }

    /**
     * @return string
     */
    public function getButtonTitle(): string
    {
        return $this->buttonTitle;
    }

    /**
     * @param string $buttonTitle
     */
    private function setButtonTitle(string $buttonTitle): void
    {
        $this->buttonTitle = $buttonTitle;
    }

    /**
     * @return string
     */
    public function getButtonUrl(): string
    {
        return $this->buttonUrl;
    }

    /**
     * @param string $buttonUrl
     */
    private function setButtonUrl(string $buttonUrl): void
    {
        $this->buttonUrl = $buttonUrl;
    }

    /**
     * @return int
     */
    public function getButtonOption(): int
    {
        return $this->buttonOption;
    }

    /**
     * @param int $buttonOption
     */
    private function setButtonOption(int $buttonOption): void
    {
        $this->buttonOption = $buttonOption;
    }

}