<?php
/**
 * Created by PhpStorm.
 * User: Wisdom Emenike
 * Date: 8/20/2018
 * Time: 2:04 PM
 */

namespace que\common\validator;

abstract class FileBase
{
    protected string $storageDir = APP_PATH . "/storage/";
    protected string $uploadDir = "";
    protected int $uploadMax = 0;
    protected bool $uploadOverwrite = false;
    protected array $allowedExtensions = [];
    protected array $allowedMimeType = [];
    protected bool $formatName = true;
    protected array $fileInfo = [];
    protected array $errors = [];
    private $fileName = null;

    /**
     * @param $key
     * @param string $error
     */
    public function addError($key, string $error){
        $this->errors[$key][] = $error;
    }

    /**
     * @param $key
     * @return bool
     */
    public function hasError($key): bool {
        return count(($this->errors[$key] ?? [])) > 0;
    }

    /**
     * @return bool
     */
    public function hasAnyError(): bool {
        return count($this->errors) > 0;
    }

    /**
     * @param $key
     * @param $index
     * @return mixed|null
     */
    public function getError($key, $index) {
        return $this->errors[$key][$index] ?? null;
    }

    /**
     * @param $key
     * @return array
     */
    public function getErrors($key): array {
        return $this->errors[$key] ?? [];
    }

    /**
     * @return array
     */
    public function getAllErrors(): array {
        return $this->errors;
    }

    /**
     * @param null $key
     * @return array|mixed|null
     */
    public function getFileInfo($key = null) {
        if (empty($key)) return $this->fileInfo;
        return $this->fileInfo[$key] ?? null;
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
     */
    public function setUploadDir(string $dir) {

        $this->uploadDir = $dir;
        $this->uploadDir = ($this->uploadDir !== null ? trim($this->uploadDir, '/') : '');
        $this->uploadDir = ($this->uploadDir !== null ? trim($this->uploadDir, '\\') : '');
        if (!empty($this->uploadDir)) $this->uploadDir = "{$this->uploadDir}/";
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
        $fileName = preg_replace('/\s/', '_', $fileName);
        $fileName = strtolower($fileName);
        return $fileName;
    }
}