@php($announcement = getWebConfig(name: 'announcement'))

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
                    <a class="topbar-link d-none d-md-inline-block direction-ltr" href="tel:{{ $web_config['phone']->value }}">
                        <i class="fa fa-phone"></i> {{ $web_config['phone']->value }}
                    </a>
                </div>
            </div>

            <div>
                @php($currency_model = getWebConfig(name: 'currency_model'))

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

    {{-- ================= FIX VARIABLE ================= --}}
    @php
        $arrowDirection = Session::get('direction') === 'rtl' ? 'left' : 'right';
    @endphp
    {{-- ================================================= --}}

</header>

@push('script')
<script>
"use strict";

$(".category-menu")
    .find(".mega_menu")
    .parents("li")
    .addClass("has-sub-item")
    .find("> a")
    .append("<i class='czi-arrow-{{ $arrowDirection }}'></i>");
</script>
@endpush
