<?php

declare(strict_types=1);

namespace App\Http\Controllers\BackOffice;

use App\Application\Tenancy\ActingStaff;
use App\Application\Tenancy\AddStaffMember;
use App\Application\Tenancy\ListStaff;
use App\Domain\Tenancy\EmailTaken;
use App\Domain\Tenancy\InvalidStaffData;
use App\Domain\Tenancy\NotAllowed;
use App\Domain\Tenancy\StaffLimitReached;
use App\Http\Requests\BackOffice\StaffRequest;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

/**
 * The staff of the acting staff member's own company: the list and a new member. No route names a
 * company.
 */
final readonly class StaffController
{
    /**
     * Takes the staff use cases.
     */
    public function __construct(
        private ListStaff $list,
        private AddStaffMember $add,
    ) {}

    /**
     * The staff, with the form to add one.
     */
    public function index(ActingStaff $acting): View
    {
        return view('back-office.staff', ['acting' => $acting, 'staff' => $this->list->handle($acting)]);
    }

    /**
     * Adds a staff member.
     *
     * @throws AccessDeniedHttpException when the role cannot manage the staff.
     * @throws ValidationException when a value is refused, the address is taken or the plan is full.
     */
    public function store(StaffRequest $request, ActingStaff $acting): RedirectResponse
    {
        try {
            $this->add->handle($acting, $request->toInput());
        } catch (NotAllowed $refused) {
            throw new AccessDeniedHttpException($refused->getMessage(), $refused);
        } catch (InvalidStaffData $error) {
            throw ValidationException::withMessages([$error->field->value => __("back-office.errors.{$error->field->value}")]);
        } catch (EmailTaken) {
            throw ValidationException::withMessages(['email' => __('back-office.errors.taken')]);
        } catch (StaffLimitReached) {
            throw ValidationException::withMessages(['email' => __('back-office.errors.limit')]);
        }

        return redirect()->route('back-office.staff')->with('status', __('back-office.status.added'));
    }
}
