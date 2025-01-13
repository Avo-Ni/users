<?php

namespace App\Controller;

use App\Entity\Role;
use App\Repository\RoleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;

class RoleController extends AbstractController
{
    #[Route('/api/roles', name: 'api_roles', methods: ['GET'])]
    public function index(RoleRepository $roleRepository, SerializerInterface $serializer): JsonResponse
    {
        $roles = $roleRepository->findAll();
        $data = $serializer->normalize($roles);

        return new JsonResponse($data, Response::HTTP_OK);
    }

    #[Route('/api/role', name: 'api_role_create', methods: ['POST'])]
    public function create(Request $request, EntityManagerInterface $entityManager, SerializerInterface $serializer): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!isset($data['name']) || empty($data['name'])) {
            return new JsonResponse(['message' => 'Le nom du rôle est requis.'], JsonResponse::HTTP_BAD_REQUEST);
        }

        $role = new Role();
        $role->setName($data['name']);

        $entityManager->persist($role);
        $entityManager->flush();

        return new JsonResponse(
            $serializer->normalize($role, null, ['groups' => 'role:read']),
            JsonResponse::HTTP_CREATED
        );
    }

    #[Route('/api/role/{id}', name: 'api_role_edit', methods: ['PUT'])]
    public function edit($id, Request $request, EntityManagerInterface $entityManager, RoleRepository $roleRepository, SerializerInterface $serializer): JsonResponse
    {
        $role = $roleRepository->find($id);

        if (!$role) {
            throw new NotFoundHttpException('Rôle introuvable.');
        }

        $data = json_decode($request->getContent(), true);
        $role->setName($data['name'] ?? $role->getName());

        $entityManager->flush();

        return new JsonResponse(
            $serializer->normalize($role, null, ['groups' => 'role:read']),
            JsonResponse::HTTP_OK
        );
    }

    #[Route('/api/role/{id}', name: 'api_role_delete', methods: ['DELETE'])]
    public function delete($id, EntityManagerInterface $entityManager, RoleRepository $roleRepository): JsonResponse
    {
        $role = $roleRepository->find($id);

        if (!$role) {
            throw new NotFoundHttpException('Rôle introuvable.');
        }

        $entityManager->remove($role);
        $entityManager->flush();

        return new JsonResponse(['message' => 'Rôle supprimé avec succès.'], JsonResponse::HTTP_OK);
    }
}
