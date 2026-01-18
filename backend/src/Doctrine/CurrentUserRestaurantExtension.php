<?php

declare(strict_types=1);

namespace App\Doctrine;

use ApiPlatform\Doctrine\Orm\Extension\QueryCollectionExtensionInterface;
use ApiPlatform\Doctrine\Orm\Extension\QueryItemExtensionInterface;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Metadata\Operation;
use App\Entity\Product;
use App\Entity\Shift;
use App\Entity\StockAlert;
use App\Entity\User;
use Doctrine\ORM\QueryBuilder;
use Symfony\Bundle\SecurityBundle\Security;

/**
 * Filters queries to only return data belonging to the current user's restaurant.
 * Admins bypass this filter and see all data.
 */
final class CurrentUserRestaurantExtension implements QueryCollectionExtensionInterface, QueryItemExtensionInterface
{
    public function __construct(
        private readonly Security $security
    ) {
    }

    public function applyToCollection(
        QueryBuilder $queryBuilder,
        QueryNameGeneratorInterface $queryNameGenerator,
        string $resourceClass,
        ?Operation $operation = null,
        array $context = []
    ): void {
        $this->addWhere($queryBuilder, $resourceClass);
    }

    public function applyToItem(
        QueryBuilder $queryBuilder,
        QueryNameGeneratorInterface $queryNameGenerator,
        string $resourceClass,
        array $identifiers,
        ?Operation $operation = null,
        array $context = []
    ): void {
        $this->addWhere($queryBuilder, $resourceClass);
    }

    private function addWhere(QueryBuilder $queryBuilder, string $resourceClass): void
    {
        // Only apply to entities that have a restaurant relationship
        if (!in_array($resourceClass, [Product::class, Shift::class, StockAlert::class, User::class], true)) {
            return;
        }

        /** @var User|null $user */
        $user = $this->security->getUser();

        if (null === $user) {
            return;
        }

        // Admins can see everything
        if (in_array('ROLE_ADMIN', $user->getRoles(), true)) {
            return;
        }

        // Get the user's restaurant
        $restaurant = $user->getRestaurant();

        if (null === $restaurant) {
            // User has no restaurant, return no results
            $queryBuilder->andWhere('1 = 0');
            return;
        }

        $rootAlias = $queryBuilder->getRootAliases()[0];

        // Filter by restaurant
        $queryBuilder->andWhere(sprintf('%s.restaurant = :current_restaurant', $rootAlias))
            ->setParameter('current_restaurant', $restaurant);
    }
}
