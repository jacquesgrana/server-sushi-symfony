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

    #[Route('/api/blog-post/unpublished/get', name: 'app_blog_post_unpublished_get', methods: ['GET'])]
    public function getUnpublishedPosts(
        BlogPostRepository $blogPostRepository
        ): JsonResponse
    {
        $posts = $blogPostRepository->findBy(['isPublished' => false], ['id' => 'DESC']);
        $data = [];
        foreach ($posts as $post) {
            $data[] = $post->normalize();
        }
        return $this->json([
            'success' => true,
            'message' => 'Unpublished BlogPosts listed successfully',
            'data' => $data
        ]);
    }
}
?>