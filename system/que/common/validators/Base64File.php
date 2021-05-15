<?php
/**
 * Created by PhpStorm.
 * User: Wisdom Emenike
 * Date: 12/31/2018
 * Time: 10:31 PM
 */

namespace que\common\validator;


use que\common\exception\QueException;

class Base64File extends FileBase
{
    /**
     * @var Base64File
     */
    private static Base64File $instance;

    protected function __construct()
    {
    }

    private function __clone()
    {
        // TODO: Implement __clone() method.
    }

    public function __wakeup()
    {
        // TODO: Implement __wakeup() method.
    }

    /**
     * @return Base64File
     */
    public static function getInstance(): Base64File
    {
        if (!isset(self::$instance))
            self::$instance = new self;
        return self::$instance;
    }

    /**
     * @param string $name
     * @param $data
     * @return array
     */
    public function parse(string $name, $data)
    {
        if (preg_match('/data:([^;]*);base64,(.*)/', $data, $matches)) {
            $data = $matches[2];
        }

        $tmpName = tempnam(sys_get_temp_dir(), sha1($name));

        file_put_contents($tmpName, base64_decode($data));

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $finfo_file = finfo_file($finfo, $tmpName);
        finfo_close($finfo);

        $finfo_file = explode("/", $finfo_file);
        $finfo_file[0] = ($finfo_file[0] ?? 'unknown');
        $finfo_file[1] = ($finfo_file[1] ?? 'unknown');

        return [
            "type" => $finfo_file[0],
            "size" => filesize($tmpName) ?: 0,
            "name" => "{$name}.{$finfo_file[1]}",
            "tmp_name" => $tmpName,
            "ext" => $finfo_file[1]
        ];
    }

    /**
     * @param string $name
     * @param $data
     * @return array|bool
     */
    public function validate(string $name, $data) {

        $uploadDir = "{$this->storageDir}{$this->uploadDir}";

        try {

            if (!is_dir($uploadDir) && !mkdir($uploadDir, 0777, true))
                throw new QueException("Directory [" . str_start_from($uploadDir, 'storage/') . "] does not exist");

            if (!$this->checkDir($uploadDir)) throw new QueException("Directory [" . str_start_from($uploadDir, 'storage/') . "] is not writable");

            if (!($files = $this->parse($this->getFileName() ?: $name, $data))) throw new QueException("Can't parse file. Invalid / Wrongly formatted base64 file");

            if ($files['size'] <= 0)
                throw new QueException("{$files['name']} is empty");

            if ($this->uploadMax)
                if ($files['size'] >= $this->uploadMax)
                    throw new QueException(sprintf("%s has a file size of %s which exceeds the system's maximum file upload size of %s", $files['name'],
                        convert_bytes($files['size'], 2), convert_bytes($this->uploadMax, 2)));

            if (!empty($this->allowedExtensions)) {
                $ext = $files['ext'];
                if (!in_array($ext, $this->allowedExtensions))
                    throw new QueException("{$files['name']} -- file extension '{$ext}' is not supported");
            }

            if (!empty($this->allowedMimeType)) {
                $fileType = $files['type'];
                if (!in_array($fileType, $this->allowedMimeType))
                    throw new QueException("{$files['name']} -- file type '{$fileType}' is not supported");
            }

            return $files;

        } catch (QueException $e) {

            $this->addError($name, $e->getMessage());
        }

        return false;

    }

    /**
     * @param string $name
     * @param $data
     * @return bool
     */
    public function upload(string $name, $data = null)
    {
        if (is_null($data)) $data = input($name);

        if ($this->hasError($name)) return false;

        $files = $this->validate($name, $data);

        if ($this->hasError($name) || !$files) return false;

        if ($this->getFileName() !== null)
            $files['name'] = "{$this->getFileName()}.{$files['ext']}";

        if ($this->formatName)
            $files['name'] = $this->formatName($files['name']);

        $x = 0;

        $newName = $files['name'];

        if ($this->uploadOverwrite)
            if (is_file($this->storageDir . $this->uploadDir . $newName))
                unlink($this->storageDir . $this->uploadDir . $newName);

        while (!$this->uploadOverwrite && file_exists($this->storageDir . $this->uploadDir . $newName)) {
            $newName = basename($files['name'], ".{$files['ext']}") . "_{$x}.{$files['ext']}";
            $x++;

            if ($x > MAX_FILE) {
                $this->addError($name, "The system can't accept more files from you. You have uploaded " . MAX_FILE . " files already");
                return false;
            }
        }

        $files['name'] = $newName;

        if (!copy($files['tmp_name'], $this->storageDir . $this->uploadDir . $files['name'])) {
            $this->addError($name, "{$files['name']} could not be uploaded");
            return false;
        }

        unlink($files['tmp_name']);

        $this->fileInfo['name'] = $files['name'];
        $this->fileInfo['dir'] = self::ROOT_DIR . $this->uploadDir;
        $this->fileInfo['path'] = self::ROOT_DIR . $this->uploadDir . $files['name'];
        $this->fileInfo['full_path'] = $this->storageDir . $this->uploadDir . $files['name'];
        $this->fileInfo['url'] = base_url($this->fileInfo['path']);
        $this->fileInfo['ext'] = $files['ext'];
        $this->fileInfo['size'] = $files['size'];
        $this->fileInfo['type'] = $files['type'];
        $this->fileInfo['hash'] = sha1_file($this->fileInfo['full_path']);

        return true;
    }
}