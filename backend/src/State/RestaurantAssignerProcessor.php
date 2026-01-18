<?php

declare(strict_types=1);

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\Product;
use App\Entity\Shift;
use App\Entity\User;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

/**
 * Automatically assigns the current user's restaurant to new Products and Shifts.
 */
final class RestaurantAssignerProcessor implements ProcessorInterface
{
    public function __construct(
        #[Autowire(service: 'api_platform.doctrine.orm.state.persist_processor')]
        private readonly ProcessorInterface $persistProcessor,
        private readonly Security $security
    ) {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): mixed
    {
        // Only process Product and Shift entities
        if (!$data instanceof Product && !$data instanceof Shift) {
            return $this->persistProcessor->process($data, $operation, $uriVariables, $context);
        }

        /** @var User|null $user */
        $user = $this->security->getUser();

        if ($user === null) {
            return $this->persistProcessor->process($data, $operation, $uriVariables, $context);
        }

        // If the entity doesn't already have a restaurant assigned, use the current user's
        $currentRestaurant = null;

        if ($data instanceof Product) {
            $currentRestaurant = $data->getRestaurant();
        } elseif ($data instanceof Shift) {
            // For Shift, the restaurant comes from the employee
            // But we can also directly assign if the entity supports it
            $currentRestaurant = $data->getRestaurant();
        }

        // If no restaurant is set, assign from current user
        if ($currentRestaurant === null && $user->getRestaurant() !== null) {
            if ($data instanceof Product) {
                $data->setRestaurant($user->getRestaurant());
            } elseif ($data instanceof Shift) {
                $data->setRestaurant($user->getRestaurant());
            }
        }

        return $this->persistProcessor->process($data, $operation, $uriVariables, $context);
    }
}
