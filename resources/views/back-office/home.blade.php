@php($company = $acting->company)
<x-layouts.back-office :title="__('back-office.title.home')">
    <p class="muted">{{ __('back-office.plan', ['plan' => $company->plan->value, 'staff' => $company->plan->staffLimit()]) }}</p>
    @error('module')<p class="error" role="alert">{{ $message }}</p>@enderror
    <ul>
        @foreach ($modules as $module)
            <li>
                <span>{{ $module->value }}:
                    @if (! $company->plan->allows($module))
                        {{ __('back-office.module.not_in_plan') }}
                    @else
                        {{ $company->hasEnabled($module) ? __('back-office.module.on') : __('back-office.module.off') }}
                    @endif
                </span>
                @if ($company->plan->allows($module) && $acting->member->role->canConfigure())
                    <form method="POST" action="{{ route('back-office.modules', $module->value) }}">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="enabled" value="{{ $company->hasEnabled($module) ? 0 : 1 }}">
                        <button type="submit">{{ $company->hasEnabled($module) ? __('back-office.actions.disable') : __('back-office.actions.enable') }}</button>
                    </form>
                @endif
            </li>
        @endforeach
    </ul>
</x-layouts.back-office>
