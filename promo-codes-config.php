<?php
/**
 * Promo Codes Configuration
 * Centralized promo code management for Black Friday banners
 *
 * Add this file via Code Snippets WP Lite Plugin
 * Location: Everywhere (to make codes available globally)
 */

// ==================== PROMO CODE CONFIGURATION ====================
// Define promo codes for different product groups
// These can be easily updated without touching the banner code

global $whx_promo_codes;
$whx_promo_codes = array(
    // DOMAIN PROMOTIONS
    'domains' => array(
        'code' => 'BFDOMAIN50',
        'discount' => '50',
        'description' => 'Get 50% off domain registrations',
        'url' => '/cloud/cart.php?a=add&domain=register&promocode=BFDOMAIN50',
    ),
    'domains_ke' => array(
        'code' => 'KEDOMAIN30',
        'discount' => '30',
        'description' => 'Save 30% on .KE domains',
        'url' => '/ke-domain?promocode=KEDOMAIN30',
    ),
    'domains_transfer' => array(
        'code' => 'TRANSFER25',
        'discount' => '25',
        'description' => '25% off domain transfers',
        'url' => '/cloud/cart.php?a=add&domain=transfer&promocode=TRANSFER25',
    ),
    'domains_free' => array(
        'code' => 'FREEDOMAIN',
        'discount' => '100',
        'description' => 'Free domain with hosting',
        'url' => '/domains/free?promocode=FREEDOMAIN',
    ),

    // HOSTING PROMOTIONS
    'hosting' => array(
        'code' => 'BFHOST85',
        'discount' => '85',
        'description' => 'Up to 85% off web hosting',
        'url' => '/hosting?promocode=BFHOST85',
    ),
    'hosting_cpanel' => array(
        'code' => 'CPANEL70',
        'discount' => '70',
        'description' => '70% off cPanel hosting',
        'url' => '/hosting/cpanel?promocode=CPANEL70',
    ),
    'hosting_cyberpanel' => array(
        'code' => 'CYBER65',
        'discount' => '65',
        'description' => '65% off CyberPanel hosting',
        'url' => '/hosting/cyberpanel?promocode=CYBER65',
    ),
    'hosting_windows' => array(
        'code' => 'WINDOWS60',
        'discount' => '60',
        'description' => '60% off Windows hosting',
        'url' => '/hosting/windows?promocode=WINDOWS60',
    ),
    'hosting_reseller' => array(
        'code' => 'RESELLER55',
        'discount' => '55',
        'description' => '55% off reseller hosting',
        'url' => '/hosting/reseller?promocode=RESELLER55',
    ),
    'hosting_free' => array(
        'code' => 'FREEHOST',
        'discount' => '100',
        'description' => 'Free hosting plan available',
        'url' => '/hosting/free',
    ),
    'hosting_dedicated' => array(
        'code' => 'DEDICATED40',
        'discount' => '40',
        'description' => '40% off dedicated servers',
        'url' => '/hosting/dedicated-servers?promocode=DEDICATED40',
    ),
    'hosting_email' => array(
        'code' => 'EMAIL50',
        'discount' => '50',
        'description' => '50% off email hosting',
        'url' => '/hosting/email?promocode=EMAIL50',
    ),

    // VPS & CLOUD PROMOTIONS
    'vps' => array(
        'code' => 'BFVPS65',
        'discount' => '65',
        'description' => '65% off VPS hosting',
        'url' => '/vps-hosting?promocode=BFVPS65',
    ),
    'vps_managed' => array(
        'code' => 'MANAGEDVPS60',
        'discount' => '60',
        'description' => '60% off managed VPS',
        'url' => '/vps-hosting/managed?promocode=MANAGEDVPS60',
    ),

    // SSL & SECURITY
    'ssl' => array(
        'code' => 'SSL50',
        'discount' => '50',
        'description' => '50% off SSL certificates',
        'url' => '/ssl?promocode=SSL50',
    ),

    // OTHER SERVICES
    'ai_builder' => array(
        'code' => 'AIBUILDER40',
        'discount' => '40',
        'description' => '40% off AI Website Builder',
        'url' => '/ai-website-builder?promocode=AIBUILDER40',
    ),
    'online_store' => array(
        'code' => 'STORE45',
        'discount' => '45',
        'description' => '45% off Online Store setup',
        'url' => '/online-store?promocode=STORE45',
    ),
    'local_seo' => array(
        'code' => 'SEO35',
        'discount' => '35',
        'description' => '35% off Local SEO services',
        'url' => '/local-seo?promocode=SEO35',
    ),

    // WHMCS CART GENERIC
    'whmcs_cart' => array(
        'code' => 'BLACKFRIDAY',
        'discount' => '50',
        'description' => 'Black Friday discount',
        'url' => '/cloud/cart.php?promocode=BLACKFRIDAY',
    ),
    'whmcs_store' => array(
        'code' => 'BFSTORE50',
        'discount' => '50',
        'description' => '50% off selected products',
        'url' => '/cloud/store?promocode=BFSTORE50',
    ),

    // GENERIC/DEFAULT
    'default' => array(
        'code' => 'BLACKFRIDAY2025',
        'discount' => '50',
        'description' => 'Black Friday mega sale',
        'url' => '/?promocode=BLACKFRIDAY2025',
    ),
);

// ==================== HELPER FUNCTIONS ====================

/**
 * Get promo code for a specific product group
 *
 * @param string $group Product group identifier (e.g., 'hosting', 'vps', 'domains')
 * @return array|null Promo code details or null if not found
 */
function whx_get_promo_code($group) {
    global $whx_promo_codes;

    if (isset($whx_promo_codes[$group])) {
        return $whx_promo_codes[$group];
    }

    // Return default if specific group not found
    return isset($whx_promo_codes['default']) ? $whx_promo_codes['default'] : null;
}

/**
 * Get promo code string only
 *
 * @param string $group Product group identifier
 * @return string Promo code or empty string
 */
function whx_get_promo_code_string($group) {
    $promo = whx_get_promo_code($group);
    return $promo ? $promo['code'] : '';
}

/**
 * Get promo URL with code applied
 *
 * @param string $group Product group identifier
 * @return string URL with promo code or default URL
 */
function whx_get_promo_url($group) {
    $promo = whx_get_promo_code($group);
    return $promo ? $promo['url'] : '#';
}

/**
 * Get all active promo codes (for admin/display purposes)
 *
 * @return array All promo codes
 */
function whx_get_all_promo_codes() {
    global $whx_promo_codes;
    return $whx_promo_codes;
}

/**
 * Auto-detect promo code based on current page URL
 * Useful for automatically showing relevant promo code
 *
 * @return array|null Matched promo code details
 */
function whx_auto_detect_promo_code() {
    global $whx_promo_codes;
    $current_url = $_SERVER['REQUEST_URI'];

    // Check for exact matches first
    foreach ($whx_promo_codes as $key => $promo) {
        if (isset($promo['url'])) {
            $clean_url = strtok($promo['url'], '?'); // Remove query params for matching
            if (strpos($current_url, $clean_url) !== false) {
                return $promo;
            }
        }
    }

    // Check for partial matches (keywords in URL)
    $url_keywords = array(
        'domain' => 'domains',
        'hosting' => 'hosting',
        'cpanel' => 'hosting_cpanel',
        'cyberpanel' => 'hosting_cyberpanel',
        'windows' => 'hosting_windows',
        'reseller' => 'hosting_reseller',
        'dedicated' => 'hosting_dedicated',
        'vps' => 'vps',
        'ssl' => 'ssl',
        'email' => 'hosting_email',
        'ai-website' => 'ai_builder',
        'store' => 'online_store',
        'seo' => 'local_seo',
    );

    foreach ($url_keywords as $keyword => $group) {
        if (stripos($current_url, $keyword) !== false && isset($whx_promo_codes[$group])) {
            return $whx_promo_codes[$group];
        }
    }

    return null;
}
