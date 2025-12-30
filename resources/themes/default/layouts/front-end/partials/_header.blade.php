@php
    $announcement = getWebConfig('announcement');
@endphp

@if (isset($announcement) && isset($announcement['status']) && $announcement['status'] == 1)
    <div class="text-center position-relative px-4 py-1" id="announcement"
         style="background-color: {{ $announcement['color'] ?? '#000' }};
                color: {{ $announcement['text_color'] ?? '#fff' }}">
        <span>{{ $announcement['announcement'] ?? '' }}</span>
        <span class="__close-announcement web-announcement-slideUp">X</span>
    </div>
@endif

<header class="rtl __inline-10">
    <div class="topbar">
        <div class="container">

            {{-- PHONE --}}
            <div>
                <div class="topbar-text dropdown d-md-none ms-auto">
                    <a class="topbar-link direction-ltr" href="tel:{{ $web_config['phone']->value ?? '' }}">
                        <i class="fa fa-phone"></i> {{ $web_config['phone']->value ?? '' }}
                    </a>
                </div>

                <div class="d-none d-md-block mr-2 text-nowrap">
                    <a class="topbar-link d-none d-md-inline-block direction-ltr"
                       href="tel:{{ $web_config['phone']->value ?? '' }}">
                        <i class="fa fa-phone"></i> {{ $web_config['phone']->value ?? '' }}
                    </a>
                </div>
            </div>

            {{-- CURRENCY & LANGUAGE --}}
            <div>
                @php
                    $currency_model = getWebConfig('currency_model');
                @endphp

                @if ($currency_model === 'multi_currency')
                    <div class="topbar-text dropdown disable-autohide mr-4">
                        <a class="topbar-link dropdown-toggle" href="#" data-toggle="dropdown">
                            <span>{{ session('currency_code') }} {{ session('currency_symbol') }}</span>
                        </a>

                        <ul class="dropdown-menu dropdown-menu-{{ Session::get('direction') === 'rtl' ? 'right' : 'left' }}">
                            @foreach (\App\Models\Currency::where('status', 1)->get() as $currency)
                                <li class="dropdown-item cursor-pointer get-currency-change-function"
                                    data-code="{{ $currency->code }}">
                                    {{ $currency->name }}
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endif
            </div>

        </div>
    </div>

    {{-- LOGO --}}
    <div class="navbar navbar-expand-md navbar-light bg-light">
        <div class="container">
            <a class="navbar-brand" href="{{ route('home') }}">
                <img src="{{ getStorageImages(path: $web_config['web_logo'] ?? '', type: 'logo') }}"
                     alt="{{ $web_config['name']->value ?? 'Logo' }}">
            </a>
        </div>
    </div>
</header>

@push('script')
<script>
    "use strict";
    $(".category-menu").find(".mega_menu").parents("li")
        .addClass("has-sub-item")
        .find("> a")
        .append("<i class='czi-arrow-{{ Session::get('direction') === 'rtl' ? 'left' : 'right' }}'></i>");
</script>
@endpush
