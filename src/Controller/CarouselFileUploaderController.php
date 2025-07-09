<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\File\Exception\FileException;


class CarouselFileUploaderController extends AbstractController
{
    /**
     * @Route("/api/upload/carousel-image", name="api_upload_carousel_image", methods={"POST"})
     */

    #[Route('api/carousel/upload/carousel-image', name: 'api_upload_carousel_image', methods: ['POST'])]
    public function uploadImage(Request $request, SluggerInterface $slugger): JsonResponse
    {

        //dd($request->files->all());
        // 1. Récupérer le fichier depuis la requête
        // La clé 'image' DOIT correspondre à la clé utilisée dans le FormData du front-end
        /** @var UploadedFile $uploadedFile */
        $uploadedFile = $request->files->get('image');

        // Si aucun fichier n'est envoyé, renvoyer une erreur
        if (!$uploadedFile) {
            return $this->json(['error' => 'Aucun fichier n\'a été envoyé.'], 400);
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
            return $this->json(['error' => 'Impossible d\'enregistrer le fichier.'], 500);
        }

        // 4. Renvoyer une réponse de succès avec le chemin public de l'image
        // Ce chemin permettra au front-end d'afficher l'image immédiatement
        return $this->json([
            'message' => 'Fichier uploadé avec succès!',
            'imageName' => $newFilename
        ] , 200);
    }
}
