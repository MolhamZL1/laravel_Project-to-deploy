@php
    $announcement = getWebConfig('announcement');
@endphp

@if (!empty($announcement) && $announcement['status'] == 1)
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
            </div>

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
                            @php
                                $currencies = collect([]);
                                try {
                                    if (\Illuminate\Support\Facades\Schema::hasTable('currencies')) {
                                        $currencies = \App\Models\Currency::where('status', 1)->get();
                                    }
                                } catch (\Exception $e) {
                                    $currencies = collect([]);
                                }
                            @endphp
                            @foreach ($currencies as $currency)
                                <li class="dropdown-item cursor-pointer"
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

    @php
        $arrowDirection = Session::get('direction') === 'rtl' ? 'left' : 'right';
    @endphp
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
