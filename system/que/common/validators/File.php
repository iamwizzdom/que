<?php
/**
 * Created by PhpStorm.
 * User: Wisdom Emenike
 * Date: 8/20/2018
 * Time: 1:35 PM
 */

namespace que\common\validator;

use que\common\exception\QueException;
use que\http\request\Files;

class File extends FileBase
{

    /**
     * @var Files
     */
    private Files $files;

    /**
     * @var File
     */
    private static File $instance;

    protected function __construct(Files $files)
    {
        $this->files = $files;
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
     * @param Files $input
     * @return File
     */
    public static function getInstance(Files $input): File
    {
        if (!isset(self::$instance))
            self::$instance = new self($input);
        return self::$instance;
    }

    /**
     * @param string $name
     */
    public function validate(string $name) {

        $uploadDir = "{$this->storageDir}{$this->uploadDir}";

        try {

            if (!is_dir($uploadDir) && !mkdir($uploadDir, 0777, true))
                throw new QueException("Directory [" . str_start_from($uploadDir, 'storage/') . "] does not exist");

            if (!$this->checkDir($uploadDir))
                throw new QueException("Directory [" . str_start_from($uploadDir, 'storage/') . "] is not writable");

            if (!$this->files->_isset($name)) throw new QueException("No file was uploaded with the name '{$name}'");

            $files = $this->files[$name];

            if (!isset($files['name']))
                throw new QueException("Can't find uploaded file");

            if ($error = $this->checkUploadError($files['error'])) throw new QueException($error);

            if ($files['size'] <= 0) throw new QueException("{$files['name']} is empty");

            $ext = pathinfo($files['name'], PATHINFO_EXTENSION);
            $ext = strtolower($ext);

            if (!empty($this->allowedExtensions))
                if (!in_array($ext, $this->allowedExtensions))
                    throw new QueException("{$files['name']} -- file extension '{$ext}' is not supported");

            if (!empty($this->allowedMimeType)) {
                $fInfo = finfo_open(FILEINFO_MIME_TYPE);
                $fileType = finfo_file($fInfo, $files['tmp_name']);
                if (!in_array($fileType, $this->allowedMimeType))
                    throw new QueException("{$files['name']} -- file type '{$fileType}' is not supported");
                finfo_close($fInfo);
            }

            if ($this->uploadMax)
                if ($files['size'] >= $this->uploadMax)
                    throw new QueException(sprintf("%s has a file size of %s which exceeds the system's maximum file upload size of %s", $files['name'],
                        convert_bytes($files['size'], 2), convert_bytes($this->uploadMax, 2)));

        } catch (QueException $e) {

            $this->addError($name, $e->getMessage());
        }
    }

    /**
     * @param string $name
     * @param int $index
     */
    public function validateMulti(string $name, int $index) {

        $uploadDir = "{$this->storageDir}{$this->uploadDir}";

        try {

            if (!is_dir($uploadDir) && !mkdir($uploadDir, 0777, true))
                throw new QueException("Directory [" . str_start_from($uploadDir, 'storage/') . "] does not exist");

            if (!$this->checkDir($uploadDir))
                    throw new QueException("Directory [" . str_start_from($uploadDir, 'storage/') . "] is not writable");

            if (!$this->files->_isset($name)) throw new QueException("No file was uploaded with the name '{$name}'");

            $files = $this->files[$name];

            if (!isset($files['name'][$index])) throw new QueException("Can't find uploaded file");

            if ($error = $this->checkUploadError($files['error'][$index])) throw new QueException($error);

            if ($files['size'][$index] <= 0) throw new QueException("{$files['name'][$index]} is empty");

            $ext = pathinfo($files['name'][$index], PATHINFO_EXTENSION);
            $ext = strtolower($ext);

            if (!empty($this->allowedExtensions))
                if (!in_array($ext, $this->allowedExtensions))
                    throw new QueException("{$files['name'][$index]} -- file extension '{$ext}' is not supported");

            if (!empty($this->allowedMimeType)) {
                $fInfo = finfo_open(FILEINFO_MIME_TYPE);
                $fileType = finfo_file($fInfo, $files['tmp_name'][$index]);
                if (!in_array($fileType, $this->allowedMimeType))
                    throw new QueException("{$files['name'][$index]} -- file type '{$fileType}' is not supported");
                finfo_close($fInfo);
            }

            if ($this->uploadMax)
                if ($files['size'][$index] >= $this->uploadMax)
                    throw new QueException(sprintf("%s has a file size of %s which exceeds the system's maximum file upload size of %s", $files['name'],
                        convert_bytes($files['size'][$index], 2), convert_bytes($this->uploadMax, 2)));

        } catch (QueException $e) {

            $this->addError($name, $e->getMessage());
        }
    }

    /**
     * @param $name
     * @return bool
     */
    public function upload($name): bool
    {

        if ($this->hasError($name)) return false;

        $this->validate($name);

        if ($this->hasError($name)) return false;

        $files = $this->files[$name];

        $ext = pathinfo($files['name'], PATHINFO_EXTENSION);
        $ext = strtolower($ext);

        if ($this->getFileName() !== null)
            $files['name'] = "{$this->getFileName()}.{$ext}";

        if ($this->formatName)
            $files['name'] = $this->formatName($files['name']);

        $newName = $files['name'];

        if ($this->uploadOverwrite) {
            if (is_file($this->storageDir . $this->uploadDir . $newName))
                unlink($this->storageDir . $this->uploadDir . $newName);
        }

        $x = 0;
        while (!$this->uploadOverwrite && is_file($this->storageDir . $this->uploadDir . $newName)) {
            $newName = basename($files['name'], ".{$ext}") . "_{$x}.{$ext}";
            $x++;

            if ($x > MAX_FILE) {
                $this->addError($name, "The system can't accept more files from you. You have uploaded " . MAX_FILE . " files already");
                return false;
            }
        }

        $files['name'] = $newName;
        
        if (!move_uploaded_file($files['tmp_name'], $this->storageDir . $this->uploadDir . $newName)) {
            $this->addError($name, "{$files['name']} could not be uploaded");
            return false;
        }

        $this->fileInfo['name'] = $files['name'];
        $this->fileInfo['dir'] = $this->uploadDir;
        $this->fileInfo['path'] = $this->uploadDir . $files['name'];
        $this->fileInfo['full_path'] = $this->storageDir . $this->fileInfo['path'];
        $this->fileInfo['ext'] = $ext;
        $this->fileInfo['size'] = $files['size'];
        $this->fileInfo['type'] = $files['type'];
        $this->fileInfo['hash'] = sha1_file($this->fileInfo['full_path']);

        return true;
    }

    /**
     * @param $name
     * @return bool
     */
    public function uploadMulti($name): bool
    {

        if ($this->hasError($name)) return false;

        $files = $this->files[$name];
        $count = count($files['name']) - 1;

        $uploaded = [];

        for ($current = 0; $current <= $count; $current++) {

            $this->validateMulti($name, $current);

            if ($this->hasError($name)) {
                $this->unlinkMulti($uploaded);
                return false;
            }

            $ext = pathinfo($files['name'][$current], PATHINFO_EXTENSION);
            $ext = strtolower($ext);

            if ($this->getFileName() !== null)
                $files['name'][$current] = $this->getFileName();

            if ($this->formatName) $files['name'][$current] = $this->formatName($files['name'][$current]);

            $newName = $files['name'][$current];

            if ($this->uploadOverwrite) {
                if (is_file($this->storageDir . $this->uploadDir . $newName))
                    unlink($this->storageDir . $this->uploadDir . $newName);
            }

            $x = 0;
            while (!$this->uploadOverwrite && is_file($this->storageDir . $this->uploadDir . $newName)) {
                $newName = basename($files['name'][$current], ".{$ext}") . "_{$x}.{$ext}";
                $x++;

                if ($x > MAX_FILE) {
                    $this->addError($name, "The system can't accept more files from you. You have uploaded " . MAX_FILE . " files already");
                    $this->unlinkMulti($uploaded);
                    return false;
                }
            }

            $files['name'][$current] = $newName;
            
            if (!move_uploaded_file($files['tmp_name'][$current], $this->storageDir . $this->uploadDir . $newName)) {
                $this->addError($name, "{$files['name'][$current]} could not be uploaded");
                $this->unlinkMulti($uploaded);
                return false;
            }

            $uploaded[] = $files['name'][$current];
            $this->fileInfo[$current]['name'] = $files['name'][$current];
            $this->fileInfo[$current]['dir'] = $this->uploadDir;
            $this->fileInfo[$current]['path'] = $this->uploadDir . $files['name'];
            $this->fileInfo[$current]['full_path'] = $this->storageDir . $this->fileInfo[$current]['path'];
            $this->fileInfo[$current]['ext'] = $ext;
            $this->fileInfo[$current]['size'] = $files['size'][$current];
            $this->fileInfo[$current]['type'] = $files['type'][$current];
            $this->fileInfo[$current]['hash'] = sha1_file($this->fileInfo[$current]['full_path']);
        }

        return true;
    }

    /**
     * @param string $fileName
     * @return bool
     */
    public function unlink(string $fileName) {
        return unlink($this->storageDir . $this->uploadDir . DIRECTORY_SEPARATOR . $fileName);
    }

    /**
     * @param array $fileNames
     * @return array
     */
    public function unlinkMulti(array $fileNames) {
        $status = [];
        foreach ($fileNames as $fileName)
            $status[] = unlink($this->storageDir . $this->uploadDir . DIRECTORY_SEPARATOR . $fileName);
        return $status;
    }

    /**
     * @param $name
     * @return bool|resource
     * @throws QueException
     */
    public function readFile($name) {

        if (!$this->files->_isset($name))
            throw new QueException("No file was uploaded with the name '{$name}'", "Read File");

        $files = $this->files[$name];

        if (!isset($files['name']))
            throw new QueException("Can't find uploaded file", "Read File");

        if ($error = $this->checkUploadError($files['error']))
            throw new QueException($error, "Read File");

        if ($files['size'] <= 0)
            throw new QueException("{$files['name']} is empty", "Read File");

        return fopen($files['tmp_name'], "r");
    }

    /**
     * @param $name
     * @return array
     * @throws QueException
     */
    public function readFileMulti($name) {

        if (!$this->files->_isset($name))
            throw new QueException("No file was uploaded with the name {$name}", "Read File");

        $files = $this->files[$name];
        $count = count($files['name']) - 1;

        $list = [];

        for ($current = 0; $current <= $count; $current++) {

            if (!isset($files['name'][$current]))
                throw new QueException("Can't find uploaded file", "Read File");

            if ($error = $this->checkUploadError($files['error'][$current]))
                throw new QueException($error, "Read File");

            if ($files['size'][$current] <= 0)
                throw new QueException("{$files['name'][$current]} is empty");

            $list[$current] = fopen($files['tmp_name'][$current], "r");
        }

        return $list;
    }

    /**
     * @param $resource
     * @return bool
     */
    public function closeFile($resource): bool {
        return fclose($resource);
    }

    /**
     * @param $name
     * @return mixed
     * @throws QueException
     */
    public function getTmpName($name) {

        if (!$this->files->_isset($name))
            throw new QueException("No file was uploaded with the name '{$name}'", "Read File");

        $files = $this->files[$name];

        if ($error = $this->checkUploadError($files['error']))
            throw new QueException($error, "Read File");

        if ($files['size'] <= 0)
            throw new QueException("{$files['name']} is empty", "Read File");

        return $files['tmp_name'];
    }

    /**
     * @param $name
     * @return array
     * @throws QueException
     */
    public function getTmpNameMulti($name) {

        if (!$this->files->_isset($name))
            throw new QueException("No file was uploaded with the name {$name}", "Read File");

        $files = $this->files[$name];
        $count = count($files['name']) - 1;

        $list = [];

        for ($current = 0; $current <= $count; $current++) {

            if ($error = $this->checkUploadError($files['error'][$current]))
                throw new QueException($error, "Read File");

            if ($files['size'][$current] <= 0)
                throw new QueException("{$files['name'][$current]} is empty");

            $list[$current] = $files['tmp_name'][$current];
        }

        return $list;
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