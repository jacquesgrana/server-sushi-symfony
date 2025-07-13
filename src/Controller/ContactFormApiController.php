<?php

namespace App\Controller;

use App\Repository\ContactFormProspectRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Service\MailerService;
use App\Entity\ContactFormProspect;

// TODO enlever 'api' de l'url ???!!!! et mofifier le parefeu et le front
final class ContactFormApiController extends AbstractController
{
    #[Route('/api/contact-form', name: 'app_contact_form_api', methods: ['POST', 'OPTIONS'])]
    public function manageContactForm(
        Request $request, 
        MailerService $mailerService,
        ContactFormProspectRepository $contactFormProspectRepository
        ): JsonResponse
    {
        // Gérer les requêtes OPTIONS (preflight)
        if ($request->getMethod() === 'OPTIONS') {
            return new JsonResponse(null, 200, [
                'Access-Control-Allow-Origin' => '^https://green-jackal-148000\.hostingersite\.com$',
                'Access-Control-Allow-Methods' => 'POST, OPTIONS',
                'Access-Control-Allow-Headers' => 'Content-Type, Authorization, X-Requested-With',
                'Access-Control-Max-Age' => '3600'
            ]);
        }

        // Récupérer les données JSON envoyées par React
        $data = json_decode($request->getContent(), true);

        // Vérifier que les données ont été reçues
        if (!$data) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Aucune donnée reçue',
                'data' => []
            ], 400);
        }

        // Vérifier les champs obligatoires (phone est facultatif)
        $requiredFields = ['email', 'message', 'name', 'firstName'];
        foreach ($requiredFields as $field) {
            if (!isset($data[$field]) || trim($data[$field]) === '') {
                return new JsonResponse([
                    'success' => false,
                    'message' => "Le champ $field est obligatoire",
                    'data' => []
                ], 400);
            }
        }

        // créer entités
        $contactFormprospect = new ContactFormProspect();
        $contactFormprospect->setName($data['name']);
        $contactFormprospect->setFirstName($data['firstName']);
        $contactFormprospect->setEmail($data['email']);
        $contactFormprospect->setPhone($data['phone']);
        $contactFormprospect->setMessage($data['message']);
        $contactFormprospect->setDate(new \DateTimeImmutable());
        
        $contactFormProspectRepository->save($contactFormprospect, true);

        $mailerService->sendEmailToOwner($data['name'], $data['firstName'], $data['email'], $data['phone'], $data['message'], false);
        $mailerService->sendEmailToOwner($data['name'], $data['firstName'], $data['email'], $data['phone'], $data['message'], true);
        $mailerService->sendEmailToUser($data['name'], $data['firstName'], $data['email']);

        return new JsonResponse([
            'success' => true,
            'message' => 'Message envoyé avec succès !',  
            'data' => $contactFormprospect->normalize()
        ], 200);
    }

    #[Route('/contact-form/get', name: 'app_contact_form_api_get', methods: ['GET'])]
    public function index(ContactFormProspectRepository $contactFormProspectRepository
): JsonResponse
    {
        $contactForms = $contactFormProspectRepository->findAll();
        $data = [];
        foreach ($contactForms as $contactForm) {
            $data[] = $contactForm->normalize();
        }
        return $this->json([
            'success' => true,
            'message' => 'ContactForm listed successfully',
            'data' => $data
        ], 200);
    }
}
