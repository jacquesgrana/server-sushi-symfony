<?php

namespace App\Controller;

use App\Repository\BlogTagRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\BlogTag;

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

    #[Route('/api/blog-tag/update/{id}', name: 'app_blog_tag_update', methods: ['PATCH'])]
    public function updateTag(
        BlogTag $blogTag,
        BlogTagRepository $blogTagRepository,
        Request $request,
        EntityManagerInterface $entityManager
        ): JsonResponse 
        {

        $content = $request->getContent();
        $data = json_decode($content, true);

        if(!isset($data['slug']) || !isset($data['name'])) {
            return $this->json([
                'success' => false,
                'message' => 'Missing parameters in JSON body',
                'data' => []
            ], 400);
        }

        $isNewSlug = $blogTagRepository->findOneBy(['slug' => $data['slug']]) ? false : true;

        if (!$isNewSlug) {
            return $this->json([
                'success' => false,
                'message' => 'Tag slug already exists',
                'data' => []
            ], 400);
        }

        $blogTag->setSlug($data['slug']);
        $blogTag->setName($data['name']);

        $entityManager->flush();

        return new JsonResponse([
            "success" => true,
            "message" => "Tag updated successfully",
            "data" => $blogTag->normalize()
        ], 200);
    }

    #[Route('/api/blog-tag/create', name: 'app_blog_tag_create', methods: ['POST'])]
    public function createTag(
        BlogTagRepository $blogTagRepository,
        Request $request,
        EntityManagerInterface $entityManager
    ): JsonResponse 
    {
        $content = $request->getContent();
        $data = json_decode($content, true);

        if(!isset($data['slug']) || !isset($data['name'])) {
            return $this->json([
                'success' => false,
                'message' => 'Missing parameters in JSON body',
                'data' => []
            ], 400);
        }

        $isNewSlug = $blogTagRepository->findOneBy(['slug' => $data['slug']]) ? false : true;

        if (!$isNewSlug) {
            return $this->json([
                'success' => false,
                'message' => 'Tag slug already exists',
                'data' => []
            ], 400);
        }

        $blogTag = new BlogTag();
        $blogTag->setSlug($data['slug']);
        $blogTag->setName($data['name']);

        $entityManager->persist($blogTag);
        $entityManager->flush();

        return new JsonResponse([
            "success" => true,
            "message" => "Tag created successfully",
            "data" => $blogTag->normalize()
        ], 200);
    }
      
    #[Route('/api/blog-tag/delete/{id}', name: 'app_blog_tag_delete', methods: ['DELETE'])]
    public function deleteTag(
        BlogTag $blogTag, 
        EntityManagerInterface $entityManager
        ): JsonResponse
    {
        if($blogTag->getBlogPosts() && $blogTag->getBlogPosts()->count() > 0) {
            $posts = $blogTag->getBlogPosts();
            foreach ($posts as $blogPost) {
                $blogPost->removeTag($blogTag);
            }
        }

        $entityManager->remove($blogTag);
        $entityManager->flush();
        return $this->json([
            'success' => true,
            'message' => 'Tag deleted successfully',
            'data' => []
        ], 200);
    }
}

?>

