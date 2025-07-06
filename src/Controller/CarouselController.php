<?php

namespace App\Controller;

use App\Repository\PhotoSlideRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

#[Route('carousel')]
class CarouselController extends AbstractController
{
    #[Route('/get_slides', name: 'app_carousel_get_slides', methods: ['GET'])]
    public function index(PhotoSlideRepository $photoSlideRepository): JsonResponse
    {
        $slides = $photoSlideRepository->findAll();
        $data = [];
        foreach ($slides as $slide) {
                $data[] = [
                    'id' => $slide->getId(),
                    'image' => $slide->getImage(),
                    'alt' => $slide->getAlt(),
                    'title' => $slide->getTitle(),
                    'description' => $slide->getDescription(),
                    'rank' => $slide->getRank(),
                ];
            }
        return $this->json([
            'success' => true,
            'message' => 'PhotoSlide listed successfully',
            'data' => $data], 200);
    }
}