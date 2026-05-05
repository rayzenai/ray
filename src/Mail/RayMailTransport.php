<?php

namespace RayzenAI\Ray\Mail;

use RayzenAI\Ray\RayDebugService;
use Symfony\Component\Mailer\SentMessage;
use Symfony\Component\Mailer\Transport\AbstractTransport;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mime\Header\HeaderInterface;
use Symfony\Component\Mime\MessageConverter;

class RayMailTransport extends AbstractTransport
{
    public function __construct(protected RayDebugService $ray)
    {
        parent::__construct();
    }

    protected function doSend(SentMessage $message): void
    {
        $original = $message->getOriginalMessage();

        try {
            $email = MessageConverter::toEmail($original);
        } catch (\Throwable) {
            $this->ray->storeWithColor(
                '(non-Email message)',
                ['raw' => $message->toString()],
                'green',
                null,
                null,
                'mail'
            );

            return;
        }

        $caller = $this->resolveCaller();

        $this->ray->storeWithColor(
            $email->getSubject() ?: '(no subject)',
            [
                'subject' => $email->getSubject(),
                'from' => $this->addressList($email->getFrom()),
                'to' => $this->addressList($email->getTo()),
                'cc' => $this->addressList($email->getCc()),
                'bcc' => $this->addressList($email->getBcc()),
                'reply_to' => $this->addressList($email->getReplyTo()),
                'html' => $email->getHtmlBody(),
                'text' => $email->getTextBody(),
                'attachments' => $this->attachmentList($email),
                'headers' => $this->headerList($email),
            ],
            'green',
            $caller['file'],
            $caller['line'],
            'mail'
        );
    }

    public function __toString(): string
    {
        return 'ray-mail';
    }

    /**
     * @param  array<int, Address>  $addresses
     * @return array<int, string>
     */
    protected function addressList(array $addresses): array
    {
        return array_map(fn (Address $address) => $address->toString(), $addresses);
    }

    /**
     * @return array<int, array{filename: ?string, content_type: string, size: int}>
     */
    protected function attachmentList(Email $email): array
    {
        return array_map(fn ($part) => [
            'filename' => $part->getFilename(),
            'content_type' => $part->getContentType(),
            'size' => strlen($part->getBody()),
        ], $email->getAttachments());
    }

    /**
     * @return array<string, string>
     */
    protected function headerList(Email $email): array
    {
        $headers = [];

        foreach ($email->getHeaders()->all() as $header) {
            /** @var HeaderInterface $header */
            $name = $header->getName();

            if (in_array(strtolower($name), ['from', 'to', 'cc', 'bcc', 'reply-to', 'subject', 'date', 'message-id', 'mime-version', 'content-type', 'content-transfer-encoding'], true)) {
                continue;
            }

            $headers[$name] = $header->getBodyAsString();
        }

        return $headers;
    }

    /**
     * @return array{file: ?string, line: ?int}
     */
    protected function resolveCaller(): array
    {
        $frames = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 50);

        foreach ($frames as $frame) {
            $file = $frame['file'] ?? null;

            if ($file === null) {
                continue;
            }

            if (str_contains($file, '/vendor/') || str_contains($file, '/rayzenai/ray/')) {
                continue;
            }

            return ['file' => $file, 'line' => $frame['line'] ?? null];
        }

        return ['file' => null, 'line' => null];
    }
}
