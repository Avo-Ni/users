<?php

namespace App\Command;

use App\Entity\User;
use App\Entity\Resource;
use App\Entity\UserPrivilege;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use App\Repository\ResourceRepository;

#[AsCommand(
    name: 'app:create-user-with-full-access',
    description: 'Creates a new user with full access to all resources',
)]
class CreateAdminCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private UserPasswordHasherInterface $passwordHasher,
        private ResourceRepository $resourceRepository
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $email = 'testuser@example.com';
        $firstName = 'Test';
        $lastName = 'User';
        $password = 'password123';

        try {
            $user = new User();
            $user->setEmail($email);
            $user->setFirstName($firstName);
            $user->setLastName($lastName);

            $hashedPassword = $this->passwordHasher->hashPassword(
                $user,
                $password
            );
            $user->setPassword($hashedPassword);

            $this->entityManager->persist($user);
            $this->entityManager->flush();
            $resources = $this->resourceRepository->findAll();
            foreach ($resources as $resource) {
                $userPrivilege = new UserPrivilege();
                $userPrivilege->setUser($user);
                $userPrivilege->setResource($resource);
                $userPrivilege->setAllowed(true);

                $this->entityManager->persist($userPrivilege);
            }
            $this->entityManager->flush();

            $io->success('User created successfully with full access to all resources.');

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $io->error('An error occurred: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
