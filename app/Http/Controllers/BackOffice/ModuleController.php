<?php

declare(strict_types=1);

namespace App\Http\Controllers\BackOffice;

use App\Application\Tenancy\ActingStaff;
use App\Application\Tenancy\ConfigureModules;
use App\Domain\Tenancy\Module;
use App\Domain\Tenancy\ModuleNotInPlan;
use App\Domain\Tenancy\NotAllowed;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

/**
 * The back office's home: the acting staff member's company, its plan and its modules, which owners
 * and managers turn on and off.
 */
final readonly class ModuleController
{
    /**
     * Takes the use case that configures the modules.
     */
    public function __construct(private ConfigureModules $modules) {}

    /**
     * The company with every module and whether it is on.
     */
    public function index(ActingStaff $acting): View
    {
        return view('back-office.home', ['acting' => $acting, 'modules' => Module::cases()]);
    }

    /**
     * Turns the module on or off (`enabled` = 1 or 0).
     *
     * @throws AccessDeniedHttpException when the role cannot configure the modules.
     * @throws ValidationException when the plan does not include the module.
     */
    public function update(Request $request, ActingStaff $acting, Module $module): RedirectResponse
    {
        try {
            $request->boolean('enabled') ? $this->modules->enable($acting, $module) : $this->modules->disable($acting, $module);
        } catch (NotAllowed $refused) {
            throw new AccessDeniedHttpException($refused->getMessage(), $refused);
        } catch (ModuleNotInPlan) {
            throw ValidationException::withMessages(['module' => __('back-office.errors.not_in_plan')]);
        }

        return redirect()->route('back-office')->with('status', __('back-office.status.modules'));
    }
}
