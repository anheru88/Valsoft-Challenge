<?php

declare(strict_types=1);

namespace App\Library\Domains\Loans\Exceptions;

use App\Library\Shared\Domain\DomainException;

/**
 * BR-LOAN-9: loans go to active member accounts only. Staff who want to borrow
 * use a member account, which keeps circulation statistics honest.
 */
final class BorrowerNotEligibleException extends DomainException
{
    /**
     * Named errorCode rather than code: Exception already owns $code, and it is
     * an int there.
     */
    private function __construct(string $message, private readonly string $errorCode)
    {
        parent::__construct($message);
    }

    public static function notAMember(): self
    {
        return new self('Loans can only be issued to member accounts.', 'LOAN_USER_NOT_MEMBER');
    }

    public static function inactive(): self
    {
        return new self('This account is deactivated and cannot borrow.', 'LOAN_USER_INACTIVE');
    }

    public function errorCode(): string
    {
        return $this->errorCode;
    }
}
