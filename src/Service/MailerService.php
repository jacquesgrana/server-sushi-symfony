<?php
//service chargé d'envoyer les mails
namespace App\Service;

use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

class MailerService
{
    private $mailer;

    public function __construct(MailerInterface $mailer)
    {
        $this->mailer = $mailer;
    }

    public function sendEmailToOwner($name, $firstName, $contactEmail, $phone, $message)
    {
        $text = 'Formulaire de contact de ' . $name . ' ' . $firstName . ' (' . $contactEmail . ') : ' . $phone . ' : ' . $message;
        $email = (new Email())
            ->from('noreply.sushi.dot.painting@free.fr')
            ->to('crouick@gmail.com')
            ->subject('formulaire de contact')
            ->text($text);

        $this->mailer->send($email);    

    }

    public function sendEmailToUser($name, $firstName, $contactEmail)
    {
        $text = 'Merci ' . $name . ' ' . $firstName . ' (' . $contactEmail . ') de nous avoir contacté. Nous vous recontacterons sous peu';
        $email = (new Email())
            ->from('noreply.sushi.dot.painting@free.fr')
            ->to($contactEmail)
            ->subject('merci de votre message')
            ->text($text);

        $this->mailer->send($email);    
    }
}