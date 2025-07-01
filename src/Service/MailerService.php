<?php
//service chargé d'envoyer les mails
namespace App\Service;

use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;

class MailerService
{
    private $mailer;

    public function __construct(MailerInterface $mailer)
    {
        $this->mailer = $mailer;
    }

    public function sendEmailToOwner($name, $firstName, $contactEmail, $phone, $message)
    {
        //$text = 'Formulaire de contact de ' . $name . ' ' . $firstName . ' (' . $contactEmail . ') : ' . $phone . ' : ' . $message;
        $email = (new TemplatedEmail())
            ->from('noreply.sushi.dot.painting@free.fr')
            ->to('crouick@gmail.com')
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
            ->from('noreply.sushi.dot.painting@free.fr')
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