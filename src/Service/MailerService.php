<?php
//service chargé d'envoyer les mails
namespace App\Service;

use Symfony\Component\Mailer\MailerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;

class MailerService
{
    private $mailer;

    private string $from;
    private string $owner;
    private string $admin;
    //private string $admin = (isset($_ENV['EMAIL_ADMIN'])) ? $_ENV['EMAIL_ADMIN'] : '';

    public function __construct(MailerInterface $mailer)
    {
        $this->mailer = $mailer;
        $this->from = isset($_ENV['EMAIL_FROM']) ? $_ENV['EMAIL_FROM'] : '';
        $this->owner = isset($_ENV['EMAIL_OWNER']) ? $_ENV['EMAIL_OWNER'] : '';
        // idem pour l'admin
        $this->admin = isset($_ENV['EMAIL_ADMIN']) ? $_ENV['EMAIL_ADMIN'] : '';
    }

    public function sendEmailToOwner($name, $firstName, $contactEmail, $phone, $message, $toAdmin = false)
    {
        //$text = 'Formulaire de contact de ' . $name . ' ' . $firstName . ' (' . $contactEmail . ') : ' . $phone . ' : ' . $message;
        $targetEmail = ($toAdmin) ? $this->admin : $this->owner;
        $email = (new TemplatedEmail())
            ->from($this->from)
            ->to($targetEmail)
            ->subject('formulaire de contact')
            ->htmlTemplate('email/contact_owner.html.twig')
            ->context([
                'name' => $name,
                'firstName' => $firstName,
                'contactEmail' => $contactEmail,
                'phone' => $phone,
                'message' => $message,
            ]);

        $this->mailer->send($email);    

    }

    public function sendEmailToUser($name, $firstName, $contactEmail)
    {
        $text = 'Merci ' . $name . ' ' . $firstName . ' (' . $contactEmail . ') de nous avoir contacté. Nous vous recontacterons sous peu';
        $email = (new TemplatedEmail())
            ->from($this->from)
            ->to($contactEmail)
            ->subject('merci de votre message')
            ->htmlTemplate('email/contact_user_confirmation.html.twig')
            ->context([
                'name' => $name,
                'firstName' => $firstName,
            ]);

        $this->mailer->send($email);    
    }
}