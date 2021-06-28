<?php

use que\common\exception\BulkException;
use que\common\manager\Manager;
use que\common\structure\Page;
use que\common\structure\Receiver;
use que\database\interfaces\model\Model;
use que\http\input\Input;
use que\support\Str;
use que\template\Composer;
use que\utility\hash\Hash;

/**
 * Created by PhpStorm.
 * User: Wisdom Emenike
 * Date: 6/27/2020
 * Time: 11:26 PM
 */

class Upload extends Manager implements Page, Receiver
{

    /**
     * @inheritDoc
     */
    public function onLoad(Input $input): void
    {
        // TODO: Implement onLoad() method.
        current_route()->setTitle('Que File upload');
        $this->composer()->data([
            'title' => 'Que file upload demonstration'
        ]);
    }

    /**
     * @inheritDoc
     */
    public function setTemplate(Composer $composer): void
    {
        // TODO: Implement setTemplate() method.
        $composer->setTmpFileName('module/upload.tpl');
        $composer->prepare()->renderWithSmarty();
    }

    /**
     * @inheritDoc
     */
    public function onReceive(Input $input, ?Model $info = null): void
    {
        // TODO: Implement onReceive() method.

        $validate = $this->validator($input);

        try {

            if ($validate->track()->check()) {

                $this->composer()->alert(ALERT_WARNING, "Refresh Detected", "Last request was successful."
                )->button("Go back", route_uri('file-upload'));

                goto LOAD;
            }

            $validate->validate('filename', true)->isEmail("Please enter a valid file name");

            if (!$validate->hasFile('file')) {
                $validate->addConditionError('file', "Please select a file", true);
            }

            if ($validate->hasError()) {

                throw $this->bulkException("An error occurred while validating the inputted data, " .
                    "please fix the error(s) below and try again.", "Upload failed");
            }

            $file = $validate->validateFile();

            $file->setMaxFileSize(convert_mega_bytes(2));
            $file->setAllowedExtension(['doc', 'docx', 'pdf', 'png', 'jpeg', 'jpg', 'gif'], true);
            $file->setUploadDir('files');
            $file->setFileName(Hash::sha(Str::uuidv4(), "SHA1"));

            if (!$file->upload("file"))
                $validate->addConditionErrors('file', $file->getErrors('file'), true);

            if ($validate->hasError()) {

                if ($file->getFileInfo()?->getName()) $file->unlink($file->getFileInfo()->getName() ?? '');

                throw $this->bulkException("An error occurred while validating the inputted data, " .
                    "please fix the error(s) below and try again.", "Upload failed");
            }

            $validate->track()->set();

            $this->composer()->alert(ALERT_SUCCESS, "Upload successful", "Your file has been uploaded successfully");

        } catch (BulkException $exception) {
            $this->composer()->setFormError($validate->getErrors());
            $this->composer()->setFormStatus($validate->getStatuses());
            $this->composer()->alert($exception->getCode() ?: ALERT_ERROR, $exception->getTitle(),
                $exception->getMessage() ?: $exception->getMessageArray());
        }

        LOAD:

        $this->onLoad($input);
    }
}
