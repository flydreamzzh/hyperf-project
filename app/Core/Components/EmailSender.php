<?php


namespace App\Core\Components;

use App\Core\Traits\HyStaticInstance;
use PHPMailer\PHPMailer\PHPMailer;

/**
 * 邮件发送
 * Class EmailSender
 * @package App\Core\Components
 */
class EmailSender
{
    use HyStaticInstance;

    /**
     * @var PHPMailer
     */
    private $mailer;

    /**
     * EmailSender constructor.
     * @throws \PHPMailer\PHPMailer\Exception
     */
    public function __construct()
    {
        $this->mailer = new PHPMailer();
        $this->mailer->CharSet = config('mailer.messageConfig.charset');
        $this->mailer->IsSMTP();//设定使用SMTP服务
        $this->mailer->SMTPDebug = 0;
        $this->mailer->SMTPAuth = true;
        $this->mailer->SMTPSecure = config('mailer.transport.encryption');
        $this->mailer->Host = config('mailer.transport.host');
        $this->mailer->Port = config('mailer.transport.port');
        $this->mailer->Username = config('mailer.transport.username');
        $this->mailer->Password = config('mailer.transport.password');
        $data = config('mailer.messageConfig.from');
        $from = array_key_first($data);
        $name = $data[$from];
        $this->mailer->setFrom($from, $name);
    }

    /**
     * @param $subject
     * @return $this
     */
    public function setSubject($subject)
    {
        $this->mailer->Subject = $subject;
        return $this;
    }

    /**
     * @param $context
     * @return $this
     * @throws \PHPMailer\PHPMailer\Exception
     */
    public function setTextBody($context)
    {
        $this->mailer->isHTML(false);
        $this->mailer->Body = $context;
        $this->mailer->WordWrap = true;
        return $this;
    }

    /**
     * @param array $addresses
     * @return $this
     * @throws \PHPMailer\PHPMailer\Exception
     */
    public function setTo($addresses = [])
    {
        $this->mailer->clearAddresses();
        foreach ($addresses as $address) {
            $this->mailer->addAddress($address);
        }
        return $this;
    }

    /**
     * @return mixed
     */
    public function send()
    {
        $channel = new \Swoole\Coroutine\Channel();
        co(function () use ($channel) {
            $result = $this->mailer->Send();
            $channel->push($result);
        });
        return $channel->pop();
    }
}