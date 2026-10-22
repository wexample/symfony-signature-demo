<?php

namespace Wexample\SymfonySignatureDemo\Service;

use Wexample\SymfonySignature\Interface\SignerAccountResolverInterface;
use Wexample\SymfonyUserDemo\Entity\DemoUser;
use Wexample\SymfonyUserDemo\Repository\DemoUserRepository;

/**
 * The accounts of the user demo, found by their address: a signer added as
 * demo-admin@example.com signs signed in.
 */
class DemoSignerAccountResolver implements SignerAccountResolverInterface
{
    public function __construct(
        private readonly DemoUserRepository $users,
    ) {
    }

    public function findAccount(string $email): ?object
    {
        return $this->users->findOneByUserIdentifier(mb_strtolower(trim($email)));
    }

    public function describe(object $account): ?array
    {
        return $account instanceof DemoUser
            ? [(string) $account->getEmail(), $account->getDisplayName()]
            : null;
    }
}
