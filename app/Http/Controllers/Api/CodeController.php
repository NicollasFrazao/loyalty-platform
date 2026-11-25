<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Application\Customer\RequestCode;
use App\Domain\Customer\InvalidCustomerData;
use App\Domain\Customer\TooManyCodes;
use App\Http\Requests\Api\CodeRequest;
use Illuminate\Http\Response;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;

/**
 * Sends a one-time code to a phone, the first step of signing up or in.
 */
final readonly class CodeController
{
    /**
     * Seconds a refused phone is told to wait: the policy's window.
     */
    private const int RETRY_AFTER = 900;

    /**
     * Takes the use case.
     */
    public function __construct(private RequestCode $request) {}

    /**
     * Sends the code; the answer is the same whether the phone has an account or not.
     *
     * @throws ValidationException when the phone is not an international mobile number.
     * @throws TooManyRequestsHttpException when the phone asked for too many codes.
     */
    public function store(CodeRequest $request): Response
    {
        try {
            $this->request->handle($request->phone());
        } catch (InvalidCustomerData $error) {
            throw ValidationException::withMessages([$error->field->value => $error->getMessage()]);
        } catch (TooManyCodes $refused) {
            throw new TooManyRequestsHttpException(self::RETRY_AFTER, $refused->getMessage(), $refused);
        }

        return response()->noContent(Response::HTTP_ACCEPTED);
    }
}
