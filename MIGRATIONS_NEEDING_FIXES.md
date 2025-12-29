# Migrations Needing Safety Checks

## Summary
**Total migrations without safety checks: 140+**

All these migrations use `Schema::table()` without checking if the table exists first. This will cause Railway deployment to crash if tables don't exist when migrations run.

## Fixed Migrations ✅
1. `2021_02_24_154706_add_deal_type_to_flash_deals.php` - Fixed (creates table if doesn't exist)
2. `2021_03_03_204349_add_cm_firebase_token_to_users.php` - Fixed
3. `2021_04_17_134848_add_column_to_order_details_stock.php` - Fixed
4. `2021_05_12_155401_add_auth_token_seller.php` - Fixed

## Remaining Migrations That Need Fixes ⚠️

### 2021 Migrations (25 files)
- `2021_06_03_104531_ex_rate_update.php`
- `2021_06_03_222413_amount_withdraw_req.php`
- `2021_06_04_154501_seller_wallet_withdraw_bal.php`
- `2021_06_04_195853_product_dis_tax.php`
- `2021_06_29_212549_add_active_col_user_table.php`
- `2021_06_30_212619_add_col_to_contact.php`
- `2021_07_04_182331_add_col_seller_sales_commission.php`
- `2021_08_07_190655_add_seo_columns_to_products.php`
- `2021_08_07_210808_add_col_to_shops_table.php`
- `2021_08_14_205216_change_product_price_col_type.php`
- `2021_08_16_201505_change_order_price_col.php`
- `2021_08_16_201552_change_order_details_price_col.php`
- `2021_08_17_213934_change_col_type_seller_earning_history.php`
- `2021_08_17_214109_change_col_type_admin_earning_history.php`
- `2021_08_17_214232_change_col_type_admin_wallet.php`
- `2021_08_17_214405_change_col_type_seller_wallet.php`
- `2021_08_22_184834_add_publish_to_products_table.php`
- `2021_09_08_211832_add_social_column_to_users_table.php`
- `2021_09_13_165535_add_col_to_user.php`
- `2021_09_19_061647_add_limit_to_coupons_table.php`
- `2021_09_20_020716_add_coupon_code_to_orders_table.php`
- `2021_09_23_003059_add_gst_to_sellers_table.php`
- `2021_10_03_194334_add_col_order_table.php`
- `2021_10_03_200536_add_shipping_cost.php`
- `2021_10_04_153201_add_col_to_order_table.php`
- `2021_10_07_172701_add_col_cart_shop_info.php`
- `2021_10_07_185416_add_user_table_email_verified.php`
- `2021_10_11_192739_add_transaction_amount_table.php`
- `2021_10_11_200850_add_order_verification_code.php`
- `2021_10_12_083241_add_col_to_order_transaction.php`
- `2021_10_12_084440_add_seller_id_to_order.php`
- `2021_10_12_102853_change_col_type.php`
- `2021_10_12_110434_add_col_to_admin_wallet.php`
- `2021_10_12_110829_add_col_to_seller_wallet.php`
- `2021_10_13_091801_add_col_to_admin_wallets.php`
- `2021_10_13_092000_add_col_to_seller_wallets_tax.php`
- `2021_10_13_165947_rename_and_remove_col_seller_wallet.php`
- `2021_10_13_170258_rename_and_remove_col_admin_wallet.php`
- `2021_10_14_061603_column_update_order_transaction.php`
- `2021_10_15_103339_remove_col_from_seller_wallet.php`
- `2021_10_15_104419_add_id_col_order_tran.php`
- `2021_10_15_213454_update_string_limit.php`
- `2021_10_16_234037_change_col_type_translation.php`
- `2021_10_16_234329_change_col_type_translation_1.php`
- `2021_10_27_091250_add_shipping_address_in_order.php`
- `2021_11_20_043814_change_pass_reset_email_col.php`
- `2021_11_25_062242_add_auth_token_delivery_man.php`
- `2021_11_27_043405_add_deliveryman_in_order_table.php`
- `2021_11_27_051512_add_fcm_col_for_delivery_man.php`
- `2021_12_15_123216_add_columns_to_banner.php`

### 2022 Migrations (40+ files)
- `2022_01_04_100543_add_order_note_to_orders_table.php`
- `2022_01_10_034952_add_lat_long_to_shipping_addresses_table.php`
- `2022_01_11_040755_add_is_billing_to_shipping_addresses_table.php`
- `2022_01_11_053404_add_billing_to_orders_table.php`
- `2022_01_11_234310_add_firebase_toke_to_sellers_table.php`
- `2022_01_16_121801_change_colu_type.php`
- `2022_01_22_101601_change_cart_col_type.php`
- `2022_01_23_031359_add_column_to_orders_table.php`
- `2022_01_28_235054_add_status_to_admins_table.php`
- `2022_02_01_214654_add_pos_status_to_sellers_table.php`
- `2022_02_11_225355_add_checked_to_orders_table.php`
- `2022_02_14_115757_add_refund_request_to_order_details_table.php`
- `2022_02_15_092604_add_order_details_id_to_transactions_table.php`
- `2022_02_24_091236_add_multiple_column_to_refund_requests_table.php`
- `2022_03_01_121420_add_refund_id_to_refund_transactions_table.php`
- `2022_03_10_091943_add_priority_to_categories_table.php`
- `2022_03_14_074413_add_four_column_to_products_table.php`
- `2022_03_15_105838_add_shipping_to_carts_table.php`
- `2022_03_16_070327_add_shipping_type_to_orders_table.php`
- `2022_03_17_070200_add_delivery_info_to_orders_table.php`
- `2022_03_18_143339_add_shipping_type_to_carts_table.php`
- `2022_04_12_233704_change_column_to_products_table.php`
- `2022_04_15_235820_add_provider.php`
- `2022_05_12_104511_add_two_column_to_users_table.php`
- `2022_05_26_044016_add_user_type_to_password_resets_table.php`
- `2022_07_21_101659_add_code_to_products_table.php`
- `2022_07_26_103744_add_notification_count_to_notifications_table.php`
- `2022_07_31_031541_add_minimum_order_qty_to_products_table.php`
- `2022_08_11_172839_add_product_type_and_digital_product_type_and_digital_file_ready_to_products.php`
- `2022_08_11_173941_add_product_type_and_digital_product_type_and_digital_file_to_order_details.php`
- `2022_08_20_094225_add_product_type_and_digital_product_type_and_digital_file_ready_to_carts_table.php`
- `2022_10_04_160234_add_banking_columns_to_delivery_men_table.php`
- `2022_10_04_184506_add_deliverymanid_column_to_withdraw_requests_table.php`
- `2022_10_11_103011_add_deliverymans_columns_to_chattings_table.php`
- `2022_10_11_144902_add_deliverman_id_cloumn_to_reviews_table.php`
- `2022_10_18_084245_add_deliveryman_charge_and_expected_delivery_date.php`
- `2022_10_29_182930_add_is_pause_cause_to_orders_table.php`
- `2022_10_31_150604_add_address_phone_country_code_column_to_delivery_men_table.php`
- `2022_11_05_185726_add_order_id_to_reviews_table.php`
- `2022_11_08_132745_change_transaction_note_type_to_withdraw_requests_table.php`
- `2022_11_10_193747_chenge_order_amount_seller_amount_admin_commission_delivery_charge_tax_toorder_transactions_table.php`
- `2022_12_17_035723_few_field_add_to_coupons_table.php`
- `2022_12_26_231606_add_coupon_discount_bearer_and_admin_commission_to_orders.php`

### 2023 Migrations (50+ files)
- `2023_01_04_003034_alter_billing_addresses_change_zip.php`
- `2023_01_05_121600_change_id_to_transactions_table.php`
- `2023_02_02_152248_add_tax_model_to_products_table.php`
- `2023_02_02_152718_add_tax_model_to_order_details_table.php`
- `2023_02_02_171034_add_tax_type_to_carts.php`
- `2023_02_06_124447_add_color_image_column_to_products_table.php`
- `2023_02_07_175939_add_withdrawal_method_id_and_withdrawal_method_fields_to_withdraw_requests_table.php`
- `2023_02_08_143314_add_vacation_start_and_vacation_end_and_vacation_not_column_to_shops_table.php`
- `2023_02_09_104656_add_payment_by_and_payment_not_to_orders_table.php`
- `2023_03_27_150723_add_expires_at_to_phone_or_email_verifications.php`
- `2023_04_17_111249_add_bottom_banner_to_shops_table.php`
- `2023_05_16_131006_add_expires_at_to_password_resets.php`
- `2023_05_17_044243_add_visit_count_to_tags_table.php`
- `2023_05_18_000403_add_title_and_subtitle_and_background_color_and_button_text_to_banners_table.php`
- `2023_05_21_111300_add_login_hit_count_and_is_temp_blocked_and_temp_block_time_to_users_table.php`
- `2023_05_21_111600_add_login_hit_count_and_is_temp_blocked_and_temp_block_time_to_phone_or_email_verifications_table.php`
- `2023_05_21_112215_add_login_hit_count_and_is_temp_blocked_and_temp_block_time_to_password_resets_table.php`
- `2023_06_04_210726_attachment_lenght_change_to_reviews_table.php`
- `2023_06_05_115153_add_referral_code_and_referred_by_to_users_table.php`
- `2023_06_21_002658_add_offer_banner_to_shops_table.php`
- `2023_07_31_111419_add_minimum_order_amount_to_sellers_table.php`
- `2023_08_07_131013_add_is_guest_column_to_carts_table.php`
- `2023_08_12_215659_add_is_guest_column_to_orders_table.php`
- `2023_08_12_215933_add_is_guest_column_to_shipping_addresses_table.php`
- `2023_08_15_000957_add_email_column_toshipping_address_table.php`
- `2023_08_17_222330_add_identify_related_columns_to_admins_table.php`
- `2023_08_20_230624_add_sent_by_and_send_to_in_notifications_table.php`
- `2023_08_21_042331_add_theme_to_banners_table.php`
- `2023_08_24_150009_add_free_delivery_over_amount_and_status_to_seller_table.php`
- `2023_08_26_161214_add_is_shipping_free_to_orders_table.php`
- `2023_08_26_173523_add_payment_method_column_to_wallet_transactions_table.php`
- `2023_08_26_204653_add_verification_status_column_to_orders_table.php`
- `2023_09_03_212200_add_free_delivery_responsibility_column_to_orders_table.php`
- `2023_09_23_153314_add_shipping_responsibility_column_to_orders_table.php`
- `2023_09_27_191638_add_attachment_column_to_support_ticket_convs_table.php`
- `2023_10_01_205117_add_attachment_column_to_chattings_table.php`
- `2023_10_21_113354_add_app_language_column_to_users_table.php`
- `2023_10_21_123433_add_app_language_column_to_sellers_table.php`
- `2023_10_21_124657_add_app_language_column_to_delivery_men_table.php`
- `2023_10_22_130225_add_attachment_to_support_tickets_table.php`
- `2023_10_25_113233_make_message_nullable_in_chattings_table.php`
- `2023_10_30_152005_make_attachment_column_type_change_to_reviews_table.php`

### 2024 Migrations (10+ files)
- `2024_01_14_192546_add_slug_to_shops_table.php`
- `2024_01_25_175421_add_country_code_to_emergency_contacts_table.php`
- `2024_02_01_200417_add_denied_count_and_approved_count_to_refund_requests_table.php`
- `2024_03_11_130425_add_seen_notification_and_notification_receiver_to_chattings_table.php`
- `2024_03_12_123322_update_images_column_in_refund_requests_table.php`
- `2024_03_21_134659_change_denied_note_column_type_to_text.php`
- `2024_04_17_102137_add_is_checked_column_to_carts_table.php`
- `2024_04_24_093932_add_type_to_help_topics_table.php`
- `2024_05_20_163043_add_image_alt_text_to_brands_table.php`
- `2024_05_27_184401_add_digital_product_file_types_and_digital_product_extensions_to_products_table.php`
- `2024_07_03_130217_add_storage_type_columns_to_product_table.php`
- `2024_07_03_153301_add_icon_storage_type_to_catogory_table.php`
- `2024_07_03_171214_add_image_storage_type_to_brands_table.php`
- `2024_07_03_185048_add_storage_type_columns_to_shop_table.php`

## Fix Pattern

Each migration needs to be updated from:
```php
public function up()
{
    Schema::table('table_name', function (Blueprint $table) {
        $table->string('column_name');
    });
}
```

To:
```php
public function up()
{
    if (Schema::hasTable('table_name')) {
        Schema::table('table_name', function (Blueprint $table) {
            if (!Schema::hasColumn('table_name', 'column_name')) {
                $table->string('column_name');
            }
        });
    }
}
```

## Recommendation

**Option 1:** Fix them as they fail during migration (current approach)
- Pros: Only fix what's needed
- Cons: Slower, requires multiple deployments

**Option 2:** Fix all 140+ migrations now
- Pros: One-time fix, safer for Railway
- Cons: Time-consuming, but can be automated

**Option 3:** Use a script to fix them all automatically
- I can create a better script that handles edge cases more carefully

