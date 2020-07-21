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
    protected Composer $composer;

    /**
     * @var array
     */
    private array $mailQueue = [];

    /**
     * @var array
     */
    protected array $error = [];
    /**
     * @var Mailer
     */
    private static ?Mailer $instance = null;

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
            $mail->SMTPDebug = config('mail.smtp.debug', false);

            // Check for remote connections
            if (config('mail.smtp.remote', false)) {
                $mail->SMTPSecure = config('mail.smtp.transport', 'ssl'); // secure transfer enabled REQUIRED
            }

            // Host & Port
            $mail->Host = config('mail.smtp.host', ''); // sets the SMTP server
            $mail->Port = config('mail.smtp.port', '465'); // set the SMTP port for the GMAIL

            // server
            $mail->Username = config('mail.smtp.username', ''); // SMTP account username
            $mail->Password = config('mail.smtp.password', ''); // SMTP account password
            $mail->SMTPOptions = config('mail.smtp.options', []); // SMTP Options
            $mail->SMTPAuth = config('mail.smtp.auth', false); // enable SMTP authentication
            $mail->Timeout = config('mail.smtp.timeout', 300); // enable SMTP authentication

            // Set Mail
            $this->mail = $mail;

            // Default Config
            $this->mail->AddReplyTo(config('mail.address.reply', ''), config('template.app.header.name'));

            $this->composer = Composer::getInstance(false);

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
    public function addMail(Mail $mail)
    {
        $this->mailQueue[$mail->getKey()] = $mail;
    }

    /**
     * @param string $key
     * @param int $compose_using
     * @throws QueException
     */
    public function prepare(string $key, int $compose_using = self::COMPOSE_USING_SMARTY)
    {

        if (!array_key_exists($key, $this->mailQueue))
            throw new QueException("Undefined key: '{$key}' not found in mailer queue", "Mailer error");

        $mail = &$this->mailQueue[$key];

        if (!$mail instanceof Mail)
            throw new QueException("Data found in mailer queue with key '{$key}' seems not to be a valid mail", "Mailer error");

        if (!$this->composer instanceof Composer)
            throw new QueException("Composer not set. Mail cannot be prepared without composer.", "Mailer error");

        $mail->AltBody = $this->parse($mail->getBodyPath(), $mail->getData(), $compose_using);
        $mail->MsgHTML = $this->parse($mail->getHtmlPath(), $mail->getData(), $compose_using);

    }

    /**
     * @param string $key
     * @return bool
     * @throws QueException
     */
    public function dispatch(string $key)
    {

        if (!array_key_exists($key, $this->mailQueue))
            throw new QueException("Undefined key: '{$key}' not found in mailer queue", "Mailer error");

        $mail = &$this->mailQueue[$key];

        if (!$mail instanceof Mail)
            throw new QueException("Data found in mailer queue with key '{$key}' seems not to be a valid mail", "Mailer error");

        if (!(isset($mail->AltBody) && !empty($mail->AltBody)) || !(isset($mail->MsgHTML) && !empty($mail->MsgHTML)))
            throw new QueException("Mail found in mailer queue with key '{$key}' seems not to be prepared. You can't dispatch an unprepared mail.", "Mailer error");

        try {

            $this->mail->Subject = $mail->getSubject();

            $from = $mail->getFrom();
            $this->mail->setFrom($from['email'] ?? config('mail.address.default', ''), $from['name'] ?? config('template.app.header.name'));

            foreach ($mail->getRecipient() as $recipient) {
                if (!isset($recipient['email']) || !is_email($recipient['email'])) continue;
                $this->mail->addAddress($recipient['email'], $recipient['name']);
            }

            foreach ($mail->getReplyTo() as $replyTo) {
                if (!isset($replyTo['email']) || !is_email($replyTo['email'])) continue;
                $this->mail->addReplyTo($replyTo['email'], $replyTo['name']);
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
     * @return false|string
     * @throws QueException
     */
    protected function render(string $file, array $data, int $compose_using)
    {
        $this->composer->dataOverwrite($data);
        $this->composer->setTmpFileName($file);
        $this->composer->prepare();
        if ($compose_using == self::COMPOSE_USING_SMARTY)
            return $this->composer->renderWithSmarty(true);
        elseif ($compose_using == self::COMPOSE_USING_TWIG)
            return $this->composer->renderWithTwig(true);
        else throw new QueException("Trying to compose mail template using an unsupported templating engine type", "Mailer error");
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
    public function __unsetError(string $key)
    {
        unset($this->error[$key]);
    }

    /**
     * @param string $key
     */
    public function __unsetMail(string $key)
    {
        unset($this->mailQueue[$key]);
    }

    /**
     * flush Mailer
     */
    protected function __flush()
    {
        $this->mail->clearReplyTos();
        $this->mail->clearAddresses();
        $this->mail->clearAllRecipients();
        $this->mail->clearAttachments();
        $this->mail->clearCCs();
        $this->mail->clearBCCs();
        $this->mail->clearCustomHeaders();
    }
}

