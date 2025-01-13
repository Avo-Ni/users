<?php

namespace App\Controller;

use App\Entity\UserPrivilege;
use App\Repository\UserRepository;
use App\Repository\ResourceRepository;
use App\Repository\UserPrivilegeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\HttpFoundation\Response;

class UserPrivilegeController extends AbstractController
{
    private $userRepository;
    private $resourceRepository;
    private $userPrivilegeRepository;

    public function __construct(
        UserRepository          $userRepository,
        ResourceRepository      $resourceRepository,
        UserPrivilegeRepository $userPrivilegeRepository
    )
    {
        $this->userRepository = $userRepository;
        $this->resourceRepository = $resourceRepository;
        $this->userPrivilegeRepository = $userPrivilegeRepository;
    }

    #[Route('/api/user/{userId}/privileges', name: 'api_user_privileges_by_user', methods: ['GET'])]
    public function getUserPrivileges($userId, SerializerInterface $serializer): JsonResponse
    {
        $user = $this->userRepository->createQueryBuilder('u')
            ->leftJoin('u.roles', 'r')
            ->addSelect('r')
            ->where('u.id = :userId')
            ->setParameter('userId', $userId)
            ->getQuery()
            ->getOneOrNullResult();

        if (!$user) {
            throw new NotFoundHttpException('Utilisateur introuvable.');
        }

        $userPrivileges = $this->userPrivilegeRepository->findBy(['user' => $user]);
        $resources = $this->resourceRepository->findAll();

        $data = [
            'user' => [
                'id' => $user->getId(),
                'email' => $user->getEmail(),
                'firstname' => $user->getFirstname(),
                'lastname' => $user->getLastname(),
                'roles' => array_map(function($role) {
                    return is_object($role) ? $role->getRole() : $role;
                }, $user->getRoles()),
                'resources' => []
            ]
        ];

        foreach ($resources as $resource) {
            $resourceData = [
                'id' => $resource->getId(),
                'name' => $resource->getName(),
                'userPrivileges' => []
            ];

            foreach ($userPrivileges as $userPrivilege) {
                if ($userPrivilege->getResource()->getId() === $resource->getId()) {
                    $resourceData['userPrivileges'][] = [
                        'allowed' => $userPrivilege->isAllowed()
                    ];
                }
            }
            $data['user']['resources'][] = $resourceData;
        }

        return new JsonResponse($data, Response::HTTP_OK);
    }

    #[Route('/api/user-privileges', name: 'api_user_privileges_update', methods: ['POST'])]
    public function updatePrivileges(Request $request, EntityManagerInterface $entityManager, SerializerInterface $serializer): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);

            if (!isset($data['userId']) || !isset($data['resourceId']) || !isset($data['isAllowed'])) {
                return new JsonResponse(['message' => 'Les données sont incomplètes.'], JsonResponse::HTTP_BAD_REQUEST);
            }

            $user = $this->userRepository->find($data['userId']);
            $resource = $this->resourceRepository->find($data['resourceId']);

            if (!$user || !$resource) {
                return new JsonResponse(['message' => 'Utilisateur ou ressource introuvable.'], JsonResponse::HTTP_BAD_REQUEST);
            }

            $userPrivilege = $this->userPrivilegeRepository->findOneBy([
                'user' => $user,
                'resource' => $resource,
            ]);

            if ($userPrivilege) {
                $userPrivilege->setAllowed($data['isAllowed']);
                $entityManager->flush();

                return new JsonResponse(
                    $serializer->normalize($userPrivilege, null, ['groups' => 'user_privilege:read']),
                    Response::HTTP_OK
                );
            } else {
                return new JsonResponse(['message' => 'Privilège utilisateur introuvable.'], JsonResponse::HTTP_BAD_REQUEST);
            }
        } catch (\Exception $e) {
            return new JsonResponse([
                'message' => 'Une erreur est survenue.',
                'error' => $e->getMessage()
            ], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
