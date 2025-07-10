<?php

namespace App\Controller;

use App\Entity\PhotoSlide;
use App\Repository\PhotoSlideRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\File\Exception\FileException;

class CarouselController extends AbstractController
{
    #[Route('carousel/get_slides', name: 'app_carousel_get_slides', methods: ['GET'])]
    public function index(PhotoSlideRepository $photoSlideRepository): JsonResponse
    {
        $slides = $photoSlideRepository->findAll();
        $data = [];
        foreach ($slides as $slide) {
                $data[] = $slide->serialize();
            }
        return $this->json([
            'success' => true,
            'message' => 'PhotoSlide listed successfully',
            'data' => $data
        ], 200);
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
                'success' => true,
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
                'success' => true,
                'message' => 'PhotoSlide already at the bottom',
                'data' => []], 200);
        }
    }
    
    #[Route('api/carousel/top/{id}', name: 'app_carousel_slide_top', methods: ['GET'])]
    public function top(int $id, PhotoSlideRepository $photoSlideRepository, EntityManagerInterface $entityManager): JsonResponse
    {
        $photoSlide = $photoSlideRepository->find($id);
        if(!$photoSlide){
            return $this->json([
                'success' => false,
                'message' => 'PhotoSlide not found',
                'data' => []], 404);
        }

        if($photoSlide->getRank() > 1){
            $photoSlide->setRank(0);
            $photoSlideRepository->save($photoSlide, true);
            $photoSlideRepository->regenerateRanks($entityManager);

            return $this->json([
                'success' => true,
                'message' => 'PhotoSlide moved to top successfully',
                'data' => $id], 200);
        }
        else {
            return $this->json([
                'success' => true,
                'message' => 'PhotoSlide already at the top',
                'data' => []], 200);
        } 
    }

    #[Route('api/carousel/bottom/{id}', name: 'app_carousel_slide_bottom', methods: ['GET'])]
    public function bottom(int $id, PhotoSlideRepository $photoSlideRepository, EntityManagerInterface $entityManager): JsonResponse
    {
        $photoSlide = $photoSlideRepository->find($id);
        if(!$photoSlide){
            return $this->json([
                'success' => false,
                'message' => 'PhotoSlide not found',
                'data' => []], 404);
        }
        $slidesCount = count($photoSlideRepository->findAll());
        if($photoSlide->getRank() < $slidesCount){
            $photoSlide->setRank($slidesCount + 1);
            $photoSlideRepository->save($photoSlide, true);
            $photoSlideRepository->regenerateRanks($entityManager);

            return $this->json([
                'success' => true,
                'message' => 'PhotoSlide moved to bottom successfully',
                'data' => $id], 200);
        }
        else {
            return $this->json([
                'success' => true,
                'message' => 'PhotoSlide already at the bottom',
                'data' => []], 200);
        }
    }


    #[Route('api/carousel/update/carousel-image/{id}', name: 'api_update_carousel_image', methods: ['POST'])]
    public function updateImage(PhotoSlide $photoSlide, PhotoSlideRepository $photoSlideRepository, Request $request, SluggerInterface $slugger): JsonResponse
    {
        //dd($photoSlide);

        /** @var UploadedFile $uploadedFile */
        $uploadedFile = $request->files->get('imageFile');

        $oldImageName = $photoSlide->getImage();

        // Si aucun fichier n'est envoyé, renvoyer une erreur
        if (!$uploadedFile) {
            return $this->json([
                'success' => false,
                'error' => 'No file uploaded.',
                'data' => []
            ], 400);
        }

        // 2. Sécurité : Créer un nom de fichier unique et sûr
        $originalFilename = pathinfo($uploadedFile->getClientOriginalName(), PATHINFO_FILENAME);
        $safeFilename = $slugger->slug($originalFilename);
        $newFilename = $safeFilename.'-'.uniqid().'.'.$uploadedFile->guessExtension();

        // 3. Déplacer le fichier vers le répertoire de destination
        // Il est recommandé de définir ce chemin comme un paramètre dans services.yaml
        $destination = $this->getParameter('kernel.project_dir').'/public/image/carousel';

        try {
            $uploadedFile->move($destination, $newFilename);
        } catch (FileException $e) {
            // Gérer l'exception si quelque chose se passe mal pendant le déplacement
            return $this->json([
                'success' => false,
                'error' => 'Failed to move uploaded file.',
                'data' => []
            ], 500);
        }

        // supprimer l'ancienne image $oldImage
        // TODO : try/catch ?
        //unlink($this->getParameter('kernel.project_dir').'/public/image/carousel/'.$oldImage);


        $photoSlide->setImage($newFilename);
        $photoSlideRepository->save($photoSlide, true);

         // Suppression de l'ancienne image APRÈS que tout s'est bien passé
        if ($oldImageName) {
            $oldImagePath = $destination . '/' . $oldImageName;
            if (file_exists($oldImagePath)) {
                // Unlink est "best-effort", on ne bloque pas si ça échoue
                @unlink($oldImagePath); 
            }
        }

        return $this->json([
            'success' => true,
            'message' => 'photoSlide image updated successfully',
            'data' => $newFilename
        ] , 200);
    }

    // version avec 'Content-Type': 'application/x-www-form-urlencoded',
    /*
    #[Route('api/carousel/update/carousel-slide/{id}', name: 'api_update_carousel_slide', methods: ['POST'])]
    public function updateSlide(PhotoSlide $photoSlide, PhotoSlideRepository $photoSlideRepository, Request $request, SluggerInterface $slugger): JsonResponse
    {
        if(!$photoSlide){
            return $this->json([
                'success' => false,
                'message' => 'PhotoSlide not found',
                'data' => []
            ], 404);
        }
        if(!$request->get('title') || !$request->get('alt') || !$request->get('description')){
            return $this->json([
                'success' => false,
                'message' => 'Missing parameters',
                'data' => []
            ], 404);
        }

        $photoSlide->setTitle($request->get('title'));
        $photoSlide->setAlt($request->get('alt'));
        $photoSlide->setDescription($request->get('description'));
        $photoSlideRepository->save($photoSlide, true);

        return $this->json([
            'success' => true,
            'message' => 'photoSlide updated successfully',
            'data' => $photoSlide->serialize()
        ] , 200);
    }*/

    #[Route('api/carousel/update/carousel-slide/{id}', name: 'api_update_carousel_slide', methods: ['POST'])]
    public function updateSlide(PhotoSlide $photoSlide, PhotoSlideRepository $photoSlideRepository, Request $request): JsonResponse
    {
        // Pas besoin de vérifier $photoSlide, le ParamConverter de Symfony le fait pour nous.
        // S'il ne trouve pas l'entité, il renvoie déjà une 404.

        // 1. Récupérer le contenu brut du corps de la requête (qui est notre JSON)
        $content = $request->getContent();
        
        // 2. Décoder la chaîne JSON en tableau associatif PHP
        $data = json_decode($content, true);

        // 3. Vérifier que les paramètres existent dans le tableau $data
        if (!isset($data['title']) || !isset($data['alt']) || !isset($data['description'])) {
            return $this->json([
                'success' => false,
                'message' => 'Missing parameters in JSON body', // Message plus précis
                'data' => []
            ], 400); // 400 Bad Request est plus approprié qu'un 404 ici
        }

        // 4. Utiliser les données du tableau $data
        $photoSlide->setTitle($data['title']);
        $photoSlide->setAlt($data['alt']);
        $photoSlide->setDescription($data['description']);
        $photoSlideRepository->save($photoSlide, true);

        return $this->json([
            'success' => true,
            'message' => 'photoSlide updated successfully',
            'data' => $photoSlide->serialize()
        ], 200);
    }


    #[Route('api/carousel/delete/carousel-slide/{id}', name: 'api_delete_carousel_slide', methods: ['POST'])]
    public function deleteSlide(PhotoSlide $photoSlide, PhotoSlideRepository $photoSlideRepository, EntityManagerInterface $entityManager): JsonResponse
    {
        if(!$photoSlide){
            return $this->json([
                'success' => false,
                'message' => 'PhotoSlide not found',
                'data' => []
            ], 404);
        }
        $oldImageName = $photoSlide->getImage();
        $entityManager->remove($photoSlide);
        $entityManager->flush();
        //unlink($this->getParameter('kernel.project_dir').'/public/image/carousel/'.$oldFileName);
        $photoSlideRepository->regenerateRanks($entityManager);

        if ($oldImageName) {
            $oldImagePath = $this->getParameter('kernel.project_dir').'/public/image/carousel/' . $oldImageName;
            if (file_exists($oldImagePath)) {
                @unlink($oldImagePath);
            }
        }

        return $this->json([
            'success' => true,
            'message' => 'photoSlide deleted successfully',
            'data' => []
        ] , 200);
    }

    #[Route('api/carousel/create/carousel-slide', name: 'api_create_carousel_slide', methods: ['POST'])]
    public function createSlide(Request $request, PhotoSlideRepository $photoSlideRepository, EntityManagerInterface $entityManager, SluggerInterface $slugger): JsonResponse
    {
        $uploadedFile = $request->files->get('imageFile');

        // Si aucun fichier n'est envoyé, renvoyer une erreur
        if (!$uploadedFile) {
            return $this->json([
                'success' => false,
                'error' => 'No file uploaded.',
                'data' => []
            ], 400);
        }

        // 2. Sécurité : Créer un nom de fichier unique et sûr
        $originalFilename = pathinfo($uploadedFile->getClientOriginalName(), PATHINFO_FILENAME);
        $safeFilename = $slugger->slug($originalFilename);
        $newFilename = $safeFilename.'-'.uniqid().'.'.$uploadedFile->guessExtension();

        // 3. Déplacer le fichier vers le répertoire de destination
        // Il est recommandé de définir ce chemin comme un paramètre dans services.yaml
        $destination = $this->getParameter('kernel.project_dir').'/public/image/carousel';

        try {
            $uploadedFile->move($destination, $newFilename);
        } 
        catch (FileException $e) {
            // Gérer l'exception si quelque chose se passe mal pendant le déplacement
            return $this->json([
                'success' => false,
                'error' => 'Failed to move uploaded file.',
                'data' => []
            ], 500);
        }

        $title = $request->get('title');
        $alt = $request->get('alt');
        $description = $request->get('description');
        $photoSlide = new PhotoSlide();
        $photoSlide->setTitle($title);
        $photoSlide->setAlt($alt);
        $photoSlide->setDescription($description);
        $photoSlide->setImage($newFilename);

        //$slidesCount = $entityManager->getRepository(PhotoSlide::class)->count([]);
        $slidesCount = $photoSlideRepository->count([]);
        $photoSlide->setRank($slidesCount + 1);
        /*
        if($slidesCount == 0){
            $photoSlide->setRank(1);
        } 
        else {
            $photoSlide->setRank($slidesCount + 1);
        }
        */
        // set rank to last rank + 1

        $entityManager->persist($photoSlide);
        $entityManager->flush();

        return $this->json([
            'success' => true,
            'message' => 'photoSlide created successfully',
            'data' => $photoSlide->serialize()
        ] , 200);
    }
}