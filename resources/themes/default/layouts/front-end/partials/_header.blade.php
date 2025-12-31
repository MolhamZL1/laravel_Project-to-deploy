@php
    $announcement = getWebConfig(name: 'announcement');
@endphp

@if (isset($announcement) && $announcement['status'] == 1)
    <div class="text-center position-relative px-4 py-1" id="announcement"
         style="background-color: {{ $announcement['color'] }}; color: {{ $announcement['text_color'] }}">
        <span>{{ $announcement['announcement'] }}</span>
        <span class="__close-announcement web-announcement-slideUp">X</span>
    </div>
@endif


<header class="rtl __inline-10">
    <div class="topbar">
        <div class="container">

            <div>
                <div class="topbar-text dropdown d-md-none ms-auto">
                    <a class="topbar-link direction-ltr" href="tel:{{ $web_config['phone']->value }}">
                        <i class="fa fa-phone"></i> {{ $web_config['phone']->value }}
                    </a>
                </div>

                <div class="d-none d-md-block mr-2 text-nowrap">
                    <a class="topbar-link d-none d-md-inline-block direction-ltr"
                       href="tel:{{ $web_config['phone']->value }}">
                        <i class="fa fa-phone"></i> {{ $web_config['phone']->value }}
                    </a>
                </div>
            </div>

            <div>
                @php
                    $currency_model = getWebConfig(name: 'currency_model');
                @endphp

                @if ($currency_model == 'multi_currency')
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

                <div class="topbar-text dropdown __language-bar text-capitalize">
                    <a class="topbar-link dropdown-toggle" href="#" data-toggle="dropdown">
                        @foreach (json_decode($language['value'], true) as $data)
                            @if ($data['code'] == getDefaultLanguage())
                                <img width="20"
                                     src="{{ theme_asset(path: 'public/assets/front-end/img/flags/'.$data['code'].'.png') }}"
                                     alt="{{ $data['name'] }}">
                                {{ $data['name'] }}
                            @endif
                        @endforeach
                    </a>

                    <ul class="dropdown-menu dropdown-menu-{{ Session::get('direction') === 'rtl' ? 'right' : 'left' }}">
                        @foreach (json_decode($language['value'], true) as $data)
                            @if ($data['status'] == 1)
                                <li class="change-language"
                                    data-action="{{ route('change-language') }}"
                                    data-language-code="{{ $data['code'] }}">
                                    <a class="dropdown-item" href="javascript:void(0)">
                                        <img width="20"
                                             src="{{ theme_asset(path: 'public/assets/front-end/img/flags/'.$data['code'].'.png') }}"
                                             alt="{{ $data['name'] }}">
                                        {{ $data['name'] }}
                                    </a>
                                </li>
                            @endif
                        @endforeach
                    </ul>
                </div>

            </div>
        </div>
    </div>

    {{-- بقية الهيدر لم تتغير (HTML فقط) --}}
</header>

@push('script')
<script>
    "use strict";
    $(".category-menu").find(".mega_menu").parents("li")
        .addClass("has-sub-item").find("> a")
        .append("<i class='czi-arrow-{{ Session::get('direction') === 'rtl' ? 'left' : 'right' }}'></i>");
</script>
@endpush
