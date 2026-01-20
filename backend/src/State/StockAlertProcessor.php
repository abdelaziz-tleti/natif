<?php

declare(strict_types=1);

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\Notification;
use App\Entity\StockAlert;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

/**
 * Processor for StockAlert that auto-assigns the current user and their restaurant.
 * Also generates notifications for managers.
 */
final class StockAlertProcessor implements ProcessorInterface
{
    public function __construct(
        #[Autowire(service: 'api_platform.doctrine.orm.state.persist_processor')]
        private readonly ProcessorInterface $persistProcessor,
        private readonly Security $security,
        private readonly EntityManagerInterface $entityManager
    ) {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): mixed
    {
        if (!$data instanceof StockAlert) {
            return $this->persistProcessor->process($data, $operation, $uriVariables, $context);
        }

        /** @var User|null $user */
        $user = $this->security->getUser();

        if ($user !== null) {
            // Auto-assign the current user as reporter
            if ($data->getReportedBy() === null) {
                $data->setReportedBy($user);
            }

            // Auto-assign restaurant from user
            if ($data->getRestaurant() === null && $user->getRestaurant() !== null) {
                $data->setRestaurant($user->getRestaurant());
            }
        }

        $result = $this->persistProcessor->process($data, $operation, $uriVariables, $context);

        // Notify managers
        if ($data->getRestaurant() !== null) {
            $this->notifyManagers($data);
        }

        return $result;
    }

    private function notifyManagers(StockAlert $alert): void
    {
        $restaurant = $alert->getRestaurant();

        // Find all managers for this restaurant
        $users = $this->entityManager->getRepository(User::class)->findBy(['restaurant' => $restaurant]);

        foreach ($users as $user) {
            if (in_array('ROLE_MANAGER', $user->getRoles(), true)) {
                $notification = new Notification();
                $notification->setUser($user);
                $notification->setTitle('Low Stock Alert: ' . $alert->getProduct()?->getName());
                $notification->setMessage(sprintf(
                    'Employee %s reported a stock issue for %s. Message: %s',
                    $alert->getReportedBy()?->getEmail() ?? 'Unknown',
                    $alert->getProduct()?->getName(),
                    $alert->getMessage() ?? 'No comment'
                ));

                $this->entityManager->persist($notification);
            }
        }

        $this->entityManager->flush();
    }
}
