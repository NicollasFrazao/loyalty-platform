<?php

declare(strict_types=1);

namespace App\Http\Controllers\BackOffice;

use App\Application\Tenancy\ActingStaff;
use App\Application\Tenancy\RemoveStaffMember;
use App\Domain\Tenancy\NotAllowed;
use App\Domain\Tenancy\StaffMemberId;
use App\Domain\Tenancy\StaffMemberNotFound;
use Illuminate\Http\RedirectResponse;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Removing a staff member of the acting staff member's own company; one of another company is not
 * found.
 */
final readonly class StaffRemovalController
{
    /**
     * Takes the removal use case.
     */
    public function __construct(private RemoveStaffMember $remove) {}

    /**
     * Removes the staff member.
     *
     * @throws AccessDeniedHttpException when the role cannot manage the staff, or it is the acting one.
     * @throws NotFoundHttpException when no member of the company has the id.
     */
    public function destroy(ActingStaff $acting, int $member): RedirectResponse
    {
        try {
            $this->remove->handle($acting, new StaffMemberId($member));
        } catch (NotAllowed $refused) {
            throw new AccessDeniedHttpException($refused->getMessage(), $refused);
        } catch (StaffMemberNotFound $missing) {
            throw new NotFoundHttpException($missing->getMessage(), $missing);
        }

        return redirect()->route('back-office.staff')->with('status', __('back-office.status.removed'));
    }
}
