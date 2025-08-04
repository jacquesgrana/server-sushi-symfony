<?php

namespace App\Controller;

use App\Repository\BlogPostRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\BlogPost;
use Doctrine\ORM\EntityManagerInterface;

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

    #[Route('/api/blog-post/publish/{id}', name: 'app_blog_post_publish', methods: ['POST'])]
    public function publishPost(
        BlogPost $post, 
        EntityManagerInterface $entityManager,
        BlogPostRepository $blogPostRepository
        ): JsonResponse
    {
        if($post->isPublished()){
            return $this->json([
                'success' => false,
                'message' => 'BlogPost already published',
                'data' => $post->normalize()
            ]);
        }
        $post->setIsPublished(true);
        $post->setRank(0);
        $entityManager->flush();
        $blogPostRepository->regenerateRanks($entityManager);
        //$entityManager->persist($post);
        return $this->json([
            'success' => true,
            'message' => 'BlogPost published successfully',
            'data' => $post->normalize()
        ]);
    }

    #[Route('/api/blog-post/unpublish/{id}', name: 'app_blog_post_unpublish', methods: ['POST'])]
    public function unpublishPost(
        BlogPost $post, 
        EntityManagerInterface $entityManager,
        BlogPostRepository $blogPostRepository
        ): JsonResponse
    {
        if(!$post->isPublished()){
            return $this->json([
                'success' => false,
                'message' => 'BlogPost already unpublished',
                'data' => $post->normalize()
            ]);
        }
        $post->setIsPublished(false);
        $post->setRank(0);
        $entityManager->flush();
        $blogPostRepository->regenerateRanks($entityManager);
        //$entityManager->persist($post);
        
        return $this->json([
            'success' => true,
            'message' => 'BlogPost unpublished successfully',
            'data' => $post->normalize()
        ]);
    }
}
?>