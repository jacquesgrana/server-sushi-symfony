<?php

namespace App\Controller;

use App\Repository\ContactFormRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Service\MailerService;
use App\Entity\ContactForm;
use Doctrine\ORM\EntityManagerInterface;


// TODO enlever 'api' de l'url ???!!!! et mofifier le parefeu et le front
final class ContactFormApiController extends AbstractController
{
    #[Route('/contact-form', name: 'app_contact_form_api', methods: ['POST', 'OPTIONS'])]
    public function manageContactForm(
        Request $request, 
        MailerService $mailerService,
        ContactFormRepository $contactFormRepository
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
        $contactForm = new ContactForm();
        $contactForm->setName($data['name']);
        $contactForm->setFirstName($data['firstName']);
        $contactForm->setEmail($data['email']);
        $contactForm->setPhone($data['phone']);
        $contactForm->setMessage($data['message']);
        $contactForm->setDate(new \DateTimeImmutable());
        
        $contactFormRepository->save($contactForm, true);

        $mailerService->sendEmailToOwner($data['name'], $data['firstName'], $data['email'], $data['phone'], $data['message'], false);
        $mailerService->sendEmailToOwner($data['name'], $data['firstName'], $data['email'], $data['phone'], $data['message'], true);
        $mailerService->sendEmailToUser($data['name'], $data['firstName'], $data['email']);

        return new JsonResponse([
            'success' => true,
            'message' => 'Message envoyé avec succès !',  
            'data' => $contactForm->normalize()
        ], 200);
    }

    #[Route('/api/contact-form/get', name: 'app_contact_form_api_get', methods: ['GET'])]
    public function index(ContactFormRepository $contactFormRepository
): JsonResponse
    {
        $contactForms = $contactFormRepository->findAll();
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

    #[Route('/api/contact-form/delete/{id}', name: 'app_contact_form_api_delete', methods: ['DELETE'])]
    public function delete(ContactForm $contactForm, EntityManagerInterface $entityManager): JsonResponse
    {
        if(!$contactForm){
            return $this->json([
                'success' => false,
                'message' => 'ContactForm not found',
                'data' => []
            ], 404);
        }
        // tester si le $contactForm a un prospect si oui le supprimer de la liste du prospect
        if($contactForm->getContactFormProspect()){
            $contactForm->getContactFormProspect()->removeContactForm($contactForm);
            //$contactForm->setContactFormProspect(null); // pas la peine, le removeContactForm fait cela
            //$entityManager->persist($contactForm->getContactFormProspect());
            //$entityManager->flush();
        }

        $entityManager->remove($contactForm);
        $entityManager->flush();
        return $this->json([
            'success' => true,
            'message' => 'ContactForm deleted successfully',
            'data' => []
        ], 200);
    }
}
