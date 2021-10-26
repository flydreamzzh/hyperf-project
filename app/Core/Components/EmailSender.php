<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://hyperf.wiki
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */
namespace App\Core\Components;

use App\Core\Traits\HyStaticInstance;
use PHPMailer\PHPMailer\PHPMailer;

/**
 * 邮件发送
 * Class EmailSender.
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
        $this->mailer->IsSMTP(); //设定使用SMTP服务
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
     * @throws \PHPMailer\PHPMailer\Exception
     * @return $this
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
     * @throws \PHPMailer\PHPMailer\Exception
     * @return $this
     */
    public function setTo($addresses = [])
    {
        $addresses = is_array($addresses) ? $addresses : [$addresses];
        $this->mailer->clearAddresses();
        foreach ($addresses as $address) {
            $this->mailer->addAddress($address);
        }
        return $this;
    }

    /**
     * 同步发送
     * 调用这个方法不会有协程异步的特性.
     * @return mixed
     */
    public function syncSend()
    {
        $channel = new \Swoole\Coroutine\Channel();
        co(function () use ($channel) {
            $result = $this->mailer->Send();
            $channel->push($result);
        });
        return $channel->pop();
    }

    /**
     * @throws \PHPMailer\PHPMailer\Exception
     * @return bool
     */
    public function send()
    {
        return $this->mailer->Send();
    }
}
