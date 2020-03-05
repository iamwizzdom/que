<?php

namespace que\mail;

use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;
use que\common\exception\QueException;
use que\http\Http;
use que\template\Composer;

class Mailer
{

    const COMPOSE_USING_SMARTY = 0; //compose mail template using smarty templating engine
    const COMPOSE_USING_TWIG = 1; //compose mail template using twig templating engine

    /**
     *
     * @var PHPMailer
     */
    private $mail;

    /**
     * @var Composer
     */
    protected $composer;

    /**
     * @var array
     */
    private $mailQueue = [];

    /**
     * @var array
     */
    protected $error = [];
    /**
     * @var Http
     */
    private static $instance;

    /**
     * Mailer constructor.
     * @throws QueException
     */
    protected function __construct()
    {
        try {

            $mail = new PHPMailer();

            $mail->IsSMTP();

            // Set mailer
            $mail->XMailer = "Que Mailer 1.0 (https://www.quidvis.com/que)";

            // Set Debug options
            $mail->SMTPDebug = APP_SMTP_DEBUG;

            // Check for remote connections
            if (APP_SMTP_REMOTE) {
                $mail->SMTPSecure = APP_SMTP_TRANSPORT; // secure transfer enabled REQUIRED
            }

            // Host & Port
            $mail->Host = APP_SMTP_HOST; // sets the SMTP server
            $mail->Port = APP_SMTP_PORT; // set the SMTP port for the GMAIL

            // server
            $mail->Username = APP_SMTP_USER; // SMTP account username
            $mail->Password = APP_SMTP_PASS; // SMTP account password
            $mail->SMTPOptions = APP_SMTP_OPTIONS; // SMTP Options
            $mail->SMTPAuth = APP_SMTP_AUTH; // enable SMTP authentication

            // Set Mail
            $this->mail = $mail;

            // Default Config
            $this->mail->AddReplyTo(APP_EMAIL_REPLY, APP_NAME);

            $this->composer = Composer::getInstance();

        } catch (\Exception $exception) {

            throw new QueException($exception->getMessage(), "Mailer error");
        }

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
     * @return Mailer
     * @throws QueException
     */
    public static function getInstance(): Mailer
    {
        if (!isset(self::$instance))
            self::$instance = new self();
        return self::$instance;
    }

    /**
     * @param Mail $mail
     */
    public function addMail(Mail $mail) {
        $this->mailQueue[$mail->getKey()] = $mail;
    }

    /**
     * @param string $key
     * @param int $compose_using
     * @throws QueException
     */
    public function prepare(string $key, int $compose_using = self::COMPOSE_USING_SMARTY) {

        if (!array_key_exists($key, $this->mailQueue))
            throw new QueException("Undefined key: '{$key}' not found in mailer queue", "Mailer error");

        $mail = &$this->mailQueue[$key];

        if (!$mail instanceof Mail)
            throw new QueException("Data found in mailer queue with key '{$key}' seems not to be a valid mail", "Mailer error");

        if (!$this->composer instanceof Composer)
            throw new QueException("Composer not set. Mail cannot be prepared without composer.", "Mailer error");

        $mail->AltBody = $this->parse($mail->getBodyPath(), $mail->getData(), $compose_using);
        $mail->MsgHTML = $this->parse($mail->getHtmlPath(), $mail->getData(), $compose_using);

        $this->composer->_flush();

    }

    /**
     * @param string $key
     * @return bool
     * @throws QueException
     */
    public function dispatch(string $key) {

        if (!array_key_exists($key, $this->mailQueue))
            throw new QueException("Undefined key: '{$key}' not found in mailer queue", "Mailer error");

        $mail = &$this->mailQueue[$key];

        if (!$mail instanceof Mail)
            throw new QueException("Data found in mailer queue with key '{$key}' seems not to be a valid mail", "Mailer error");

        if (!(isset($mail->AltBody) && !empty($mail->AltBody)) || !(isset($mail->MsgHTML) && !empty($mail->MsgHTML)))
            throw new QueException("Mail found in mailer queue with key '{$key}' seems not to be prepared. You can't dispatch an unprepared mail.", "Mailer error");

        $this->mail->Subject = $mail->getSubject();

        try {
            $from = $mail->getFrom();
            $this->mail->setFrom($from['email'] ?: APP_EMAIL_DEFAULT, $from['name'] ?: APP_NAME);
        } catch (Exception $e) {
            throw new QueException($e->getMessage(), "Mailer error");
        }

        foreach ($mail->getRecipient() as $recipient) {
            $this->mail->addAddress($recipient['email'], $recipient['name']);
        }

        if (!empty($replyTos = $mail->getReplyTo())) {
            $this->mail->clearReplyTos();
            foreach ($replyTos as $replyTo) {
                $this->mail->addReplyTo($replyTo['email'], $replyTo['name']);
            }
        }

        foreach ($mail->getCc() as $cc) {
            if (!isset($cc['email']) || !is_email($cc['email'])) continue;
            $this->mail->addCC($cc['email'], $cc['name']);
        }

        foreach ($mail->getBcc() as $bcc) {
            if (!isset($bcc['email']) || !is_email($bcc['email'])) continue;
            $this->mail->addBCC($bcc['email'], $bcc['name']);
        }

        $this->mail->AltBody = $mail->AltBody;
        $this->mail->MsgHTML($mail->MsgHTML);

        try {

            foreach ($mail->getAttachment() as $attachment) {
                $this->mail->addAttachment($attachment['path'], $attachment['name'],
                    $attachment['encoding'], $attachment['type'], $attachment['disposition']);
            }

            foreach ($mail->getStringAttachment() as $attachment) {
                $this->mail->addStringAttachment($attachment['data'], $attachment['name'],
                    $attachment['encoding'], $attachment['type'], $attachment['disposition']);
            }

            $status = (bool) $this->mail->Send();

            $mail->setError($this->mail->ErrorInfo);
            $this->error[$mail->getKey()] = $mail->getError();

            $this->__flush();

            return $status;

        } catch (Exception $exception) {

            throw new QueException($exception->getMessage(), "Mailer error");

        }

    }

    /**
     * @param string $path
     * @param array $data
     * @param int $compose_using
     * @return false|string
     * @throws QueException
     */
    protected function parse(string $path, array $data, int $compose_using)
    {
        return $this->render($path, $data, $compose_using);
    }

    /**
     * @param string $file
     * @param array $data
     * @param int $compose_using
     * @throws QueException
     */
    protected function display(string $file, array $data, int $compose_using)
    {
        $this->composer->dataOverwrite($data);
        $this->composer->setTmpFileName($file);
        $this->composer->prepare();
        if ($compose_using == self::COMPOSE_USING_SMARTY)
            $this->composer->renderWithSmarty();
        elseif ($compose_using == self::COMPOSE_USING_TWIG)
            $this->composer->renderWithTwig();
        else throw new QueException(
            "Trying to compose mail template using an unsupported templating engine type", "Mailer error");
    }

    /**
     * @param string $file
     * @param array $data
     * @param int $compose_using
     * @return false|string
     * @throws QueException
     */
    protected function render(string $file, array $data, int $compose_using)
    {
        ob_start();
        $this->display($file, $data, $compose_using);
        $content = ob_get_contents();
        if (ob_get_length()) {
            ob_end_clean();
        }
        return $content;
    }

    /**
     * @param string $key
     * @return mixed|null
     */
    public function getError(string $key)
    {
        return $this->error[$key] ?? null;
    }

    /**
     * @param int $no
     * @return string
     */
    protected function line($no = 1)
    {
        $line = "";
        for ($i = 0; $i < $no; $i++) {
            $line = "\t\n";
        }
        return $line;
    }

    /**
     * @param int $no
     * @return string
     */
    protected function tab($no = 1): string
    {
        $line = "";
        for ($i = 0; $i < $no; $i++)
            $line = "\t";
        return $line;
    }

    /**
     * @param string $key
     */
    public function __unsetError(string $key) {
        unset($this->error[$key]);
    }

    /**
     * @param string $key
     */
    public function __unsetMail(string $key) {
        unset($this->mailQueue[$key]);
    }

    /**
     * flush Mailer
     */
    protected function __flush() {
        $this->mail->clearReplyTos();
        $this->mail->clearAddresses();
        $this->mail->clearAllRecipients();
        $this->mail->clearAttachments();
        $this->mail->clearCCs();
        $this->mail->clearBCCs();
        $this->mail->clearCustomHeaders();
    }
}

