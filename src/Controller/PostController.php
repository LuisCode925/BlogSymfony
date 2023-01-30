<?php

namespace App\Controller;

use App\Entity\Post;
use App\Form\PostType;
use App\Form\SearchPostType;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;

class PostController extends AbstractController
{
    #[Route('/crear-post', name: 'app_post')]
    public function index(Request $request, SluggerInterface $slugger): Response
    {
        $post = new Post();
        $form = $this->createForm(PostType::class, $post);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $brochureFile = $form->get('thumb')->getData();
            if ($brochureFile) {
                $manager = $this->getDoctrine()->getManager();

                $originalFilename = pathinfo($brochureFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$brochureFile->guessExtension();
                try {
                    $brochureFile->move($this->getParameter('thumb_directory'),$newFilename);
                } catch (FileException $e) {
                    throw new FileException($e->getMessage());
                }
                $post->setThumb($newFilename);
            }

            $post->setUser($this->getUser());
            $manager->persist($post);
            $manager->flush();
            return $this->redirectToRoute('app_dashboard');
        }
        
        return $this->render('post/index.html.twig', [
            'form_post' => $form->createView(),
        ]);
    }

    /**
     * @Route("/post/{id}", name="app_show_post")
     */
    public function showPost($id): Response
    {
        $manager = $this->getDoctrine()->getManager();
        $post = $manager->getRepository(Post::class)->findPost($id);
        return $this->render('post/showPost.html.twig', [
            'post' => $post,
        ]);
    }

    /**
     * @Route("/mis-posts", name="app_own_posts")
     */
    public function OwnPosts(): Response
    {
        $user = $this->getUser();
        $manager = $this->getDoctrine()->getManager();
        $posts = $manager->getRepository(Post::class)->findBy(['User' => $user]);
        return $this->render('post/ownPosts.html.twig', [
            'posts' => $posts
        ]);
    }

    /**
     * @Route("/buscar/{search}", name="app_search_post")
     */
    public function searchPosts(PaginatorInterface $paginator, Request $request, $search): Response
    {

        if (!empty($search)) {
            $manager = $this->getDoctrine()->getManager();
            $query = $manager->getRepository(Post::class)->searchPostsLike($search);
            $pagination = $paginator->paginate(
                $query, 
                $request->query->getInt('page', 1), 
                3 # Numero de elementos
            );

            return $this->render('post/searchPost.html.twig', [
                'posts' => $pagination
            ]);
        }
        
    }

}