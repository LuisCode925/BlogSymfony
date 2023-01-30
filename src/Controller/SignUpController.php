<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class SignUpController extends AbstractController
{
    #[Route('/registrar', name: 'app_sign_up')]
    public function index(Request $request, UserPasswordHasherInterface $passwordHasher): Response
    {
        $user = new User(); // En el constructor se definen las propieddades por defecto
        $form = $this->createForm(UserType::class, $user);

        // Se obtiene el formulario
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();

            $hashedPassword = $passwordHasher->hashPassword($user, $form['password']->getData());
            $user->setPassword($hashedPassword);

            $em->persist($user);
            $em->flush();
            $this->addFlash('success', User::REGISTRO_EXITOSO);

            return $this->redirectToRoute('app_sign_up');
        }

        return $this->render('sign_up/index.html.twig', [
            'title' => 'Registrate Ahora',
            'sign_up_form' => $form->createView(),
        ]);
    }
}
