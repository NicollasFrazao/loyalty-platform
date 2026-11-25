<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title }} · {{ config('app.name') }}</title>
    <style>
        body { margin: 0; font-family: system-ui, sans-serif; line-height: 1.5; background: #f8fafc; color: #0f172a; }
        header, main { max-width: 44rem; margin: 0 auto; padding: 1rem; }
        header { display: flex; flex-wrap: wrap; gap: 1rem; align-items: center; }
        header strong { margin-right: auto; }
        header form { margin: 0; }
        a { color: #0369a1; }
        a:focus-visible, button:focus-visible, input:focus-visible, select:focus-visible { outline: 3px solid #c2410c; outline-offset: 2px; }
        label { display: block; margin-top: .75rem; }
        input, select { display: block; width: 100%; max-width: 24rem; padding: .5rem; margin-top: .25rem; font-size: 1rem; }
        button { margin-top: .5rem; padding: .5rem 1rem; font-size: 1rem; min-height: 2.75rem; }
        ul { padding-left: 0; list-style: none; }
        li { display: flex; flex-wrap: wrap; gap: .5rem 1rem; align-items: center; justify-content: space-between; padding: .35rem 0; border-bottom: 1px solid #e2e8f0; }
        li form button { margin-top: 0; }
        .status { padding: .5rem; background: #dcfce7; color: #14532d; }
        .error { color: #b91c1c; font-size: .9rem; }
        .muted { color: #475569; }
    </style>
</head>
<body>
    @auth
        <header>
            <strong>{{ app(\App\Application\Tenancy\ActingStaff::class)->company->name->value }}</strong>
            <a href="{{ route('back-office') }}">{{ __('back-office.title.home') }}</a>
            <a href="{{ route('back-office.staff') }}">{{ __('back-office.title.staff') }}</a>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit">{{ __('back-office.actions.logout') }}</button>
            </form>
        </header>
    @endauth
    <main>
        <h1>{{ $title }}</h1>
        @if (session('status'))
            <p class="status" role="status">{{ session('status') }}</p>
        @endif
        {{ $slot }}
    </main>
</body>
</html>
