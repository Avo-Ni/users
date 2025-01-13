<?php

namespace App\Service;

use App\Repository\ResourceRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RouterInterface;

class ResourceAccessService
{
    private $resourceRepository;
    private $router;

    public function __construct(ResourceRepository $resourceRepository, RouterInterface $router)
    {
        $this->resourceRepository = $resourceRepository;
        $this->router = $router;
    }

    public function getResourceForRequest(Request $request): ?\App\Entity\Resource
    {
        $path = $request->getPathInfo();

        return $this->resourceRepository->findOneBy(['path' => $path]);
    }
}