<?php

use App\Entity\User;
use App\Entity\Restaurant;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\ConsoleOutput;

require __DIR__ . '/vendor/autoload.php';

$kernel = new App\Kernel('dev', true);
$kernel->boot();

$container = $kernel->getContainer();
$entityManager = $container->get('doctrine')->getManager();
$passwordHasher = $container->get('security.password_hasher');

// Cleanup users
$entityManager->createQuery('DELETE FROM App\Entity\User')->execute();

$restaurants = $entityManager->getRepository(Restaurant::class)->findAll();
$italia = $restaurants[0];
$sushi = $restaurants[1] ?? $italia;

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

    $hashedPassword = $passwordHasher->hashPassword($user, $userData['password']);
    $user->setPassword($hashedPassword);

    $entityManager->persist($user);
}

$entityManager->flush();

echo "Users re-seeded successfully.\n";
