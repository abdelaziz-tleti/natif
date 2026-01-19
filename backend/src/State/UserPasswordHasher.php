<?php

declare(strict_types=1);

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\User;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

final class UserPasswordHasher implements ProcessorInterface
{
    public function __construct(
        #[Autowire(service: 'api_platform.doctrine.orm.state.persist_processor')]
        private readonly ProcessorInterface $persistProcessor,
        private readonly UserPasswordHasherInterface $passwordHasher,
        private readonly Security $security
    ) {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): mixed
    {
        if (!$data instanceof User) {
            return $this->persistProcessor->process($data, $operation, $uriVariables, $context);
        }

        if ($data->getPlainPassword()) {
            $hashed = $this->passwordHasher->hashPassword(
                $data,
                $data->getPlainPassword()
            );
            $data->setPassword($hashed);
            $data->eraseCredentials();
        }

        // Auto-assign restaurant from manager and set joining date
        /** @var User|null $currentUser */
        $currentUser = $this->security->getUser();
        if ($currentUser) {
            if ($data->getRestaurant() === null && $currentUser->getRestaurant() !== null) {
                $data->setRestaurant($currentUser->getRestaurant());
            }
        }

        if ($data->getJoiningDate() === null) {
            $data->setJoiningDate(new \DateTimeImmutable());
        }

        return $this->persistProcessor->process($data, $operation, $uriVariables, $context);
    }
}
