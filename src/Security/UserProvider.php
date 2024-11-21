<?php

namespace App\Security;

use App\Entity\Participant;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class UserProvider implements UserProviderInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager
    ) {}

    public function loadUserByIdentifier(string $identifier): UserInterface
    {
        $participant = $this->entityManager->createQueryBuilder()
            ->select('p')
            ->from(Participant::class, 'p')
            ->where('p.email = :identifier')
            ->orWhere('p.pseudo = :identifier')
            ->setParameter('identifier', $identifier)
            ->getQuery()
            ->getOneOrNullResult();

        if (!$participant) {
            throw new UserNotFoundException();
        }

        return $participant;
    }

    public function refreshUser(UserInterface $user): UserInterface
    {
        return $this->loadUserByIdentifier($user->getUserIdentifier());
    }

    public function supportsClass(string $class): bool
    {
        return Participant::class === $class;
    }
}
