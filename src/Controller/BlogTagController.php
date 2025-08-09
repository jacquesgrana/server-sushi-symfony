<?php

namespace App\Controller;

use App\Repository\BlogTagRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;

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

    #[Route('/api/blog-tag/check-uniqueness', name: 'app_blog_tag_check_uniqueness', methods: ['POST'])]
    public function checkNewTagUniqueness(
        BlogTagRepository $blogTagRepository,
        Request $request): JsonResponse 
    {
        $content = $request->getContent();
        $data = json_decode($content, true);

        if(!isset($data['slug'])) {
            return $this->json([
                'success' => false,
                'message' => 'Missing slug parameter in JSON body',
                'data' => []
            ], 400);
        }

        $tagSlug = $data['slug'];
        $isNewSlug = $blogTagRepository->findOneBy(['slug' => $tagSlug]) ? false : true;

        return new JsonResponse([
            "success" => true,
            "message" => "Tag verified successfully",
            "data" => ["isNewSlug" => $isNewSlug]
        ], 200);
    }
}

?>

