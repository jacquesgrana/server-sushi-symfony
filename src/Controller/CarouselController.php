<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

#[Route('carousel')]
class CarouselController extends AbstractController
{
    #[Route('/get_slides', name: 'app_carousel_get_slides', methods: ['GET'])]
    public function index(): JsonResponse
    {
        return $this->json([
            'message' => 'Welcome to your new controller!',
            'path' => 'src/Controller/CarouselController.php',
        ]);
    }
}