<?php

declare(strict_types=1);

namespace Resonance;

/**
 * @template TSubject of CrudActionSubjectInterface
 */
interface CrudActionGateInterface
{
    /**
     * @param TSubject $subject
     */
    public function can(
        ?UserInterface $user,
        CrudActionSubjectInterface $subject,
        CrudAction $crudAction,
    ): bool;
}