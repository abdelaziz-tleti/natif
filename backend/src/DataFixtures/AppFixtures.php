<?php

namespace App\DataFixtures;

use App\Entity\Restaurant;
use App\Entity\User;
use App\Entity\Product;
use App\Entity\Shift;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    public function __construct(
        private UserPasswordHasherInterface $passwordHasher
    ) {
    }

    public function load(ObjectManager $manager): void
    {
        // =============================================
        // Restaurant 1: La Bella Vita (Italian)
        // =============================================
        $restaurant1 = new Restaurant();
        $restaurant1->setName("La Bella Vita");
        $manager->persist($restaurant1);

        // Manager for Restaurant 1
        $manager1 = new User();
        $manager1->setEmail('manager@natif.com');
        $manager1->setRoles(['ROLE_MANAGER']);
        $manager1->setPassword($this->passwordHasher->hashPassword($manager1, 'password'));
        $manager1->setRestaurant($restaurant1);
        $manager->persist($manager1);

        // Employees for Restaurant 1
        $employee1 = new User();
        $employee1->setEmail('employee@natif.com');
        $employee1->setRoles(['ROLE_USER']);
        $employee1->setPassword($this->passwordHasher->hashPassword($employee1, 'password'));
        $employee1->setRestaurant($restaurant1);
        $manager->persist($employee1);

        $employee1b = new User();
        $employee1b->setEmail('marco@natif.com');
        $employee1b->setRoles(['ROLE_USER']);
        $employee1b->setPassword($this->passwordHasher->hashPassword($employee1b, 'password'));
        $employee1b->setRestaurant($restaurant1);
        $manager->persist($employee1b);

        // Products for Restaurant 1
        $products1 = [
            ['name' => 'Tomates', 'quantity' => 50, 'threshold' => 10, 'supplier' => 'Metro'],
            ['name' => 'Farine', 'quantity' => 5, 'threshold' => 20, 'supplier' => 'Metro'],
            ['name' => 'Huile d\'olive', 'quantity' => 15, 'threshold' => 5, 'supplier' => 'Carrefour Pro'],
            ['name' => 'Parmesan', 'quantity' => 8, 'threshold' => 3, 'supplier' => 'Italia Import'],
        ];

        foreach ($products1 as $p) {
            $product = new Product();
            $product->setName($p['name']);
            $product->setQuantity($p['quantity']);
            $product->setMinThreshold($p['threshold']);
            $product->setSupplier($p['supplier']);
            $product->setRestaurant($restaurant1);
            $manager->persist($product);
        }

        // Shifts for Restaurant 1 (today and tomorrow)
        $today = new \DateTimeImmutable('today');
        $tomorrow = new \DateTimeImmutable('tomorrow');

        $shift1 = new Shift();
        $shift1->setUser($employee1);
        $shift1->setRestaurant($restaurant1);
        $shift1->setStartTime($today->setTime(9, 0));
        $shift1->setEndTime($today->setTime(14, 0));
        $manager->persist($shift1);

        $shift2 = new Shift();
        $shift2->setUser($employee1b);
        $shift2->setRestaurant($restaurant1);
        $shift2->setStartTime($today->setTime(14, 0));
        $shift2->setEndTime($today->setTime(22, 0));
        $manager->persist($shift2);

        $shift3 = new Shift();
        $shift3->setUser($employee1);
        $shift3->setRestaurant($restaurant1);
        $shift3->setStartTime($tomorrow->setTime(10, 0));
        $shift3->setEndTime($tomorrow->setTime(18, 0));
        $manager->persist($shift3);

        // =============================================
        // Restaurant 2: Sushi Tokyo (Japanese)
        // =============================================
        $restaurant2 = new Restaurant();
        $restaurant2->setName("Sushi Tokyo");
        $manager->persist($restaurant2);

        // Manager for Restaurant 2
        $manager2 = new User();
        $manager2->setEmail('manager2@natif.com');
        $manager2->setRoles(['ROLE_MANAGER']);
        $manager2->setPassword($this->passwordHasher->hashPassword($manager2, 'password'));
        $manager2->setRestaurant($restaurant2);
        $manager->persist($manager2);

        // Employee for Restaurant 2
        $employee2 = new User();
        $employee2->setEmail('employee2@natif.com');
        $employee2->setRoles(['ROLE_USER']);
        $employee2->setPassword($this->passwordHasher->hashPassword($employee2, 'password'));
        $employee2->setRestaurant($restaurant2);
        $manager->persist($employee2);

        $employee2b = new User();
        $employee2b->setEmail('yuki@natif.com');
        $employee2b->setRoles(['ROLE_USER']);
        $employee2b->setPassword($this->passwordHasher->hashPassword($employee2b, 'password'));
        $employee2b->setRestaurant($restaurant2);
        $manager->persist($employee2b);

        // Products for Restaurant 2
        $products2 = [
            ['name' => 'Riz à sushi', 'quantity' => 100, 'threshold' => 30, 'supplier' => 'Asia Market'],
            ['name' => 'Saumon frais', 'quantity' => 20, 'threshold' => 10, 'supplier' => 'Poissonnerie Océan'],
            ['name' => 'Algue Nori', 'quantity' => 200, 'threshold' => 50, 'supplier' => 'Asia Market'],
            ['name' => 'Wasabi', 'quantity' => 3, 'threshold' => 5, 'supplier' => 'Asia Market'],
        ];

        foreach ($products2 as $p) {
            $product = new Product();
            $product->setName($p['name']);
            $product->setQuantity($p['quantity']);
            $product->setMinThreshold($p['threshold']);
            $product->setSupplier($p['supplier']);
            $product->setRestaurant($restaurant2);
            $manager->persist($product);
        }

        // Shifts for Restaurant 2
        $shift4 = new Shift();
        $shift4->setUser($employee2);
        $shift4->setRestaurant($restaurant2);
        $shift4->setStartTime($today->setTime(11, 0));
        $shift4->setEndTime($today->setTime(15, 0));
        $manager->persist($shift4);

        $shift5 = new Shift();
        $shift5->setUser($employee2b);
        $shift5->setRestaurant($restaurant2);
        $shift5->setStartTime($today->setTime(17, 0));
        $shift5->setEndTime($today->setTime(23, 0));
        $manager->persist($shift5);

        // =============================================
        // Admin (Global - No restaurant restriction)
        // =============================================
        $admin = new User();
        $admin->setEmail('admin@natif.com');
        $admin->setRoles(['ROLE_ADMIN']);
        $admin->setPassword($this->passwordHasher->hashPassword($admin, 'password'));
        $manager->persist($admin);

        $manager->flush();
    }
}
