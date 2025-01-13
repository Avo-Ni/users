<?php

namespace App\Controller;

use App\Entity\Resource;
use App\Repository\ResourceRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;

class ResourceController extends AbstractController
{
    #[Route('/api/resources', name: 'api_resources', methods: ['GET'])]
    public function index(ResourceRepository $resourceRepository, SerializerInterface $serializer): JsonResponse
    {
        $resources = $resourceRepository->findAll();
        $data = $serializer->normalize($resources);

        return new JsonResponse($data, Response::HTTP_OK);
    }

    #[Route('/api/resource', name: 'api_resource_create', methods: ['POST'])]
    public function create(Request $request, EntityManagerInterface $entityManager, SerializerInterface $serializer): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!isset($data['name']) || empty($data['name'])) {
            return new JsonResponse(['message' => 'Le nom de la resource est requis.'], JsonResponse::HTTP_BAD_REQUEST);
        }

        $resource = new Resource();
        $resource->setName($data['name']);
        $resource->setPath($data['path']);


        $entityManager->persist($resource);
        $entityManager->flush();

        return new JsonResponse(
            $serializer->normalize($resource, null, ['groups' => 'resource:read']),
            JsonResponse::HTTP_CREATED
        );
    }

    #[Route('/api/resource/{id}', name: 'api_resource_edit', methods: ['PUT'])]
    public function edit($id, Request $request, EntityManagerInterface $entityManager, ResourceRepository $resourceRepository, SerializerInterface $serializer): JsonResponse
    {
        $resource = $resourceRepository->find($id);

        if (!$resource) {
            throw new NotFoundHttpException('Resource introuvable.');
        }

        $data = json_decode($request->getContent(), true);
        $resource->setName($data['name'] ?? $resource->getName());

        $entityManager->flush();

        return new JsonResponse(
            $serializer->normalize($resource, null, ['groups' => 'resource:read']),
            JsonResponse::HTTP_OK
        );
    }

    #[Route('/api/resource/{id}', name: 'api_resource_delete', methods: ['DELETE'])]
    public function delete($id, EntityManagerInterface $entityManager, ResourceRepository $resourceRepository): JsonResponse
    {
        $resource = $resourceRepository->find($id);

        if (!$resource) {
            throw new NotFoundHttpException('Resource introuvable.');
        }

        $entityManager->remove($resource);
        $entityManager->flush();

        return new JsonResponse(['message' => 'Resource supprimé avec succès.'], JsonResponse::HTTP_OK);
    }
}
