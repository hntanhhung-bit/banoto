<?php

namespace App\Mail\Transports;

use Symfony\Component\Mailer\SentMessage;
use Symfony\Component\Mailer\Transport\AbstractTransport;
use Symfony\Component\Mime\MessageConverter;

class MailtrapTransport extends AbstractTransport
{
    protected string $apiToken;
    protected string $inboxId;

    public function __construct(string $apiToken, string $inboxId)
    {
        parent::__construct();
        $this->apiToken = $apiToken;
        $this->inboxId = $inboxId;
    }

    protected function doSend(SentMessage $message): void
    {
        $email = MessageConverter::toEmail($message->getOriginalMessage());

        $toList = [];
        foreach ($email->getTo() as $address) {
            $toList[] = [
                'email' => $address->getAddress(),
                'name' => $address->getName() ?: $address->getAddress(),
            ];
        }

        $fromAddress = $email->getFrom()[0] ?? null;
        $from = [
            'email' => $fromAddress ? $fromAddress->getAddress() : 'noreply@banoto.com',
            'name' => $fromAddress ? ($fromAddress->getName() ?: 'Oto.com.vn System') : 'Oto.com.vn System',
        ];

        $payload = [
            'to' => $toList,
            'from' => $from,
            'subject' => $email->getSubject() ?: 'Thông báo từ Oto.com.vn',
            'html' => $email->getHtmlBody() ?: $email->getTextBody(),
            'text' => $email->getTextBody() ?: strip_tags((string) $email->getHtmlBody()),
        ];

        $ch = curl_init("https://sandbox.api.mailtrap.io/api/send/{$this->inboxId}");
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Api-Token: {$this->apiToken}",
            "Content-Type: application/json"
        ]);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 15);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($httpCode >= 400 || $response === false) {
            throw new \RuntimeException("Lỗi gửi Mailtrap Sandbox: [HTTP {$httpCode}] {$response} {$error}");
        }
    }

    public function __toString(): string
    {
        return 'mailtrap';
    }
}
