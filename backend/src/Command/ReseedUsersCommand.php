<?php

namespace App\Command;

use App\Entity\User;
use App\Entity\Restaurant;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand(
    name: 'app:reseed-users',
    description: 'Reseeds default users',
)]
class ReseedUsersCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private UserPasswordHasherInterface $passwordHasher
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        // Cleanup users
        $this->entityManager->createQuery('DELETE FROM App\Entity\User')->execute();

        $restaurants = $this->entityManager->getRepository(Restaurant::class)->findAll();
        if (empty($restaurants)) {
            $io->error('No restaurants found. Please create restaurants first.');
            return Command::FAILURE;
        }

        $italia = $restaurants[0];

        $users = [
            [
                'phone' => '0101010101',
                'email' => 'admin@natif.com',
                'password' => 'password',
                'roles' => ['ROLE_ADMIN'],
                'firstName' => 'Iheb',
                'lastName' => 'Abdel',
                'restaurant' => null
            ],
            [
                'phone' => '0606060606',
                'email' => 'manager@natif.com',
                'password' => 'password',
                'roles' => ['ROLE_MANAGER'],
                'firstName' => 'Mario',
                'lastName' => 'Rossi',
                'restaurant' => $italia
            ],
            [
                'phone' => '0707070707',
                'email' => 'employee@natif.com',
                'password' => 'password',
                'roles' => ['ROLE_USER'],
                'firstName' => 'Luigi',
                'lastName' => 'Verdi',
                'restaurant' => $italia
            ]
        ];

        foreach ($users as $userData) {
            $user = new User();
            $user->setPhone($userData['phone']);
            $user->setEmail($userData['email']);
            $user->setRoles($userData['roles']);
            $user->setFirstName($userData['firstName']);
            $user->setLastName($userData['lastName']);
            $user->setRestaurant($userData['restaurant']);
            $user->setJoiningDate(new \DateTimeImmutable());

            $hashedPassword = $this->passwordHasher->hashPassword($user, $userData['password']);
            $user->setPassword($hashedPassword);

            $this->entityManager->persist($user);
        }

        $this->entityManager->flush();

        $io->success('Users re-seeded successfully.');

        return Command::SUCCESS;
    }
}
