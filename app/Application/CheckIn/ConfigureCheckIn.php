<?php

declare(strict_types=1);

namespace App\Application\CheckIn;

use App\Application\Tenancy\ActingStaff;
use App\Domain\CheckIn\CheckInRepository;
use App\Domain\CheckIn\CheckInSettings;
use App\Domain\CheckIn\GeoPoint;
use App\Domain\CheckIn\InvalidCheckInSettings;
use App\Domain\CheckIn\NoGeofence;
use App\Domain\CheckIn\RadiusGeofence;
use App\Domain\Tenancy\NotAllowed;

/**
 * The check-in rules of the acting staff member's company: owners and managers change them (use
 * case).
 */
final readonly class ConfigureCheckIn
{
    /**
     * Takes the check-ins.
     */
    public function __construct(private CheckInRepository $checkIns) {}

    /**
     * The company's rules.
     */
    public function show(ActingStaff $acting): CheckInSettings
    {
        return $this->checkIns->settings($acting->company->id);
    }

    /**
     * Replaces the company's rules.
     *
     * @throws NotAllowed when the role cannot configure the company.
     * @throws InvalidCheckInSettings when a value is refused.
     */
    public function update(ActingStaff $acting, CheckInSettingsInput $input): CheckInSettings
    {
        $acting->ensureCanConfigure();
        $fence = $input->fence instanceof FenceInput
            ? RadiusGeofence::around(GeoPoint::at($input->fence->latitude, $input->fence->longitude), $input->fence->radiusMetres)
            : new NoGeofence;
        $settings = CheckInSettings::of($input->cooldownMinutes, $input->requiresConfirmation, $fence);
        $this->checkIns->saveSettings($acting->company->id, $settings);

        return $settings;
    }
}
