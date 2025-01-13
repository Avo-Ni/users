<?php

namespace App\Entity;

use App\Repository\UserPrivilegeRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: UserPrivilegeRepository::class)]
#[ORM\Table(name: "user_privileges")]
#[ORM\UniqueConstraint(name: "user_resource_unique", columns: ["user_id", "resource_id"])]
class UserPrivilege
{
    #[ORM\Id]
    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: "userPrivileges")]
    #[ORM\JoinColumn(nullable: false, onDelete: "CASCADE")]
    private ?User $user = null;

    #[ORM\Id]
    #[ORM\ManyToOne(targetEntity: Resource::class, inversedBy: "userPrivileges")]
    #[ORM\JoinColumn(nullable: false, onDelete: "CASCADE")]
    private ?Resource $resource = null;

    #[ORM\Column(type: "boolean")]
    private ?bool $isAllowed = null;

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;

        return $this;
    }

    public function getResource(): ?Resource
    {
        return $this->resource;
    }

    public function setResource(?Resource $resource): static
    {
        $this->resource = $resource;

        return $this;
    }

    public function isAllowed(): ?bool
    {
        return $this->isAllowed;
    }

    public function setAllowed(bool $isAllowed): static
    {
        $this->isAllowed = $isAllowed;

        return $this;
    }
}
