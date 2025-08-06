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
        ], 200);
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
            ], 200);
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
        ], 200);
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
            ], 200);
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
        ], 200);
    }

    #[Route('/api/blog-post/up/{id}', name: 'app_blog_post_move_up', methods: ['POST'])]
    public function up(
        BlogPost $post, 
        EntityManagerInterface $entityManager,
        BlogPostRepository $blogPostRepository
        ): JsonResponse
    {
        if(!$post->isPublished()){
            return $this->json([
                'success' => false,
                'message' => 'BlogPost not published',
                'data' => $post->normalize()
            ], 200);
        }
        if($post->getRank() == 1){
            return $this->json([
                'success' => true,
                'message' => 'BlogPost already at the top',
                'data' => $post->normalize()
            ], 200);
        }
        $rank = $post->getRank();
        $previousPost = $blogPostRepository->findOneBy(['rank' => $rank - 1]);
        $rankPrevious = $previousPost->getRank();
        $previousPost->setRank($rank);
        $post->setRank($rankPrevious);
        $entityManager->flush();
        return $this->json([
            'success' => true,
            'message' => 'BlogPost moved up successfully',
            'data' => $post->normalize()
        ], 200);
    }

    #[Route('/api/blog-post/down/{id}', name: 'app_blog_post_move_down', methods: ['POST'])]
    public function down(
        BlogPost $post, 
        EntityManagerInterface $entityManager,
        BlogPostRepository $blogPostRepository
        ): JsonResponse
    {
        if(!$post->isPublished()){
            return $this->json([
                'success' => false,
                'message' => 'BlogPost not published',
                'data' => $post->normalize()
            ], 200);
        }
        $count = count($blogPostRepository->findBy(['isPublished' => true], []));
        if($post->getRank() == $count)
        {
            return $this->json([
                'success' => true,
                'message' => 'BlogPost already at the bottom',
                'data' => $post->normalize()
            ], 200);
        }

        $rank = $post->getRank();
        $nextPost = $blogPostRepository->findOneBy(['rank' => $rank + 1]);
        $rankNext = $nextPost->getRank();
        $nextPost->setRank($rank);
        $post->setRank($rankNext);
        $entityManager->flush();
        return $this->json([
            'success' => true,
            'message' => 'BlogPost moved down successfully',
            'data' => $post->normalize()
        ], 200);
    }

    #[Route('/api/blog-post/top/{id}', name: 'app_blog_post_move_top', methods: ['POST'])]
    public function top(
        BlogPost $post, 
        EntityManagerInterface $entityManager,
        BlogPostRepository $blogPostRepository
        ): JsonResponse
    {
        if(!$post->isPublished()){
            return $this->json([
                'success' => false,
                'message' => 'BlogPost not published',
                'data' => $post->normalize()
            ], 200);
        }
        if($post->getRank() == 1){
            return $this->json([
                'success' => true,
                'message' => 'BlogPost already at the top',
                'data' => $post->normalize()
            ], 200);
        }

        $post->setRank(0);
        $entityManager->flush();
        $blogPostRepository->regenerateRanks($entityManager);
        return $this->json([
            'success' => true,
            'message' => 'BlogPost moved to top successfully',
            'data' => $post->normalize()
        ], 200);
    }

    #[Route('/api/blog-post/bottom/{id}', name: 'app_blog_post_move_bottom', methods: ['POST'])]
    public function bottom(
        BlogPost $post, 
        EntityManagerInterface $entityManager,
        BlogPostRepository $blogPostRepository
        ): JsonResponse
    {
        if(!$post->isPublished()){
            return $this->json([
                'success' => false,
                'message' => 'BlogPost not published',
                'data' => $post->normalize()
            ], 200);
        }
        $count = count($blogPostRepository->findBy(['isPublished' => true], []));
        if($post->getRank() == $count){
            return $this->json([
                'success' => true,
                'message' => 'BlogPost already at the bottom',
                'data' => $post->normalize()
            ], 200);
        }
        $post->setRank($count + 1);
        $entityManager->flush();
        $blogPostRepository->regenerateRanks($entityManager);
        return $this->json([
            'success' => true,
            'message' => 'BlogPost moved to bottom successfully',
            'data' => $post->normalize()
        ], 200);
    }
}
?>