<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Library\Domains\Books\Models\Book;
use App\Library\Domains\Loans\Models\Loan;
use App\Library\Domains\Users\Enums\UserRole;
use App\Library\Domains\Users\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;

/**
 * A year of circulation history.
 *
 * The point is not volume but shape: the dashboard's monthly series needs
 * twelve months of movement, the popular-authors report needs some titles to
 * circulate far more than others, and the overdue report needs debts of
 * different ages. A uniform random scatter would produce flat charts.
 *
 * The generated data obeys the same rules the API enforces, so nothing here
 * describes a state the application would have refused to create:
 * `available_copies` always equals total minus open loans (ADR-4), no member
 * holds more than the configured limit (BR-LOAN-2), and no member holds the
 * same title twice at once (BR-LOAN-4).
 */
class CirculationSeeder extends Seeder
{
    private const MONTHS_OF_HISTORY = 12;

    /** Loans opened per month, oldest to newest — a library that grows. */
    private const VOLUME_BY_MONTH = [18, 22, 20, 26, 31, 28, 24, 33, 30, 36, 34, 40];

    /** @var array<int, int> book id => copies currently out */
    private array $openByBook = [];

    /** @var array<int, int> user id => open loans */
    private array $openByMember = [];

    /** @var array<string, true> "userId:bookId" of open loans */
    private array $openPairs = [];

    /** @var list<array<string, mixed>> */
    private array $pending = [];

    public function run(): void
    {
        $members = User::query()->role(UserRole::Member->value)->where('is_active', true)->get();
        $books = Book::query()->get();

        if ($members->isEmpty() || $books->isEmpty()) {
            return;
        }

        $limit = (int) config('library.loans.max_active_per_member');
        $period = (int) config('library.loans.default_period_days');
        $today = CarbonImmutable::today();

        // A handful of titles account for most of the circulation, the way they
        // do on a real shelf; this is what gives the reports a ranking.
        $popular = $books->take(12);

        foreach (self::VOLUME_BY_MONTH as $offset => $volume) {
            $monthStart = $today->startOfMonth()->subMonths(self::MONTHS_OF_HISTORY - 1 - $offset);
            $isCurrentMonth = $monthStart->isSameMonth($today);

            for ($i = 0; $i < $volume; $i++) {
                $book = random_int(1, 100) <= 55 ? $popular->random() : $books->random();
                $member = $members->random();

                if (! $this->canBorrow($member, $book, $limit)) {
                    continue;
                }

                $loanedAt = $this->dayWithin($monthStart, $today);
                $dueDate = $loanedAt->addDays($period);

                // Older loans have come back; the recent ones are what is still
                // out on the shelves.
                $stillOut = $isCurrentMonth
                    ? random_int(1, 100) <= 70
                    : ($offset >= self::MONTHS_OF_HISTORY - 2 && random_int(1, 100) <= 25);

                $this->pending[] = [
                    'user_id' => $member->id,
                    'book_id' => $book->id,
                    'loaned_at' => $loanedAt->toDateString(),
                    'due_date' => $dueDate->toDateString(),
                    'returned_at' => $stillOut ? null : $this->returnDate($loanedAt, $dueDate, $today),
                    'created_at' => $loanedAt,
                    'updated_at' => $loanedAt,
                ];

                if ($stillOut) {
                    $this->markOpen($member->id, $book->id);
                }
            }
        }

        $this->addOverdueBacklog($members, $books, $limit, $period, $today);

        Loan::query()->insert($this->pending);

        $this->reconcileAvailability();
    }

    /**
     * The rules that make a loan possible, checked before it is written so the
     * fixture cannot describe an impossible library.
     */
    private function canBorrow(User $member, Book $book, int $limit): bool
    {
        return ($this->openByBook[$book->id] ?? 0) < $book->total_copies
            && ($this->openByMember[$member->id] ?? 0) < $limit
            && ! isset($this->openPairs[$member->id.':'.$book->id]);
    }

    private function markOpen(int $memberId, int $bookId): void
    {
        $this->openByBook[$bookId] = ($this->openByBook[$bookId] ?? 0) + 1;
        $this->openByMember[$memberId] = ($this->openByMember[$memberId] ?? 0) + 1;
        $this->openPairs[$memberId.':'.$bookId] = true;
    }

    /**
     * Debts of different ages, so the overdue report has something to sort by
     * and the dashboard's overdue counter is not zero.
     *
     * @param  Collection<int, User>  $members
     * @param  Collection<int, Book>  $books
     */
    private function addOverdueBacklog(Collection $members, Collection $books, int $limit, int $period, CarbonImmutable $today): void
    {
        foreach ([3, 6, 9, 14, 21, 30, 45, 62] as $daysOverdue) {
            $member = $members->random();
            $book = $books->random();

            if (! $this->canBorrow($member, $book, $limit)) {
                continue;
            }

            $dueDate = $today->subDays($daysOverdue);
            $loanedAt = $dueDate->subDays($period);

            $this->pending[] = [
                'user_id' => $member->id,
                'book_id' => $book->id,
                'loaned_at' => $loanedAt->toDateString(),
                'due_date' => $dueDate->toDateString(),
                'returned_at' => null,
                'created_at' => $loanedAt,
                'updated_at' => $loanedAt,
            ];

            $this->markOpen($member->id, $book->id);
        }
    }

    private function dayWithin(CarbonImmutable $monthStart, CarbonImmutable $today): CarbonImmutable
    {
        $last = min($monthStart->endOfMonth()->day, $monthStart->isSameMonth($today) ? $today->day : 31);

        return $monthStart->setDay(random_int(1, max(1, $last)));
    }

    /**
     * Most books come back before the due date, some a little late.
     */
    private function returnDate(CarbonImmutable $loanedAt, CarbonImmutable $dueDate, CarbonImmutable $today): string
    {
        $returned = random_int(1, 100) <= 80
            ? $loanedAt->addDays(random_int(2, max(3, (int) $loanedAt->diffInDays($dueDate) - 1)))
            : $dueDate->addDays(random_int(1, 12));

        return $returned->isAfter($today) ? $today->toDateTimeString() : $returned->toDateTimeString();
    }

    /**
     * The counter is derived from the open loans that were just written, which
     * is the invariant the nightly reconciliation job checks in production.
     */
    private function reconcileAvailability(): void
    {
        Book::query()->each(function (Book $book): void {
            $book->available_copies = $book->total_copies - ($this->openByBook[$book->id] ?? 0);
            $book->save();
        });
    }
}
