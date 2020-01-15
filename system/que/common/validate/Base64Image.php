<?php
/**
 * Created by PhpStorm.
 * User: Wisdom Emenike
 * Date: 12/31/2018
 * Time: 10:31 PM
 */

namespace que\common\validate;


use que\common\exception\QueException;
use const MAX_FILE;

class Base64Image extends FileBase
{
    /**
     * @var Base64Image
     */
    private static $instance;

    protected function __construct()
    {
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
     * @return Base64Image
     */
    public static function getInstance(): Base64Image
    {
        if (!isset(self::$instance))
            self::$instance = new self;
        return self::$instance;
    }

    /**
     * @param $data
     * @return array|bool
     * @throws QueException
     */
    public function parse($data)
    {

        if (!preg_match('/data:([^;]*);base64,(.*)/', $data, $matches)) return false;

        $knownImages = [
            'image/png' => 'png',
            'image/jpeg' => 'jpeg',
            'image/jpg' => 'jpg',
            'image/gif' => 'gif'
        ];

        if (!isset($knownImages[$matches[1]]))
            throw new QueException("Unknown Data Type", "File Upload");

        return [
            "type" => $matches[1],
            "base64" => $matches[2],
            "size" => strlen($matches[2]),
            "name" => sha1($matches[2]) . "." . $knownImages[$matches[1]],
            "ext" => $knownImages[$matches[1]]
        ];
    }

    /**
     * @param $data
     * @throws QueException
     */
    public function upload($data)
    {

        $this->uploadMultiple = false;

        if (empty($this->uploadDir))
            $this->uploadDir = dirname(__FILE__) . '/';

        if (!$this->checkDir($this->uploadDir)) {
            throw new QueException("Directory [{$this->uploadDir}] is not writable", "File Upload");
        }

        try {

            $files = $this->parse($data);

            if (!$files)
                throw new QueException("Can't parse file upload", "File Upload");

            if ($files['size'] <= 0)
                throw new QueException($files['name'] . ' is empty', "File Upload");

            if ($this->uploadMax)
                if ($files['size'] >= $this->uploadMax)
                    throw new QueException(sprintf("%s has a file size of %s which exceeds %s's maximum file upload size of %s", $files['name'],
                        convert_bytes($files['size'], 2), APP_NAME, convert_bytes($this->uploadMax, 2)), "File Upload");

            // Saving Typing
            $ext = $files['ext'];

            if (!empty($this->allowedExtensions))
                if (!in_array($ext, $this->allowedExtensions))
                    throw new QueException($files['name'] . " -- file extension '$ext' is not supported", "File Upload");

            $fileType = $files['type'];

            if (!empty($this->allowedMimeType))
                if (!in_array($fileType, $this->allowedMimeType))
                    throw new QueException($files['name'] . " -- file type '$fileType' is not supported", "File Upload");

            if ($this->getFileName() !== null)
                $files['name'] = $this->getFileName() . ".$ext";

            if ($this->formatName)
                $files['name'] = $this->formatName($files['name']);

            $x = 0;

            $newName = $files['name'];

            if ($this->uploadOverwrite)
                if (file_exists($this->uploadDir . DIRECTORY_SEPARATOR . $newName))
                    unlink($this->uploadDir . DIRECTORY_SEPARATOR . $newName);

            while (!$this->uploadOverwrite && file_exists($this->uploadDir . DIRECTORY_SEPARATOR . $newName)) {
                $newName = basename($files['name'], "." . $ext) . "_" . $x . ".$ext";
                $x++;

                if ($x > MAX_FILE)
                    throw new QueException(APP_NAME .
                        " can't accept more files from you, you have " . MAX_FILE . " files already", "File Upload");
            }

            $files['name'] = $newName;

            switch ($ext) {
                case "png":
                    $image = imagecreatefrompng($data);
                    $background = imagecolorallocate($image, 0, 0, 0);
                    // removing the black from the placeholder
                    imagecolortransparent($image, $background);

                    // Blend Current Images
                    imagealphablending($image, false);

                    // turning on alpha channel information saving (to ensure the full range
                    // of transparency is preserved)
                    imagesavealpha($image, true);

                    break;

                case "gif":
                    $image = imagecreatefromgif($data);
                    $background = imagecolorallocate($image, 0, 0, 0);

                    // removing the black from the placeholder
                    imagecolortransparent($image, $background);

                    break;
                case "jpeg":
                    $image = imagecreatefromjpeg($data);

                    break;
                default:
                    $image = imagecreatefromstring(base64_decode($files['base64']));
                    break;
            }

            if (!imagepng($image, $this->uploadDir . DIRECTORY_SEPARATOR . $files['name']))
                throw new QueException($files['name'] . ' could not be uploaded', "File Upload");

            imagedestroy($image);

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
}