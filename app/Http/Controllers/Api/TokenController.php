<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Application\Customer\CustomerSession;
use App\Domain\Customer\CodeRefused;
use App\Domain\Customer\CustomerField;
use App\Domain\Customer\InvalidCustomerData;
use App\Http\Requests\Api\TokenRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\ValidationException;

/**
 * The customer app's sessions: a code for a token, and signing out of the device.
 */
final readonly class TokenController
{
    /**
     * Takes the use case.
     */
    public function __construct(private CustomerSession $session) {}

    /**
     * A token for the customer the code opens, signing them up the first time.
     *
     * @throws ValidationException when the phone is not valid or the code is refused.
     */
    public function store(TokenRequest $request): JsonResponse
    {
        try {
            $token = $this->session->open($request->phone(), $request->code(), $request->device());
        } catch (InvalidCustomerData $error) {
            throw ValidationException::withMessages([$error->field->value => $error->getMessage()]);
        } catch (CodeRefused $refused) {
            throw ValidationException::withMessages([CustomerField::Code->value => $refused->getMessage()]);
        }

        return response()->json(['token' => $token, 'token_type' => 'Bearer'], Response::HTTP_CREATED);
    }

    /**
     * Revokes the token the request was signed with.
     */
    public function destroy(Request $request): Response
    {
        $this->session->close((string) $request->bearerToken());

        return response()->noContent();
    }
}
