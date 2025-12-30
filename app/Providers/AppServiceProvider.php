<?php

namespace App\Providers;

use App\Models\FlashDealProduct;
use App\Traits\FileManagerTrait;
use App\Utils\Helpers;
use App\Enums\GlobalConstant;
use App\Models\Banner;
use App\Models\Currency;
use App\Models\Setting;
use App\Models\Shop;
use App\Models\SocialMedia;
use App\Models\Tag;
use App\Models\BusinessSetting;
use App\Models\Category;
use App\Models\FlashDeal;
use App\Models\Product;
use App\Traits\AddonHelper;
use App\Traits\ThemeHelper;
use App\Utils\ProductManager;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

ini_set('memory_limit',-1);
ini_set('upload_max_filesize','180M');
ini_set('post_max_size','200M');

class AppServiceProvider extends ServiceProvider
{

    use AddonHelper;
    use ThemeHelper;
    use FileManagerTrait;

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        if ($this->app->isLocal()) {
            $this->app->register(\Amirami\Localizator\ServiceProvider::class);
        }
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */

    public function boot()
    {
        if (!App::runningInConsole()) {
            Paginator::useBootstrap();

            Config::set('addon_admin_routes', $this->get_addon_admin_routes());
            Config::set('get_payment_publish_status', $this->get_payment_publish_status());

            Config::set('get_theme_routes', $this->get_theme_routes());

            // Initialize default web_config and language
            $web_config = [
                'primary_color' => '#007bff',
                'secondary_color' => '#6c757d',
                'primary_color_light' => '',
                'name' => (object)['value' => 'Company'],
                'phone' => (object)['value' => ''],
                'web_logo' => ['path' => ''],
                'mob_logo' => ['path' => ''],
                'fav_icon' => ['path' => ''],
                'email' => (object)['value' => ''],
                'about' => (object)['value' => ''],
                'footer_logo' => ['path' => ''],
                'copyright_text' => (object)['value' => ''],
                'decimal_point_settings' => 0,
                'seller_registration' => '0',
                'wallet_status' => 0,
                'loyalty_point_status' => 0,
                'guest_checkout_status' => 0,
                'featured_deals' => collect([]),
                'flash_deals' => null,
                'shops' => collect([]),
                'main_categories' => collect([]),
                'currencies' => collect([]),
                'social_media' => collect([]),
            ];
            $language = null;

            try {
                if (Schema::hasTable('business_settings')) {

                    $this->setStorageConnectionEnvironment();
                    $web = BusinessSetting::all();
                    $settings = Helpers::get_settings($web, 'colors');
                    $data = json_decode($settings['value'], true);

                    Cache::forget('inhouseShopInTemporaryClose');
                    $inhouseShopInTemporaryClose = Cache::get('inhouseShopInTemporaryClose');
                    if ($inhouseShopInTemporaryClose === null) {
                        $inhouseShopInTemporaryClose = Helpers::get_settings($web, 'temporary_close');
                        $inhouseShopInTemporaryClose = $inhouseShopInTemporaryClose ? json_decode($inhouseShopInTemporaryClose->value, true)['status'] : 0;
                        Cache::put('inhouseShopInTemporaryClose', $inhouseShopInTemporaryClose, (60 * 24));
                    }

                    $web_config = [
                        'primary_color' => $data['primary'] ?? '#007bff',
                        'secondary_color' => $data['secondary'] ?? '#6c757d',
                        'primary_color_light' => isset($data['primary_light']) ? $data['primary_light'] : '',
                        'name' => Helpers::get_settings($web, 'company_name') ?? (object)['value' => 'Company'],
                        'phone' => Helpers::get_settings($web, 'company_phone') ?? (object)['value' => ''],
                        'web_logo' => getWebConfig('company_web_logo') ?? ['path' => ''],
                        'mob_logo' => getWebConfig( 'company_mobile_logo') ?? ['path' => ''],
                        'fav_icon' => getWebConfig( 'company_fav_icon') ?? ['path' => ''],
                        'email' => Helpers::get_settings($web, 'company_email') ?? (object)['value' => ''],
                        'about' => Helpers::get_settings($web, 'about_us') ?? (object)['value' => ''],
                        'footer_logo' => getWebConfig('company_footer_logo') ?? ['path' => ''],
                        'copyright_text' => Helpers::get_settings($web, 'company_copyright_text') ?? (object)['value' => ''],
                        'decimal_point_settings' => !empty(\App\Utils\Helpers::get_business_settings('decimal_point_settings')) ? \App\Utils\Helpers::get_business_settings('decimal_point_settings') : 0,
                        'seller_registration' => BusinessSetting::where(['type' => 'seller_registration'])->first()->value ?? '0',
                        'wallet_status' => Helpers::get_business_settings('wallet_status') ?? 0,
                        'loyalty_point_status' => Helpers::get_business_settings('loyalty_point_status') ?? 0,
                        'guest_checkout_status' => Helpers::get_business_settings('guest_checkout') ?? 0,
                    ];

                    if ((!Request::is('admin') && !Request::is('admin/*') && !Request::is('seller/*') && !Request::is('vendor/*')) || Request::is('vendor/auth/registration/*') ) {
                        try {
                            $userId = Auth::guard('customer')->user() ? Auth::guard('customer')->id() : 0;
                            $flashDeal = ProductManager::getPriorityWiseFlashDealsProductsQuery(userId: $userId)['flashDeal'];

                            $featuredDealID = null;
                            $featuredDealProductIDs = [];
                            $featuredDealList = collect([]);
                            
                            if (Schema::hasTable('flash_deals') && Schema::hasTable('flash_deal_products')) {
                                $featuredDealID = FlashDeal::where(['deal_type' => 'feature_deal', 'status' => 1])->whereDate('start_date', '<=', date('Y-m-d'))
                                    ->whereDate('end_date', '>=', date('Y-m-d'))->pluck('id')->first();
                                $featuredDealProductIDs = $featuredDealID ? FlashDealProduct::where('flash_deal_id', $featuredDealID)->pluck('product_id')->toArray() : [];
                                if (!empty($featuredDealProductIDs) && Schema::hasTable('products')) {
                                    $featuredDealList = ProductManager::getPriorityWiseFeatureDealQuery(Product::active()->whereIn('id', $featuredDealProductIDs), dataLimit: 'all');
                                }
                            }

                            $shops = collect([]);
                            if (Schema::hasTable('shops') && Schema::hasTable('sellers')) {
                                $shops = Shop::whereHas('seller', function ($query) {
                                    return $query->approved();
                                })->take(9)->get();
                            }

                            $recaptcha = Helpers::get_business_settings('recaptcha');
                            $socials_login = Helpers::get_business_settings('social_login');
                            $socialLoginTextShowStatus = false;
                            if (is_array($socials_login)) {
                                foreach ($socials_login as $socialLoginService) {
                                    if (isset($socialLoginService) && $socialLoginService['status'] == true) {
                                        $socialLoginTextShowStatus = true;
                                    }
                                }
                            }

                            $popup_banner = null;
                            $header_banner = null;
                            if (Schema::hasTable('banners')) {
                                $popup_banner = Banner::inRandomOrder()->where('theme', theme_root_path())->where(['published' => 1, 'banner_type' => 'Popup Banner'])->first();
                                $header_banner = Banner::where('banner_type', 'Header Banner')->where('published', 1)->latest()->first();
                            }

                            $paymentGatewayPublishedStatus = 0;
                            $paymentPublishedStatus = config('get_payment_publish_status');
                            if (isset($paymentPublishedStatus[0]['is_published'])) {
                                $paymentGatewayPublishedStatus = $paymentPublishedStatus[0]['is_published'];
                            }

                            $paymentsGatewaysList = [];
                            if (Schema::hasTable('settings')) {
                                $paymentGatewaysQuery = Setting::whereIn('settings_type', ['payment_config'])->where('is_active', 1);
                                if ($paymentGatewayPublishedStatus == 1) {
                                    $paymentsGatewaysList = $paymentGatewaysQuery->select('key_name', 'additional_data')->get();
                                } else {
                                    $paymentsGatewaysList = $paymentGatewaysQuery->whereIn('key_name', GlobalConstant::DEFAULT_PAYMENT_GATEWAYS)->select('key_name', 'additional_data')->get();
                                }
                            }

                            $referralEarningStatus = 0;
                            if (Schema::hasTable('business_settings')) {
                                $referralEarningStatus = BusinessSetting::where('type', 'ref_earning_status')->first()->value ?? 0;
                            }

                            $currencies = collect([]);
                            $main_categories = collect([]);
                            $social_media = collect([]);
                            $discount_product = 0;
                            
                            if (Schema::hasTable('currencies')) {
                                $currencies = Currency::where('status', 1)->get();
                            }
                            if (Schema::hasTable('categories')) {
                                $main_categories = Category::with(['childes.childes'])->where('position', 0)->priority()->get();
                            }
                            if (Schema::hasTable('social_medias')) {
                                $social_media = SocialMedia::where('active_status', 1)->get();
                            }
                            if (Schema::hasTable('products')) {
                                $discount_product = Product::with(['reviews'])->active()->withCount('reviews')->where('discount', '!=', 0)->count();
                            }

                            $web_config += [
                                'cookie_setting' => Helpers::get_settings($web, 'cookie_setting'),
                                'announcement' => Helpers::get_business_settings('announcement'),
                                'currency_model' => Helpers::get_business_settings('currency_model'),
                                'currencies' => $currencies,
                                'main_categories' => $main_categories,
                                'business_mode' => Helpers::get_business_settings('business_mode'),
                                'social_media' => $social_media,
                                'ios' => Helpers::get_business_settings('download_app_apple_stroe'),
                                'android' => Helpers::get_business_settings('download_app_google_stroe'),
                                'refund_policy' => Helpers::get_business_settings('refund-policy'),
                                'return_policy' => Helpers::get_business_settings('return-policy'),
                                'cancellation_policy' => Helpers::get_business_settings('cancellation-policy'),
                                'flash_deals' => $flashDeal,
                                'featured_deals' => $featuredDealList,
                                'shops' => $shops,
                                'brand_setting' => Helpers::get_business_settings('product_brand'),
                                'discount_product' => $discount_product,
                                'recaptcha' => $recaptcha,
                                'socials_login' => $socials_login,
                                'social_login_text' => $socialLoginTextShowStatus,
                                'popup_banner' => $popup_banner,
                                'header_banner' => $header_banner,
                                'payments_list' => $paymentsGatewaysList,
                                'ref_earning_status' => $referralEarningStatus,
                            ];

                            if (theme_root_path() == "theme_fashion") {
                                $features_section = [
                                    'features_section_top' => [],
                                    'features_section_middle' => [],
                                    'features_section_bottom' => [],
                                ];
                                $tags = collect([]);
                                $total_discount_products = 0;
                                $products_stock_limit = 0;

                                if (Schema::hasTable('business_settings')) {
                                    $features_section = [
                                        'features_section_top' => BusinessSetting::where('type', 'features_section_top')->first() ? BusinessSetting::where('type', 'features_section_top')->first()->value : [],
                                        'features_section_middle' => BusinessSetting::where('type', 'features_section_middle')->first() ? BusinessSetting::where('type', 'features_section_middle')->first()->value : [],
                                        'features_section_bottom' => BusinessSetting::where('type', 'features_section_bottom')->first() ? BusinessSetting::where('type', 'features_section_bottom')->first()->value : [],
                                    ];
                                    $stockLimitSetting = Helpers::get_settings($web, 'stock_limit');
                                    $products_stock_limit = $stockLimitSetting ? $stockLimitSetting->value : 0;
                                }
                                if (Schema::hasTable('tags')) {
                                    $tags = Tag::orderBy('visit_count', 'desc')->take(15)->get();
                                }
                                if (Schema::hasTable('products')) {
                                    $total_discount_products = Product::active()->withCount('reviews')->where('discount', '!=', '0')->count();
                                }

                                $web_config += [
                                    'tags' => $tags,
                                    'features_section' => $features_section,
                                    'total_discount_products' => $total_discount_products,
                                    'products_stock_limit' => $products_stock_limit,
                                ];
                            }
                        } catch (\Exception $e) {
                            // Error loading additional config, continue with defaults
                        }
                    }

                    //language
                    $language = BusinessSetting::where('type', 'language')->first();

                    //currency
                    try {
                        \App\Utils\Helpers::currency_load();
                    } catch (\Exception $e) {
                        // Currency load failed, continue
                    }

                    Schema::defaultStringLength(191);
                }
            } catch (\Exception $exception) {
                // Error loading business settings, use defaults
            }

            // Always share web_config and language, even if tables don't exist
            View::share(['web_config' => $web_config, 'language' => $language]);
        }

        /**
         * Paginate a standard Laravel Collection.
         *
         * @param int $perPage
         * @param int $total
         * @param int $page
         * @param string $pageName
         * @return array
         */

        Collection::macro('paginate', function ($perPage, $total = null, $page = null, $pageName = 'page') {
            $page = $page ?: LengthAwarePaginator::resolveCurrentPage($pageName);

            return new LengthAwarePaginator(
                $this->forPage($page, $perPage),
                $total ?: $this->count(),
                $perPage,
                $page,
                [
                    'path' => LengthAwarePaginator::resolveCurrentPath(),
                    'pageName' => $pageName,
                ]
            );
        });

    }
}
