<?php
/**
 * Created by PhpStorm.
 * User: Wisdom Emenike
 * Date: 8/20/2018
 * Time: 1:35 PM
 */

namespace que\common\validate;

use que\common\exception\QueException;
use que\http\input\Input;

class File extends FileBase
{

    /**
     * @var Input
     */
    private $input;

    /**
     * @var File
     */
    private static $instance;

    protected function __construct(Input $input)
    {
        $this->input = $input;
    }

    private function __clone()
    {
        // TODO: Implement __clone() method.
    }

    private function __wakeup()
    {
        // TODO: Implement __wakeup() method.
    }

    /**
     * @param Input $input
     * @return File
     */
    public static function getInstance(Input $input): File
    {
        if (!isset(self::$instance))
            self::$instance = new self($input);
        return self::$instance;
    }

    /**
     * @param $object
     * @throws QueException
     */
    public function upload($object)
    {

        $this->uploadMultiple = false;

        if (empty($this->uploadDir))
            $this->uploadDir = dirname(__FILE__) . '/';

        if (!$this->checkDir($this->uploadDir)) {
            throw new QueException("Directory [{$this->uploadDir}] is not writable", "File Upload");
        }

        try {

            if (!array_key_exists($object, $this->input->_get()))
                throw new QueException("No file was uploaded with the name '{$object}'", "File Upload");

            $files = $this->input[$object];

            if (!isset($files['name']))
                throw new QueException("Can't find uploaded file", "File Upload");

            if ($error = $this->checkUploadError($files['error']))
                throw new QueException($error, "File Upload");

            if ($files['size'] <= 0)
                throw new QueException("{$files['name']} is empty", "File Upload");

            $ext = pathinfo($files['name'], PATHINFO_EXTENSION);
            $ext = strtolower($ext);

            if (!empty($this->allowedExtensions))
                if (!in_array($ext, $this->allowedExtensions))
                    throw new QueException("{$files['name']} -- file extension '{$ext}' is not supported", "File Upload");

            if (!empty($this->allowedMimeType)) {
                $fInfo = finfo_open(FILEINFO_MIME_TYPE);
                $fileType = finfo_file($fInfo, $files['tmp_name']);
                if (!in_array($fileType, $this->allowedMimeType))
                    throw new QueException("{$files['name']} -- file type '{$fileType}' is not supported", "File Upload");
                finfo_close($fInfo);
            }

            if ($this->uploadMax)
                if ($files['size'] >= $this->uploadMax)
                    throw new QueException(sprintf("%s has a file size of %s which exceeds %s's maximum file upload size of %s", $files['name'],
                        convert_bytes($files['size'], 2), APP_NAME, convert_bytes($this->uploadMax, 2)), "File Upload");

            if ($this->getFileName() !== null)
                $files['name'] = $this->getFileName() . ".$ext";

            if ($this->formatName)
                $files['name'] = $this->formatName($files['name']);

            $newName = $files['name'];

            if ($this->uploadOverwrite)
                if (file_exists($this->uploadDir . DIRECTORY_SEPARATOR . $newName))
                    unlink($this->uploadDir . DIRECTORY_SEPARATOR . $newName);

            $x = 0;
            while (!$this->uploadOverwrite && file_exists($this->uploadDir . DIRECTORY_SEPARATOR . $newName)) {
                $newName = basename($files['name'], ".{$ext}") . "_{$x}.{$ext}";
                $x++;

                if ($x > MAX_FILE)
                    throw new QueException(APP_NAME .
                        " can't accept more files from you, you have uploaded " . MAX_FILE . " files already", "File Upload");
            }

            $files['name'] = $newName;

            if (!move_uploaded_file($files['tmp_name'], $this->uploadDir . DIRECTORY_SEPARATOR . $files['name']))
                throw new QueException("{$files['name']} could not be uploaded", "File Upload");

            $this->fileInfo['name'] = $files['name'];
            $this->fileInfo['dir'] = $this->uploadDir;
            $this->fileInfo['path'] = $this->uploadDir . DIRECTORY_SEPARATOR . $files['name'];
            $this->fileInfo['ext'] = $ext;
            $this->fileInfo['size'] = $files['size'];
            $this->fileInfo['type'] = $files['type'];
            $this->fileInfo['hash'] = sha1_file($this->fileInfo['path']);

        } catch (QueException $e) {
            $this->addError($e->getMessage());
        }
    }

    /**
     * @param $object
     * @throws QueException
     */
    public function uploadMulti($object)
    {

        $this->uploadMultiple = true;

        if (empty($this->uploadDir))
            $this->uploadDir = dirname(__FILE__) . '/';

        if (!$this->checkDir($this->uploadDir)) {
            throw new QueException("Directory [{$this->uploadDir}] is not writable", "File Upload");
        }

        if (!array_key_exists($object, $this->input->_get()))
            throw new QueException("No file was uploaded with the name {$object}", "File Upload");

        $files = $this->input[$object];
        $count = count($files['name']) - 1;

        for ($current = 0; $current <= $count; $current++) {

            try {

                if ($error = $this->checkUploadError($files['error'][$current]))
                    throw new QueException($error, "File Upload");

                if ($files['size'][$current] <= 0)
                    throw new QueException("{$files['name'][$current]} is empty", "File Upload");

                $ext = pathinfo($files['name'], PATHINFO_EXTENSION);
                $ext = strtolower($ext);

                if (!empty($this->allowedExtensions))
                    if (!in_array($ext, $this->allowedExtensions))
                        throw new QueException("{$files['name'][$current]} -- file extension '{$ext}' is not supported", "File Upload");

                if (!empty($this->allowedMimeType)) {
                    $fInfo = finfo_open(FILEINFO_MIME_TYPE);
                    $fileType = finfo_file($fInfo, $files['tmp_name']);
                    if (!in_array($fileType, $this->allowedMimeType))
                        throw new QueException("{$files['name'][$current]} -- file type '{$fileType}' is not supported", "File Upload");
                    finfo_close($fInfo);
                }

                if ($this->uploadMax)
                    if ($files['size'] >= $this->uploadMax)
                        throw new QueException(sprintf("%s has a file size of %s which exceeds %s's maximum file upload size of %s", $files['name'],
                            convert_bytes($files['size'], 2), APP_NAME, convert_bytes($this->uploadMax, 2)), "File Upload");

                if ($this->getFileName() !== null)
                    $files['name'] = $this->getFileName();

                if ($this->formatName)
                    $files['name'][$current] = $this->formatName($files['name'][$current]);

                $newName = $files['name'][$current];

                if ($this->uploadOverwrite)
                    if (file_exists($this->uploadDir . DIRECTORY_SEPARATOR . $newName))
                        unlink($this->uploadDir . DIRECTORY_SEPARATOR . $newName);

                $x = 0;
                while (!$this->uploadOverwrite && file_exists($this->uploadDir . $files['name'][$current])) {
                    $newName = basename($files['name'][$current], ".{$ext}") . "_{$x}.{$ext}";
                    $x++;

                    if ($x > MAX_FILE)
                        throw new QueException(APP_NAME .
                            " can't accept more files from you, you have uploaded" . MAX_FILE . " files already", "File Upload");
                }

                $files['name'][$current] = $newName;

                if (!move_uploaded_file($files['tmp_name'][$current], $this->uploadDir . $files['name'][$current]))
                    throw new QueException("{$files['name'][$current]} could not be uploaded", "File Upload");

                $this->fileInfo[$current]['name'] = $files['name'][$current];
                $this->fileInfo[$current]['dir'] = $this->uploadDir;
                $this->fileInfo[$current]['path'] = $this->uploadDir . $files['name'][$current];
                $this->fileInfo[$current]['ext'] = $ext;
                $this->fileInfo[$current]['size'] = $files['size'][$current];
                $this->fileInfo[$current]['type'] = $files['type'][$current];
                $this->fileInfo[$current]['hash1'] = sha1_file($this->fileInfo[$current]['path']);

            } catch (QueException $e) {
                $this->addError($e->getMessage());
            }
        }
    }

    /**
     * @param $uploadError
     * @return string
     */
    protected function checkUploadError($uploadError)
    {
        $error = ""; // check for $this->input Errors
        switch ($uploadError) {
            case UPLOAD_ERR_OK:
                break;
            case UPLOAD_ERR_INI_SIZE:
                //	$error = 'File exceeds the upload_max_filesize directive in php.ini';
                $error = 'File exceeds the expected size';
                break;
            case UPLOAD_ERR_FORM_SIZE:
                //$error = 'File exceeds the MAX_FILE_SIZE directive that was specified in the HTML form';
                $error = 'File exceeds the expected size';
                break;
            case UPLOAD_ERR_PARTIAL:
                $error = 'File was only partially uploaded';
                break;
            case UPLOAD_ERR_NO_FILE:
                $error = 'No file was uploaded';
                break;
            case UPLOAD_ERR_NO_TMP_DIR:
                $error = 'Missing a temporary folder';
                break;
            case UPLOAD_ERR_CANT_WRITE:
                $error = 'Failed to write File to disk';
                break;
            case UPLOAD_ERR_EXTENSION:
                $error = 'File stopped by extension';
                break;
            default:
                $error = 'Unidentified RuntimeError, caused by File';
                break;
        }

        return $error;
    }
}