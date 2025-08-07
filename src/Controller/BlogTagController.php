<?php

namespace App\Controller;

use App\Repository\BlogTagRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class BlogTagController extends AbstractController
{
    #[Route('/blog-tag/get', name: 'app_blog_tag_get', methods: ['GET'])]
    public function getTags(BlogTagRepository $blogTagRepository): JsonResponse 
    {
        $tags = $blogTagRepository->findAll();
        $data = [];
        foreach ($tags as $tag) {
            $data[] = $tag->normalizeWithoutDependencies();
        }
        return new JsonResponse([
            "success" => true,
            "message" => "Tags listed successfully",
            "data" => $data
        ], 200);
    }
}

?>

