<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Library\Domains\Loans\Actions\CheckoutBookAction;
use App\Library\Domains\Loans\Actions\ReturnLoanAction;
use App\Library\Domains\Loans\Contracts\LoanRepositoryInterface;
use App\Library\Domains\Loans\DTOs\CheckoutData;
use App\Library\Domains\Loans\DTOs\LoanFilters;
use App\Library\Domains\Loans\Models\Loan;
use App\Library\Domains\Loans\Requests\IndexLoanRequest;
use App\Library\Domains\Loans\Requests\StoreLoanRequest;
use App\Library\Domains\Loans\Resources\LoanResource;
use App\Library\Domains\Users\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;

final class LoanController
{
    public function index(IndexLoanRequest $request, LoanRepositoryInterface $loans): AnonymousResourceCollection
    {
        Gate::authorize('viewAny', Loan::class);

        /** @var User $actor */
        $actor = $request->user();
        $filters = LoanFilters::fromRequest($request);

        // FR-LOAN-5: a member sees their own loans regardless of the parameters
        // they sent, so the scoping is applied after the filters are built.
        if (! $actor->isStaff()) {
            $filters = $filters->scopedToUser($actor->id);
        }

        return LoanResource::collection($loans->paginate($filters));
    }

    public function show(int $loan, LoanRepositoryInterface $loans): LoanResource
    {
        $model = $loans->findById($loan);

        // A loan that exists but belongs to somebody else answers exactly like
        // a missing one, so the endpoint cannot confirm existence.
        abort_if($model === null, Response::HTTP_NOT_FOUND);
        abort_if(Gate::denies('view', $model), Response::HTTP_NOT_FOUND);

        return new LoanResource($model);
    }

    public function store(StoreLoanRequest $request, CheckoutBookAction $checkout): JsonResponse
    {
        Gate::authorize('create', Loan::class);

        $loan = $checkout(CheckoutData::fromRequest($request));

        return (new LoanResource($loan))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED)
            ->header('Location', route('loans.show', $loan));
    }

    public function return(Loan $loan, ReturnLoanAction $returnLoan): LoanResource
    {
        Gate::authorize('return', Loan::class);

        return new LoanResource($returnLoan($loan));
    }

    public function forUser(IndexLoanRequest $request, User $user, LoanRepositoryInterface $loans): AnonymousResourceCollection
    {
        Gate::authorize('viewHistoryOf', [Loan::class, $user]);

        return LoanResource::collection(
            $loans->paginate(LoanFilters::fromRequest($request)->scopedToUser($user->id)),
        );
    }
}
