<?php

namespace App\Controller;

use App\Entity\ContactForm;
use App\Entity\ContactFormProspect;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

use Symfony\Component\HttpFoundation\Request;
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
    ): JsonResponse
     {

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

    #[Route('/api/contact-form-prospect/update/{id}', name: 'app_contact_form_prospect_api_update', methods: ['PUT'])]
    public function updateContactFormProspect(
        ContactFormProspect $contactFormProspect,
        EntityManagerInterface $entityManager,
        Request $request
    ): JsonResponse
    {
        $content = $request->getContent();
        $data = json_decode($content, true);

        $contactFormProspect->setName($data['name']);
        $contactFormProspect->setFirstName($data['firstName']);
        $contactFormProspect->setEmail($data['email']);
        $contactFormProspect->setPhone($data['phone']);
        $contactFormProspect->setComment($data['comment']);
        //$entityManager->persist($contactFormProspect);
        $entityManager->flush();
        return $this->json([
            'success' => true,
            'message' => 'Prospect updated successfully',
            'data' => $contactFormProspect->normalize()
        ], 200);
    }

    #[Route('/api/contact-form-prospect/delete/{id}', name: 'app_contact_form_prospect_api_delete', methods: ['DELETE'])]
    public function deleteContactFormProspect(
        ContactFormProspect $contactFormProspect,
        EntityManagerInterface $entityManager
    ): JsonResponse
    {
        if($contactFormProspect->getContactForms()->count() > 0) {
            foreach ($contactFormProspect->getContactForms() as $contactForm) {
                $contactForm->setContactFormProspect(null);
                //$entityManager->persist($contactForm);
            }
        }
        $entityManager->remove($contactFormProspect);
        $entityManager->flush();
        return $this->json([
            'success' => true,
            'message' => 'Prospect deleted successfully',
            'data' => []
        ], 200);
    }

    #[Route('/api/contact-form-prospect/create', name: 'app_contact_form_prospect_api_create', methods: ['POST'])]
    public function createContactFormProspect(
        EntityManagerInterface $entityManager,
        Request $request,
        ContactFormProspectRepository $contactFormProspectRepository
    ): JsonResponse 
    {
        $content = $request->getContent();
        $data = json_decode($content, true);

        // TODO verifier si l'email n'est pas deja utilisé par un autre Prospect ???
        $oldProspect = $contactFormProspectRepository->findOneBy(['email' => $data['email']]);

        if ($oldProspect) {
            return $this->json([
                'success' => false,
                'message' => 'Email already exists',
                'data' => $oldProspect->normalize()
            ], 200);
        }

        $contactFormProspect = new ContactFormProspect();
        $contactFormProspect->setName($data['name']);
        $contactFormProspect->setFirstName($data['firstName']);
        $contactFormProspect->setEmail($data['email']);
        $contactFormProspect->setPhone($data['phone']);
        $contactFormProspect->setComment($data['comment']);
        $contactFormProspect->setDate(new \DateTime());
        $entityManager->persist($contactFormProspect);
        $entityManager->flush();

        return $this->json([
            'success' => true,
            'message' => 'Prospect created successfully',
            'data' => $contactFormProspect->normalize()
        ], 200);
    }

    #[Route('/api/contact-form-prospect/export', name: 'app_contact_form_prospect_api_export', methods: ['POST'])]
    public function exportProspectsFromForm(
        ContactFormProspectRepository $contactFormProspectRepository,
        Request $request
        ): Response
    {
        $content = $request->getContent();
        $data = json_decode($content, true);
        //dd($data);
        $prospectIdsString = $data['prospects'];
        $prospectIds = $prospectIdsString ? explode(',', $prospectIdsString) : [];

        $fieldsString = $data['fields'];
        $fields = $fieldsString ? explode(',', $fieldsString) : [];
        //dd($prospectIds, $fields);
        $prospects = $contactFormProspectRepository->findByProspectIds($prospectIds);

        //dd($prospects);
        $filteredDatas = [];
        foreach ($prospects as $p) {
            $row = [
                'name'      => $p->getName(),
                'firstName' => $p->getFirstName(),
                'email'     => $p->getEmail(),
                'phone'     => $p->getPhone(),
                'comment'   => $p->getComment(),
            ];

            // ne conserver que les clés demandées
            $filteredDatas[] = array_intersect_key(
                $row,
                array_flip($fields)
            );
        }

        //dd($filteredDatas);

        // transformer en csv
        
        $csv = fopen('php://output', 'w');
        // régler le séparateur de colonne
        fputcsv($csv, $fields, ';');
        foreach ($filteredDatas as $data) {
            fputcsv($csv, $data, ';');
        }
        fclose($csv);
        //dd($csv);
        // renvoyer le fichier csv

        $csvFile = file_get_contents('php://output');
        $response = new Response();
        $response->headers->set('Content-Type', 'text/csv');
        $response->headers->set('Content-Disposition', 'attachment; filename="prospects.csv"');
        $response->setContent($csvFile);
        
        return $response;
        /*
        return $this->json([
            'success' => true,
            'message' => 'Prospects exported successfully',
            'data' => $filteredDatas
        ], 200);
        */
    }
}