<?php

namespace App\Controller;

use App\Repository\PhotoSlideRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;


class CarouselController extends AbstractController
{
    #[Route('/carousel/get_slides', name: 'app_carousel_get_slides', methods: ['GET'])]
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

    #[Route('api/carousel/up/{id}', name: 'app_carousel_slide_up', methods: ['GET'])]
    public function up(int $id, PhotoSlideRepository $photoSlideRepository): JsonResponse
    {
        $photoSlide = $photoSlideRepository->find($id);
        if(!$photoSlide){
            return $this->json([
                'success' => false,
                'message' => 'PhotoSlide not found',
                'data' => []], 404);
        }
        if($photoSlide->getRank() > 1){
            $rank = $photoSlide->getRank();
            $previousPhotoSlide = $photoSlideRepository->findOneBy(['rank' => $rank - 1]);
            $rankPrevious = $previousPhotoSlide->getRank();
            $previousPhotoSlide->setRank($rank);
            $photoSlide->setRank($rankPrevious);
            $photoSlideRepository->save($previousPhotoSlide, false);
            $photoSlideRepository->save($photoSlide, true);

            return $this->json([
                'success' => true,
                'message' => 'PhotoSlide moved up successfully',
                'data' => $id], 200);
        }
        else {
            return $this->json([
                'success' => false,
                'message' => 'PhotoSlide already at the top',
                'data' => []], 200);
        }
    }

    #[Route('api/carousel/down/{id}', name: 'app_carousel_slide_down', methods: ['GET'])]
    public function down(int $id, PhotoSlideRepository $photoSlideRepository): JsonResponse
    {
        $photoSlide = $photoSlideRepository->find($id);
        if(!$photoSlide){
            return $this->json([
                'success' => false,
                'message' => 'PhotoSlide not found',
                'data' => []], 404);
        }
        if($photoSlide->getRank() < count($photoSlideRepository->findAll())){
            $rank = $photoSlide->getRank();
            $nextPhotoSlide = $photoSlideRepository->findOneBy(['rank' => $rank + 1]);
            $rankNext = $nextPhotoSlide->getRank();
            $nextPhotoSlide->setRank($rank);
            $photoSlide->setRank($rankNext);
            $photoSlideRepository->save($nextPhotoSlide, false);
            $photoSlideRepository->save($photoSlide, true); 

            return $this->json([
                'success' => true,
                'message' => 'PhotoSlide moved down successfully',
                'data' => $id], 200);
        }
        else {
            return $this->json([
                'success' => false,
                'message' => 'PhotoSlide already at the bottom',
                'data' => []], 200);
        }
    }                   


}