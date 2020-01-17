<?php
/**
 * Created by PhpStorm.
 * User: Wisdom Emenike
 * Date: 8/20/2018
 * Time: 2:04 PM
 */

namespace que\common\validate;

use que\common\exception\QueException;

abstract class FileBase
{
    protected $uploadDir = APP_PATH . "/storage";
    protected $uploadMax = 0;
    protected $uploadOverwrite = false;
    protected $uploadMultiple = false;
    protected $allowedExtensions = array();
    protected $allowedMimeType = array();
    protected $formatName = true;
    protected $fileInfo = array();
    protected $errors = array();
    private $fileName = null;

    /**
     * @param $error
     */
    public function addError($error){
        $this->errors[] = $error;
    }

    /**
     * @return int
     */
    public function hasError(): int {
        return count($this->errors);
    }

    /**
     * @return array
     */
    public function getErrors(): array {
        return $this->errors;
    }

    /**
     * @param string $key
     * @return array|bool|mixed
     */
    public function getFileInfo($key = "") {
        if (empty($key)) return $this->fileInfo;

        if (array_key_exists($key, $this->fileInfo))
            return $this->fileInfo[$key];

        return false;
    }

    /**
     * @return string|null
     */
    public function getFileName(): ?string
    {
        return $this->fileName;
    }

    /**
     * @param array $extension
     * @param bool $auto_set_mime_type
     */
    public function setAllowedExtension(array $extension, bool $auto_set_mime_type = false){
        $this->allowedExtensions = $extension;
        if ($auto_set_mime_type)
            foreach ($extension as $ext)
                array_push($this->allowedMimeType, mime_type_from_extension($ext));
    }

    /**
     * @param array $MimeType
     */
    public function setAllowedMimeType(array $MimeType){
        $this->allowedMimeType = $MimeType;
    }

    /**
     * @param string $dir
     * @throws QueException
     */
    public function setUploadDir(string $dir) {

        if (empty($dir)) return;

        if (!is_dir("{$this->uploadDir}/{$dir}") && !mkdir("{$this->uploadDir}/{$dir}", 0777, true))
            throw new QueException("Directory [{$dir}] Doest Not Exist", "File Upload");

        if ($this->checkDir("{$this->uploadDir}/{$dir}")) $this->uploadDir = "{$this->uploadDir}/{$dir}";
        else throw new QueException("Directory Not Writable", "File Upload");
    }

    /**
     * @param int $max
     */
    public function setMaxFileSize(int $max = 1024){
        $this->uploadMax = $max;
    }

    /**
     * @param bool $overwrite
     */
    public function setOverwrite(bool $overwrite = true){
        $this->uploadOverwrite = $overwrite;
    }

    /**
     * @param string $name
     */
    public function setFileName(string $name = ""){
        $this->fileName = $name;
    }

    /**
     * @param bool $format
     */
    public function setFormatName(bool $format = false){
        $this->formatName = $format;
    }

    /**
     * @param string|null $dir
     * @return bool
     */
    public function checkDir(string $dir = null): bool {
        if ($dir === null || !is_dir($dir) || !is_writable($dir))
            return false;
        return true;
    }

    /**
     * @param string $fileName
     * @return string|string[]|null
     */
    public function formatName(string $fileName) {
        $fileName = trim($fileName);
        $fileName = preg_replace('/ /', '_', $fileName);
        $fileName = strtolower($fileName);
        return $fileName;
    }
}