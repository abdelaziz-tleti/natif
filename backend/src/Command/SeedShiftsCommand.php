<?php

namespace App\Command;

use App\Entity\User;
use App\Entity\Shift;
use App\Entity\Restaurant;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:seed-shifts',
    description: 'Seed demo shifts for the week',
)]
class SeedShiftsCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $entityManager
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        // Get restaurant and employees
        $restaurant = $this->entityManager->getRepository(Restaurant::class)->findOneBy([]);
        if (!$restaurant) {
            $io->error('No restaurant found. Please create a restaurant first.');
            return Command::FAILURE;
        }

        $employees = $this->entityManager->getRepository(User::class)->findAll();
        if (count($employees) < 2) {
            $io->error('Need at least 2 employees. Please create users first.');
            return Command::FAILURE;
        }

        // Clear existing shifts
        $this->entityManager->createQuery('DELETE FROM App\Entity\Shift')->execute();

        // Get current week's Monday
        $now = new \DateTime();
        $dayOfWeek = $now->format('N'); // 1 (Monday) to 7 (Sunday)
        $monday = (clone $now)->modify('-' . ($dayOfWeek - 1) . ' days')->setTime(0, 0);

        $io->info("Creating shifts for week starting: " . $monday->format('Y-m-d'));

        $shiftsData = [
            // Lundi
            ['day' => 0, 'type' => 'morning', 'start' => '08:00', 'end' => '15:00', 'employees' => [0, 1]],
            ['day' => 0, 'type' => 'evening', 'start' => '17:00', 'end' => '23:00', 'employees' => [1, 2]],

            // Mardi
            ['day' => 1, 'type' => 'morning', 'start' => '08:00', 'end' => '15:00', 'employees' => [0, 2]],
            ['day' => 1, 'type' => 'evening', 'start' => '17:00', 'end' => '23:00', 'employees' => [0, 1]],

            // Mercredi
            ['day' => 2, 'type' => 'morning', 'start' => '08:00', 'end' => '15:00', 'employees' => [1, 2]],
            ['day' => 2, 'type' => 'evening', 'start' => '17:00', 'end' => '23:00', 'employees' => [0, 1, 2]],

            // Jeudi
            ['day' => 3, 'type' => 'morning', 'start' => '08:00', 'end' => '15:00', 'employees' => [0]],
            ['day' => 3, 'type' => 'evening', 'start' => '17:00', 'end' => '23:00', 'employees' => [1, 2]],

            // Vendredi
            ['day' => 4, 'type' => 'morning', 'start' => '08:00', 'end' => '15:00', 'employees' => [0, 1, 2]],
            ['day' => 4, 'type' => 'evening', 'start' => '17:00', 'end' => '00:00', 'employees' => [0, 1, 2]],

            // Samedi
            ['day' => 5, 'type' => 'morning', 'start' => '09:00', 'end' => '16:00', 'employees' => [1, 2]],
            ['day' => 5, 'type' => 'evening', 'start' => '18:00', 'end' => '01:00', 'employees' => [0, 1, 2]],

            // Dimanche - Fermé
        ];

        $count = 0;
        foreach ($shiftsData as $data) {
            $shiftDate = (clone $monday)->modify('+' . $data['day'] . ' days');

            [$startHour, $startMin] = explode(':', $data['start']);
            [$endHour, $endMin] = explode(':', $data['end']);

            $startTime = (clone $shiftDate)->setTime((int) $startHour, (int) $startMin);
            $endTime = (clone $shiftDate)->setTime((int) $endHour, (int) $endMin);

            // If end time is after midnight (00:00 ou 01:00)
            if ($endHour === '00' || $endHour === '01') {
                $endTime->modify('+1 day');
            }

            $shift = new Shift();
            $shift->setStartTime(\DateTimeImmutable::createFromMutable($startTime));
            $shift->setEndTime(\DateTimeImmutable::createFromMutable($endTime));
            $shift->setType($data['type']);
            $shift->setRestaurant($restaurant);

            // Add employees
            foreach ($data['employees'] as $empIndex) {
                if (isset($employees[$empIndex])) {
                    $shift->addUser($employees[$empIndex]);
                }
            }

            $this->entityManager->persist($shift);
            $count++;
        }

        $this->entityManager->flush();

        $io->success("Created {$count} demo shifts for the week!");
        $io->info("Go to Manager > Planning to see the visual schedule.");

        return Command::SUCCESS;
    }
}
