<?php

namespace App\Controller;

use App\Repository\BlogPostRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\BlogPost;
use App\Repository\BlogTagRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\File\Exception\FileException;

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


    ///api/blog-post/update/

    #[Route('/api/blog-post/update/infos/{id}', name: 'app_blog_post_update', methods: ['PATCH'])]
    public function updateInfos(
        BlogPost $post,
        EntityManagerInterface $entityManager,
        BlogTagRepository $blogTagRepository,
        Request $request
        ): JsonResponse
    {

        $content = $request->getContent();
        $data = json_decode($content, true);

        if(!isset($data['slug']) || !isset($data['title']) || !isset($data['intro']) || !isset($data['text']) || !isset($data['tags'])) {
            return $this->json([
                'success' => false,
                'message' => 'Missing parameters in JSON body',
                'data' => []
            ], 400);
        }

        $post->setSlug($data['slug']);
        $post->setTitle($data['title']);
        $post->setIntro($data['intro']);
        $post->setText($data['text']);
        $post->setModifiedAt(new \DateTimeImmutable());

        $tagIds  = array_filter(array_map('intval', explode(';', $data['tags'])));
        $newTags = $blogTagRepository->findBy(['id' => $tagIds]);

        if (count($newTags) !== count($tagIds)) {
            return $this->json([
                'success' => false,
                'message' => 'Some tag IDs do not exist',
            ], 400);
        }

        $post->setTags(new ArrayCollection($newTags));
        $entityManager->flush();

        return $this->json([
            'success' => true,
            'message' => 'BlogPost infos updated successfully',
            'data' => $post->normalize()
        ], 200);
    }


///api/blog-post/update/image/

    #[Route('/api/blog-post/update/image/{id}', name: 'app_blog_post_update_image', methods: ['POST'])]
    public function updateImage(
        BlogPost $post,
        EntityManagerInterface $entityManager,
        Request $request,
        SluggerInterface $slugger
    ): JsonResponse {

        /** @var UploadedFile $uploadedFile */
        $uploadedFile = $request->files->get('imageFile');
        $oldImageName = $post->getImageName();

        if(!$uploadedFile) {
            return $this->json([
                'success' => false,
                'error' => 'No file uploaded.',
                'data' => []
            ], 400);
        }

        $originalFilename = pathinfo($uploadedFile->getClientOriginalName(), PATHINFO_FILENAME);
        $safeFilename = $slugger->slug($originalFilename);
        $newFilename = $safeFilename.'-'.uniqid().'.'.$uploadedFile->guessExtension();

        $destination = $this->getParameter('kernel.project_dir').'/public/image/blog_post';

        try {
            $uploadedFile->move($destination, $newFilename);
        } 
        catch (FileException $e) {
            return $this->json([
                'success' => false,
                'error' => 'Failed to move uploaded file.',
                'data' => []
            ], 400);
        }

        $post->setImageName($newFilename);
        $post->setModifiedAt(new \DateTimeImmutable());
        $entityManager->flush();

        if($oldImageName && file_exists($destination.'/'.$oldImageName)) {
            unlink($destination.'/'.$oldImageName);
        }

        return $this->json([
            'success' => true,
            'message' => 'BlogPost image updated successfully',
            'data' => $post->normalize()
        ], 200);
    }

    
    #[Route('/api/blog-post/create', name: 'app_blog_post_create', methods: ['POST'])]
    public function createBlogPost(
        Request $request, 
        EntityManagerInterface $entityManager,
        SluggerInterface $slugger,
        BlogTagRepository $blogTagRepository
        ): JsonResponse
    {
        $user = $this->getUser();
        //dd($user);
        /** @var UploadedFile $uploadedFile */
        $uploadedFile = $request->files->get('imageFile');

        // Si aucun fichier n'est envoyé, renvoyer une erreur
        if (!$uploadedFile) {
            return $this->json([
                'success' => false,
                'error' => 'No file uploaded.',
                'data' => []
            ], 400);
        }

        $originalFilename = pathinfo($uploadedFile->getClientOriginalName(), PATHINFO_FILENAME);
        $safeFilename = $slugger->slug($originalFilename);
        $newFilename = $safeFilename.'-'.uniqid().'.'.$uploadedFile->guessExtension();  

        $destination = $this->getParameter('kernel.project_dir').'/public/image/blog_post';

        try {
            $uploadedFile->move($destination, $newFilename);
        } 
        catch (FileException $e) {
            return $this->json([
                'success' => false,
                'error' => 'Failed to move uploaded file.',
                'data' => []
            ], 400);
        }

        //$data = json_decode($request->getContent(), true);

        $post = new BlogPost();

        $post->setSlug($request->get('slug'));
        $post->setTitle($request->get('title'));
        $post->setIntro($request->get('intro'));
        $post->setText($request->get('text'));
        $post->setImageName($newFilename);
        $post->setCreatedAt(new \DateTimeImmutable());
        $post->setModifiedAt(new \DateTimeImmutable());
        $post->setAuthor($user);
        $post->setIsPublished(false);
        $post->setRank(0);

        $tagIds  = array_filter(array_map('intval', explode(';', $request->get('tags'))));
        $newTags = $blogTagRepository->findBy(['id' => $tagIds]);
        $post->setTags(new ArrayCollection($newTags));
        $entityManager->persist($post);
        $entityManager->flush();
        //$blogPostRepository->regenerateRanks($entityManager);

        return $this->json([
            'success' => true,
            'message' => 'BlogPost created successfully',
            'data' => $post->normalize()
        ], 200);
    }

    #[Route('/api/blog-post/delete/{id}', name: 'app_blog_post_delete', methods: ['DELETE'])]
    public function deleteBlogPost(
        BlogPost $post, 
        EntityManagerInterface $entityManager,
        BlogPostRepository $blogPostRepository
        ): JsonResponse
    {
        $isPublished = $post->isPublished();
        $oldImageName = $post->getImageName();
        
        if($post->getTags()) {
            foreach ($post->getTags() as $tag) {
                $post->removeTag($tag);
            }
        }

        $entityManager->remove($post);
        $entityManager->flush();

        if ($isPublished) {
            $blogPostRepository->regenerateRanks($entityManager);
        }
        if ($oldImageName) {
            $oldImagePath = $this->getParameter('kernel.project_dir').'/public/image/blog_post/' . $oldImageName;
            if (file_exists($oldImagePath)) {
                @unlink($oldImagePath);
            }
        }
        return $this->json([
            'success' => true,
            'message' => 'BlogPost deleted successfully',
            'data' => []
        ], 200);
    }
}
?>

