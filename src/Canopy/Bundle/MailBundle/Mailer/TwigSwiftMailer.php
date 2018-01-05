<?php

namespace Canopy\Bundle\MailBundle\Mailer;

use Psr\Log\LoggerInterface;

class TwigSwiftMailer
{
    /**
     * @var \Swift_Mailer
     */
    protected $mailer;

    /**
     * @var \Twig_Environment
     */
    protected $twig;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    public function __construct(\Swift_Mailer $mailer, \Twig_Environment $twig, LoggerInterface $logger)
    {
        $this->mailer = $mailer;
        $this->twig = $twig;
        $this->logger = $logger;
    }

    public function sendEmail($templateName, $context, $fromEmail, $toEmail, array $attachments = [], $bcc = null)
    {
        $context = $this->twig->mergeGlobals($context);
        $template = $this->twig->loadTemplate($templateName);

        $subject = $template->renderBlock('subject', $context);
        $textBody = $template->renderBlock('body_text', $context);
        $htmlBody = $template->renderBlock('body_html', $context);

        $message = \Swift_Message::newInstance()
            ->setSubject($subject)
            ->setFrom($fromEmail)
            ->setTo($toEmail);

        if (null !== $bcc) {
            $message->setBcc($bcc);
        }

        foreach ($attachments as $attachment) {
            $message->attach(
                \Swift_Attachment::newInstance($attachment['file'], $attachment['name'], $attachment['content_type'])
            );
        }

        if (!empty($htmlBody)) {
            $message->setBody($htmlBody, 'text/html')
                ->addPart($textBody, 'text/plain');
        } else {
            $message->setBody($textBody);
        }

        $this->logger->notice(sprintf('Send Email %s', $subject));

        try {
            return $this->mailer->send($message);
        } catch (\Swift_TransportException $e) {
            $this->logger->error(sprintf('Error while sending mail "%s"', $e->getMessage()));
            throw $e;
        }
    }
}
