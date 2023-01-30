<?php

namespace App\Controller;

use App\Entity\Post;
use App\Form\SearchPostType;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class DashboardController extends AbstractController
{
    /**
     * @Route("/", name="app_dashboard")
     */
    public function index(PaginatorInterface $paginator, Request $request): Response
    {
        $manager = $this->getDoctrine()->getManager();
        $query = $manager->getRepository(Post::class)->findAllPostsDashboard();
        $pagination = $paginator->paginate(
            $query, 
            $request->query->getInt('page', 1), 
            3 # Numero de elementos
        );
        
        return $this->render('dashboard/index.html.twig', [
            'posts' => $pagination
        ]);
    }

}
