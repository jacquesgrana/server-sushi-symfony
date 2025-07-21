<?php

namespace App\Controller;

use App\Entity\ContactForm;
use App\Entity\ContactFormProspect;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\ContactFormProspectRepository;

final class ContactFormProspectController extends AbstractController
{
    #[Route('/api/contact-form-prospect/get', name: 'app_contact_form_prospect_api_get', methods: ['GET'])]
    public function getContactFormProspects(
        ContactFormProspectRepository $contactFormProspectRepository
    ): JsonResponse
    {
        $contactFormProspects = $contactFormProspectRepository->findAll();
        $data = [];
        foreach ($contactFormProspects as $contactFormProspect) {
            $data[] = $contactFormProspect->normalize();
        }
        return $this->json([
            'success' => true,
            'message' => 'ContactFormProspect listed successfully',
            'data' => $data
        ], 200);
    }

    #[Route('/api/contact-form-prospect/create/{id}', name: 'app_contact_form_prospect_api_create', methods: ['POST'])]
    public function createProspectFromContactForm(
        ContactForm $contactForm,
        EntityManagerInterface $entityManager,
        ContactFormProspectRepository $contactFormProspectRepository
    ) {

        if ($contactForm->getContactFormProspect()) {
            return $this->json([
                'success' => false,
                'message' => 'ContactForm already has a Prospect',
                'data' => []
            ], 400);
        }

        // TODO vérifier si l'email n'est pas déjà utilisé par un autre Prospect
        //$prospects = $contactFormProspectRepository->findBy(['email' => $contactForm->getEmail()]);
            
        $oldProspect = $contactFormProspectRepository->findOneBy(['email' => $contactForm->getEmail()]);

        // si oui ajouter le ContactForm au Prospect
        if ($oldProspect) {
            $oldProspect->addContactForm($contactForm);
            //$entityManager->persist($oldProspect);
            $entityManager->flush();

            return $this->json([
                'success' => true,
                'message' => 'ContactForm added to Prospect successfully',
                'data' => $oldProspect->normalize()
            ], 200);
        }

        // sinon créer un nouveau Prospect
        else {
            $contactFormProspect = new ContactFormProspect();
            $contactFormProspect->setName($contactForm->getName());
            $contactFormProspect->setFirstName($contactForm->getFirstName());
            $contactFormProspect->setEmail($contactForm->getEmail());
            $contactFormProspect->setPhone($contactForm->getPhone());
            $contactFormProspect->setComment("Commentaire à modifier.");
            $contactFormProspect->setDate(new \DateTime());
            $contactFormProspect->addContactForm($contactForm);
            $entityManager->persist($contactFormProspect);
            $entityManager->flush();

            return $this->json([
                'success' => true,
                'message' => 'ContactFormProspect created successfully',
                'data' => $contactFormProspect->normalize()
            ], 200);
        }

        
        
    }
}