<x-layouts.back-office :title="__('back-office.title.staff')">
    <ul>
        @foreach ($staff as $member)
            <li>
                <span>{{ $member->profile->name->value }} · {{ $member->profile->email->value }} · {{ __('back-office.roles.'.$member->role->value) }}</span>
                @if ($acting->member->role->canManageStaff() && $member->id->value !== $acting->member->id->value)
                    <form method="POST" action="{{ route('back-office.staff.destroy', $member->id->value) }}">
                        @csrf
                        @method('DELETE')
                        <button type="submit" aria-label="{{ __('back-office.actions.remove') }} {{ $member->profile->name->value }}">{{ __('back-office.actions.remove') }}</button>
                    </form>
                @endif
            </li>
        @endforeach
    </ul>
    @if ($acting->member->role->canManageStaff())
        <form method="POST" action="{{ route('back-office.staff.store') }}">
            @csrf
            @foreach (['name' => 'text', 'email' => 'email', 'password' => 'password', 'password_confirmation' => 'password'] as $field => $type)
                <label for="{{ $field }}">{{ __('back-office.fields.'.$field) }}</label>
                <input id="{{ $field }}" name="{{ $field }}" type="{{ $type }}" value="{{ $type === 'password' ? '' : old($field) }}" required>
                @error($field)<p class="error">{{ $message }}</p>@enderror
            @endforeach
            <label for="role">{{ __('back-office.fields.role') }}</label>
            <select id="role" name="role">
                @foreach (\App\Domain\Tenancy\Role::cases() as $role)
                    <option value="{{ $role->value }}" @selected(old('role') === $role->value)>{{ __('back-office.roles.'.$role->value) }}</option>
                @endforeach
            </select>
            <button type="submit">{{ __('back-office.actions.add') }}</button>
        </form>
    @endif
</x-layouts.back-office>
