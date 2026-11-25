<x-layouts.back-office :title="__('back-office.title.login')">
    <form method="POST" action="{{ route('login') }}">
        @csrf
        <label for="email">{{ __('back-office.fields.email') }}</label>
        <input id="email" name="email" type="email" value="{{ old('email') }}" autocomplete="email" required>
        @error('email')<p class="error">{{ $message }}</p>@enderror
        <label for="password">{{ __('back-office.fields.password') }}</label>
        <input id="password" name="password" type="password" autocomplete="current-password" required>
        <button type="submit">{{ __('back-office.actions.login') }}</button>
    </form>
</x-layouts.back-office>
