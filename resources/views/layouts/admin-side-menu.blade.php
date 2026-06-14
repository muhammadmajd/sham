<aside class="admin-side-menu">
    <div class="admin-side-brand">
        <div>
            <span>Sham VPN</span>
            <strong>Admin Panel</strong>
        </div>
    </div>

    <nav class="admin-side-nav" aria-label="Admin quick links">
        @foreach ($adminSideLinks as $link)
            @php
                $active = request()->routeIs($link['active']);
            @endphp

            <a href="{{ route($link['route']) }}"
                class="admin-side-link {{ $active ? 'is-active' : '' }}"
                style="--item-color: {{ $link['color'] }};">
                <span class="admin-side-link-main">
                    <span class="admin-side-dot"></span>
                    <span>{{ $link['label'] }}</span>
                </span>

                @if (!is_null($link['count']))
                    <span class="admin-side-count">{{ number_format($link['count']) }}</span>
                @endif
            </a>
        @endforeach
    </nav>
</aside>
