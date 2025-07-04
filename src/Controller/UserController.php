<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

#[Route('api/user')]
class UserController extends AbstractController
{
    #[Route('/user_infos', name: 'app_user_infos_api', methods: ['GET'])]
    public function getUserInfos(): JsonResponse
    {   
        $user = $this->getUser();
        if (!$user instanceof User) {
            return new JsonResponse(['error' => 'Utilisateur non trouvé'], 404);
        }

        return new JsonResponse([
            'name' => $user->getName(),
            'firstName' => $user->getFirstName(),
            'email' => $user->getEmail()
        ], 200);
        
    }
}