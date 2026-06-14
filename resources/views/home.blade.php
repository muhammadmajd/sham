@php
    $dir = in_array($locale, ['ar', 'fa'], true) ? 'rtl' : 'ltr';
    $title = $content->{'title_' . $locale} ?: $content->title_en ?: config('app.name', 'Sham VPN');
    $description = $content->{'description_' . $locale} ?: $content->description_en;
    $languageLabels = ['en' => 'English', 'ar' => 'العربية', 'fa' => 'فارسی', 'ru' => 'Русский'];
@endphp

<!DOCTYPE html>
<html lang="{{ $locale }}" dir="{{ $dir }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title }}</title>
    <link rel="icon" type="image/png" href="{{ asset('images/icon.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('images/icon.png') }}">
    <style>
        body { margin: 0; font-family: Arial, sans-serif; background: #f4f7fb; color: #111827; }
        a { text-decoration: none; }
        .site-header { display: flex; justify-content: space-between; align-items: center; gap: 1rem; padding: 18px clamp(16px, 4vw, 56px); background: #fff; border-bottom: 1px solid #e5e7eb; }
        .brand { display: inline-flex; align-items: center; gap: .75rem; color: #111827; font-weight: 800; }
        .brand img { width: 42px; height: 42px; object-fit: contain; }
        .lang { display: flex; flex-wrap: wrap; gap: .45rem; }
        .lang a { padding: .45rem .65rem; border-radius: 999px; color: #475569; background: #f1f5f9; font-size: .85rem; font-weight: 700; }
        .lang a.active { color: #fff; background: #111827; }
        .hero { padding: clamp(42px, 7vw, 90px) clamp(16px, 4vw, 56px) 32px; }
        .hero-inner { max-width: 1120px; margin: 0 auto; display: grid; grid-template-columns: 1.1fr .9fr; gap: 2rem; align-items: center; }
        h1 { margin: 0; font-size: clamp(2.2rem, 6vw, 4.75rem); line-height: 1.02; letter-spacing: 0; }
        .description { margin: 1.2rem 0 0; max-width: 680px; color: #475569; font-size: 1.1rem; line-height: 1.75; white-space: pre-line; }
        .hero-card { background: #111827; color: #fff; border-radius: 18px; padding: 2rem; min-height: 260px; display: grid; place-items: center; text-align: center; box-shadow: 0 24px 50px rgba(15, 23, 42, .22); }
        .hero-card img { width: 120px; height: 120px; object-fit: contain; margin: 0 auto 1rem; }
        .hero-card strong { display: block; font-size: 1.35rem; }
        .versions { max-width: 1120px; margin: 0 auto; padding: 22px clamp(16px, 4vw, 56px) 56px; }
        .versions h2 { margin: 0 0 1rem; font-size: 1.55rem; }
        .version-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(230px, 1fr)); gap: 1rem; }
        .version-card { background: #fff; border: 1px solid #e5e7eb; border-radius: 14px; padding: 1.15rem; box-shadow: 0 1px 2px rgba(15, 23, 42, .04); }
        .version-card h3 { margin: 0; font-size: 1.1rem; }
        .version-number { color: #64748b; margin: .35rem 0 1rem; font-weight: 700; }
        .notes { min-height: 42px; color: #475569; font-size: .92rem; line-height: 1.5; white-space: pre-line; }
        .download { display: inline-flex; margin-top: 1rem; padding: .68rem .9rem; border-radius: 10px; color: #fff; background: #2563eb; font-weight: 800; }
        .disabled { display: inline-flex; margin-top: 1rem; padding: .68rem .9rem; border-radius: 10px; color: #64748b; background: #f1f5f9; font-weight: 800; }
        .login-link { color: #2563eb; font-weight: 800; }
        @media (max-width: 800px) {
            .hero-inner { grid-template-columns: 1fr; }
            .site-header { align-items: flex-start; flex-direction: column; }
        }
    </style>
</head>
<body>
    <header class="site-header">
        <a class="brand" href="{{ route('home') }}">
            <img src="{{ asset('images/icon.png') }}" alt="{{ config('app.name', 'Sham VPN') }}">
            <span>{{ config('app.name', 'Sham VPN') }}</span>
        </a>
        <nav class="lang" aria-label="Language">
            @foreach ($languageLabels as $code => $label)
                <a class="{{ $locale === $code ? 'active' : '' }}" href="{{ route('home', ['lang' => $code]) }}">{{ $label }}</a>
            @endforeach
            <a class="login-link" href="{{ route('login') }}">Login</a>
        </nav>
    </header>

    @if ($content->is_published)
        <section class="hero">
            <div class="hero-inner">
                <div>
                    <h1>{{ $title }}</h1>
                    <div class="description">{{ $description }}</div>
                </div>
                <div class="hero-card">
                    <div>
                        <img src="{{ asset('images/icon.png') }}" alt="{{ $title }}">
                        <strong>{{ $title }}</strong>
                    </div>
                </div>
            </div>
        </section>

        <section class="versions">
            <h2>{{ $locale === 'ar' ? 'إصدارات التطبيق' : ($locale === 'fa' ? 'نسخه‌های برنامه' : ($locale === 'ru' ? 'Версии приложения' : 'App Versions')) }}</h2>
            <div class="version-grid">
                @forelse ($versions as $version)
                    @php
                        $notes = $version->{'notes_' . $locale} ?: $version->notes_en;
                    @endphp
                    <article class="version-card">
                        <h3>{{ $version->platform_label }}</h3>
                        <div class="version-number">v{{ $version->version }}</div>
                        <div class="notes">{{ $notes }}</div>
                        @if ($version->download_url)
                            <a class="download" href="{{ $version->download_url }}" download>Download</a>
                        @else
                            <span class="disabled">Coming soon</span>
                        @endif
                    </article>
                @empty
                    <article class="version-card">
                        <h3>No versions available</h3>
                        <div class="notes">Please check back later.</div>
                    </article>
                @endforelse
            </div>
        </section>
    @else
        <section class="hero">
            <div class="hero-inner">
                <div>
                    <h1>{{ config('app.name', 'Sham VPN') }}</h1>
                    <div class="description">Home page content is currently hidden.</div>
                </div>
            </div>
        </section>
    @endif
</body>
</html>
