<?php

declare(strict_types=1);

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\StockAlert;
use App\Entity\User;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

/**
 * Processor for StockAlert that auto-assigns the current user and their restaurant.
 */
final class StockAlertProcessor implements ProcessorInterface
{
    public function __construct(
        #[Autowire(service: 'api_platform.doctrine.orm.state.persist_processor')]
        private readonly ProcessorInterface $persistProcessor,
        private readonly Security $security
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

        return $this->persistProcessor->process($data, $operation, $uriVariables, $context);
    }
}
