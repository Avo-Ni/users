<?php

namespace App\Security\Voter;

use App\Entity\User;
use App\Entity\Resource;
use App\Repository\UserPrivilegeRepository;
use App\Service\ResourceAccessService;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\HttpFoundation\RequestStack;

class ResourceAccessVoter extends Voter
{
    public function __construct(
        private UserPrivilegeRepository $userPrivilegeRepository,
        private ResourceAccessService $resourceAccessService,
        private RequestStack $requestStack
    ) {}

    protected function supports(string $attribute, $subject): bool
    {
        return $attribute === 'ACCESS_RESOURCE';
    }

    protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();

        if (!$user instanceof User) {
            return false;
        }

        $request = $this->requestStack->getCurrentRequest();
        if (!$request) {
            return false;
        }

        $resource = $this->resourceAccessService->getResourceForRequest($request);

        if (!$resource) {
            return false;
        }

        return $this->userPrivilegeRepository->checkUserResourceAccess(
            $user->getId(),
            $resource->getId()
        );
    }
}
