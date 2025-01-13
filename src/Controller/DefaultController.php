<?php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DefaultController extends AbstractController
{
    #[Route(
        '/{vueRouting}',
        name: 'vue_frontend',
        requirements: ['vueRouting' => '^(?!logout|login|api).*$']
    )]
    public function index(): Response
    {
        $this->denyAccessUnlessGranted('ACCESS_RESOURCE');

        $user = $this->getUser();
        $isAdmin = $user && in_array('ROLE_ADMIN', $user->getRoles());

        return $this->render('base.html.twig', [
            'controller_name' => 'DefaultController',
            'isAdmin' => $isAdmin,
        ]);
    }
}