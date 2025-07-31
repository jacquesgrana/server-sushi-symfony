<?php

namespace App\Controller;

use App\Repository\BlogPostRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class BlogPostController extends AbstractController
{

    #[Route('/blog-post/published/get', name: 'app_blog_post_published_get', methods: ['GET'])]
    public function getPosts(BlogPostRepository $blogPostRepository): JsonResponse
    {
        $posts = $blogPostRepository->findBy(['isPublished' => true], ['rank' => 'ASC']);
        $data = [];
        foreach ($posts as $post) {
            $data[] = $post->normalize();
        }
        return $this->json([
            'success' => true,
            'message' => 'BlogPosts listed successfully',
            'data' => $data
        ]);
    }
}

?>