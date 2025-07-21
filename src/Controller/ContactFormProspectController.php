<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\ContactFormProspectRepository;

final class ContactFormProspectController extends AbstractController
{
    #[Route('/api/contact-form-prospect/get', name: 'app_contact_form_api_get', methods: ['GET'])]
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
}