<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Role;
use App\Entity\UserPrivilege;
use App\Repository\ResourceRepository;
use App\Repository\UserRepository;
use App\Repository\RoleRepository;
use App\Repository\UserPrivilegeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserController extends AbstractController
{
    private $passwordHasher;
    private $userRepository;
    private $roleRepository;
    private $resourceRepository;
    private $userPrivilegeRepository;

    public function __construct(
        UserPasswordHasherInterface $passwordHasher,
        UserRepository              $userRepository,
        RoleRepository              $roleRepository,
        ResourceRepository          $resourceRepository,
        UserPrivilegeRepository     $userPrivilegeRepository,
    )
    {
        $this->passwordHasher = $passwordHasher;
        $this->userRepository = $userRepository;
        $this->roleRepository = $roleRepository;
        $this->resourceRepository = $resourceRepository;
        $this->userPrivilegeRepository = $userPrivilegeRepository;
    }

    #[Route('/api/all/users', name: 'api_users', methods: ['GET'])]
    public function index(SerializerInterface $serializer): JsonResponse
    {
        $users = $this->userRepository->findAll();
        $data = array_map(function(User $user) {
            return [
                'id' => $user->getId(),
                'email' => $user->getEmail(),
                'userIdentifier' => $user->getUserIdentifier(),
                'password' => $user->getPassword(),
                'firstname' => $user->getFirstname(),
                'lastname' => $user->getLastname(),
                'roles' => implode(', ', $user->getRoles())
            ];
        }, $users);

        return new JsonResponse($data, Response::HTTP_OK);
    }


    #[Route('/api/users', name: 'api_userss', methods: ['GET'])]
    public function list(SerializerInterface $serializer): JsonResponse
    {
        $users = $this->userRepository->createQueryBuilder('u')
            ->leftJoin('u.roles', 'r')
            ->addSelect('r')
            ->getQuery()
            ->getResult();
        return new JsonResponse(
            $serializer->normalize($users),
            JsonResponse::HTTP_CREATED
        );
    }

    #[Route('/api/user', name: 'api_user_create', methods: ['POST'])]
    public function create(Request $request, EntityManagerInterface $entityManager, SerializerInterface $serializer): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!isset($data['email']) || empty($data['email'])) {
            return new JsonResponse(['message' => 'L\'email est requis.'], JsonResponse::HTTP_BAD_REQUEST);
        }

        if ($this->userRepository->findOneBy(['email' => $data['email']])) {
            return new JsonResponse(['message' => 'Cet email est déjà utilisé.'], JsonResponse::HTTP_BAD_REQUEST);
        }

        if (!isset($data['password']) || empty($data['password'])) {
            return new JsonResponse(['message' => 'Le mot de passe est requis.'], JsonResponse::HTTP_BAD_REQUEST);
        }

        if (!isset($data['roleId']) || empty($data['roleId'])) {
            return new JsonResponse(['message' => 'Le rôle est requis.'], JsonResponse::HTTP_BAD_REQUEST);
        }

        $role = $this->roleRepository->findOneBy(['id' => $data['roleId']]);

        if (!$role) {
            return new JsonResponse(['message' => 'Le rôle spécifié n\'existe pas.'], JsonResponse::HTTP_BAD_REQUEST);
        }

        $encodedPassword = $this->passwordHasher->hashPassword(new User(), $data['password']);

        $user = new User();
        $user->setEmail($data['email']);
        $user->setPassword($encodedPassword);
        $user->setFirstname($data['firstname'] ?? null);
        $user->setLastname($data['lastname'] ?? null);
        $user->addRole($role);

        $entityManager->persist($user);
        $entityManager->flush();

        $resources = $this->resourceRepository->findAll();

        $allowed = $role->getName() === 'admin' ? true : false;

        foreach ($resources as $resource) {
            $userPrivilege = new UserPrivilege();
            $userPrivilege->setUser($user);
            $userPrivilege->setResource($resource);
            $userPrivilege->setAllowed($allowed);

            $entityManager->persist($userPrivilege);
        }

        $entityManager->flush();


        return new JsonResponse(
            $serializer->normalize($user, null, ['groups' => 'user:read']),
            JsonResponse::HTTP_CREATED);
    }

    #[Route('/api/user/{id}', name: 'api_user_update', methods: ['PUT'])]
    public function update(
        int $id,
        Request $request,
        EntityManagerInterface $entityManager
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);

        $user = $this->userRepository->find($id);
        if (!$user) {
            return new JsonResponse(['message' => 'Utilisateur non trouvé.'], JsonResponse::HTTP_NOT_FOUND);
        }

        // Mise à jour de l'email
        if (isset($data['email']) && !empty($data['email'])) {
            $existingUser = $this->userRepository->findOneBy(['email' => $data['email']]);
            if ($existingUser && $existingUser->getId() !== $user->getId()) {
                return new JsonResponse(['message' => 'Cet email est déjà utilisé.'], JsonResponse::HTTP_BAD_REQUEST);
            }
            $user->setEmail($data['email']);
        }

        // Mise à jour du mot de passe
        if (isset($data['password']) && !empty($data['password'])) {
            $encodedPassword = $this->passwordHasher->hashPassword($user, $data['password']);
            $user->setPassword($encodedPassword);
        }

        // Mise à jour du prénom
        if (isset($data['firstname'])) {
            $user->setFirstname($data['firstname']);
        }

        // Mise à jour du nom de famille
        if (isset($data['lastname'])) {
            $user->setLastname($data['lastname']);
        }

        // Mise à jour des rôles
        /*if (isset($data['roleId']) && !empty($data['roleId'])) {
            $role = $this->roleRepository->find($data['roleId']);
            if (!$role) {
                return new JsonResponse(['message' => 'Le rôle spécifié n\'existe pas.'], JsonResponse::HTTP_BAD_REQUEST);
            }

            foreach ($user->getRoles() as $currentRoleName) {
                $roleEntity = $this->roleRepository->findOneBy(['name' => $currentRoleName]);
                if ($roleEntity) {
                    $user->removeRole($roleEntity);
                }
            }
            $user->addRole($role);
        }*/

        $entityManager->persist($user);
        $entityManager->flush();

        return new JsonResponse(['message' => 'Utilisateur mis à jour avec succès.'], JsonResponse::HTTP_OK);
    }



    #[Route('/api/user/{id}', name: 'api_user_delete', methods: ['DELETE'])]
    public function delete($id, EntityManagerInterface $entityManager): JsonResponse
    {
        $user = $this->userRepository->find($id);

        if (!$user) {
            throw new NotFoundHttpException('Utilisateur introuvable.');
        }

        $entityManager->remove($user);
        $entityManager->flush();

        return new JsonResponse(['message' => 'Utilisateur supprimé avec succès.'], JsonResponse::HTTP_OK);
    }

    #[Route('/api/user/{id}', name: 'api_user_show', methods: ['GET'])]
    public function show($id, SerializerInterface $serializer): JsonResponse
    {
        $user = $this->userRepository->find($id);

        if (!$user) {
            throw new NotFoundHttpException('Utilisateur introuvable.');
        }

        $data = $serializer->normalize($user, null, ['groups' => 'user:read']);

        return new JsonResponse($data, JsonResponse::HTTP_OK);
    }
}
