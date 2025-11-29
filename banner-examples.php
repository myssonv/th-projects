<?php
/**
 * Black Friday Banner - Ready-to-Use Examples
 *
 * Copy any of these configurations into your main banner file
 * or use them as inspiration for your own designs
 */

// ==================== EXAMPLE 1: HOSTINGER STYLE ====================
// Bold, vibrant design with large discount graphic
$hostinger_style = array(
    'enabled' => true,
    'discount' => '85',
    'heading' => 'Black Friday sale',
    'subheading' => 'Up to {discount}% off Hosting + Website Builder',
    'price' => '1.95',
    'bonus' => '+3 months free',
    'features' => array(
        array('icon' => '✓', 'text' => 'Build your site fast with AI'),
        array('icon' => '✓', 'text' => 'Free domain'),
        array('icon' => '✓', 'text' => 'Free SSL certificate'),
    ),
    'cta_text' => 'Claim deal',
    'cta_url' => '#pricing',
    'guarantee' => '30-day money-back guarantee',
    'background_color' => '#000000',
    'accent_color' => '#FF1B6D',
    'show_graphic' => true,
    'countdown_end' => '2025-11-29 23:59:59',
);

// ==================== EXAMPLE 2: HOSTING.COM STYLE ====================
// Clean, professional with countdown focus
$hosting_com_style = array(
    'enabled' => true,
    'discount' => '50',
    'heading' => 'This Black Friday, be seen, get ready, stay ahead.',
    'subheading' => 'Get your WordPress site ready for Black Friday',
    'price' => '1.00',
    'bonus' => 'free for 6 months',
    'features' => array(
        array('icon' => '⚡', 'text' => 'Lightning fast performance'),
        array('icon' => '🔒', 'text' => 'Enterprise security'),
    ),
    'cta_text' => 'See plans',
    'cta_url' => '/wordpress-hosting',
    'guarantee' => null,
    'background_color' => '#2c3e50',
    'accent_color' => '#3BFFB4',
    'show_graphic' => true,
    'countdown_end' => '2025-11-29 23:59:59',
);

// ==================== EXAMPLE 3: OVHCLOUD STYLE ====================
// Minimal, corporate style
$ovh_style = array(
    'enabled' => true,
    'discount' => '50',
    'heading' => 'Black Friday 2025',
    'subheading' => 'Up to {discount}% off a wide selection of products',
    'price' => null,
    'bonus' => null,
    'features' => array(
        array('icon' => '🖥️', 'text' => 'Dedicated servers'),
        array('icon' => '☁️', 'text' => 'Public Cloud'),
        array('icon' => '🌐', 'text' => 'Domain names'),
        array('icon' => '💻', 'text' => 'VPS hosting'),
    ),
    'cta_text' => 'View offers',
    'cta_url' => '/black-friday',
    'guarantee' => null,
    'background_color' => '#000080',
    'accent_color' => '#FFFFFF',
    'show_graphic' => false,
    'countdown_end' => '2025-11-29 23:59:59',
);

// ==================== EXAMPLE 4: GRADIENT STYLE ====================
// Modern gradient background
$gradient_style = array(
    'enabled' => true,
    'discount' => '75',
    'heading' => 'Black Friday Mega Sale',
    'subheading' => 'Save {discount}% on all premium plans',
    'price' => '2.99',
    'bonus' => '+6 months free',
    'features' => array(
        array('icon' => '🚀', 'text' => 'Blazing fast SSD storage'),
        array('icon' => '🛡️', 'text' => 'DDoS protection included'),
        array('icon' => '📊', 'text' => 'Free website migration'),
    ),
    'cta_text' => 'Get started',
    'cta_url' => '#plans',
    'guarantee' => '45-day money-back guarantee',
    'background_color' => '#1a1a2e',
    'accent_color' => '#00ff88',
    'show_graphic' => true,
    'countdown_end' => '2025-11-29 23:59:59',
);

// ==================== EXAMPLE 5: CYBER MONDAY ====================
// Cyber Monday variant
$cyber_monday_style = array(
    'enabled' => true,
    'discount' => '60',
    'heading' => 'Cyber Monday Sale',
    'subheading' => 'Extended! Get {discount}% off premium hosting',
    'price' => '3.49',
    'bonus' => 'First year only',
    'features' => array(
        array('icon' => '⚡', 'text' => 'NVMe SSD storage'),
        array('icon' => '🔒', 'text' => 'Free SSL & CDN'),
        array('icon' => '📧', 'text' => 'Unlimited email accounts'),
    ),
    'cta_text' => 'Start now',
    'cta_url' => '/checkout',
    'guarantee' => '30-day guarantee',
    'background_color' => '#0a0e27',
    'accent_color' => '#00d4ff',
    'show_graphic' => true,
    'countdown_end' => '2025-12-02 23:59:59',
);

// ==================== EXAMPLE 6: MINIMALIST ====================
// Clean, minimal design
$minimalist_style = array(
    'enabled' => true,
    'discount' => '40',
    'heading' => 'Black Friday',
    'subheading' => '{discount}% off premium hosting',
    'price' => '4.99',
    'bonus' => null,
    'features' => array(
        array('icon' => '✓', 'text' => 'Premium support'),
        array('icon' => '✓', 'text' => 'Daily backups'),
    ),
    'cta_text' => 'Shop now',
    'cta_url' => '/shop',
    'guarantee' => null,
    'background_color' => '#ffffff',
    'accent_color' => '#000000',
    'show_graphic' => false,
    'countdown_end' => '2025-11-29 23:59:59',
);

// ==================== EXAMPLE 7: VPS FOCUSED ====================
// VPS-specific offering
$vps_style = array(
    'enabled' => true,
    'discount' => '65',
    'heading' => 'VPS Black Friday Special',
    'subheading' => 'Save {discount}% on all VPS plans',
    'price' => '5.99',
    'bonus' => 'First 3 months',
    'features' => array(
        array('icon' => '💾', 'text' => 'Up to 8TB SSD storage'),
        array('icon' => '🔧', 'text' => 'Full root access'),
        array('icon' => '⚡', 'text' => 'Instant setup'),
        array('icon' => '🌍', 'text' => 'Global data centers'),
    ),
    'cta_text' => 'Choose VPS plan',
    'cta_url' => '/vps',
    'guarantee' => '7-day money-back guarantee',
    'background_color' => '#1e1e1e',
    'accent_color' => '#ff6b35',
    'show_graphic' => true,
    'countdown_end' => '2025-11-29 23:59:59',
);

// ==================== EXAMPLE 8: DOMAIN NAMES ====================
// Domain registration focus
$domain_style = array(
    'enabled' => true,
    'discount' => '80',
    'heading' => 'Domain Name Sale',
    'subheading' => 'Get your perfect domain for just $0.99',
    'price' => '0.99',
    'bonus' => 'First year only',
    'features' => array(
        array('icon' => '🌐', 'text' => '.com, .net, .org & more'),
        array('icon' => '🔒', 'text' => 'Free WHOIS privacy'),
        array('icon' => '📧', 'text' => 'Free email forwarding'),
    ),
    'cta_text' => 'Search domains',
    'cta_url' => '/domains',
    'guarantee' => null,
    'background_color' => '#2d3748',
    'accent_color' => '#48bb78',
    'show_graphic' => true,
    'countdown_end' => '2025-11-29 23:59:59',
);

// ==================== EXAMPLE 9: AGENCY/RESELLER ====================
// For agencies and resellers
$agency_style = array(
    'enabled' => true,
    'discount' => '55',
    'heading' => 'Black Friday Agency Deals',
    'subheading' => '{discount}% off reseller & agency plans',
    'price' => '19.99',
    'bonus' => 'First 6 months',
    'features' => array(
        array('icon' => '👥', 'text' => 'White-label solution'),
        array('icon' => '💼', 'text' => 'Client management tools'),
        array('icon' => '💰', 'text' => 'Priority support'),
    ),
    'cta_text' => 'View agency plans',
    'cta_url' => '/agency',
    'guarantee' => '30-day money-back guarantee',
    'background_color' => '#0f172a',
    'accent_color' => '#f59e0b',
    'show_graphic' => false,
    'countdown_end' => '2025-11-29 23:59:59',
);

// ==================== EXAMPLE 10: E-COMMERCE HOSTING ====================
// WooCommerce/eCommerce focused
$ecommerce_style = array(
    'enabled' => true,
    'discount' => '70',
    'heading' => 'Launch Your Store This Black Friday',
    'subheading' => '{discount}% off WooCommerce hosting',
    'price' => '6.99',
    'bonus' => 'Free premium theme',
    'features' => array(
        array('icon' => '🛒', 'text' => 'Optimized for WooCommerce'),
        array('icon' => '💳', 'text' => 'PCI compliance ready'),
        array('icon' => '📈', 'text' => 'Advanced analytics'),
    ),
    'cta_text' => 'Start selling',
    'cta_url' => '/woocommerce',
    'guarantee' => '60-day money-back guarantee',
    'background_color' => '#1a1625',
    'accent_color' => '#a855f7',
    'show_graphic' => true,
    'countdown_end' => '2025-11-29 23:59:59',
);

// ==================== HOW TO USE THESE EXAMPLES ====================
/*

1. Copy the style you like
2. Paste it into your $banner_configs array in black-friday-hero-banner.php
3. Customize the values to match your offer
4. Set the page detection key (e.g., 'hosting', 'vps', etc.)

Example:

$banner_configs = array(
    'hosting' => $hostinger_style,  // Use pre-made style
    'vps' => $vps_style,            // Use another pre-made style
    'custom' => array(              // Or create your own
        'enabled' => true,
        // ... your custom values
    ),
);

*/

// ==================== COLOR SCHEMES ====================
/*

Popular Color Combinations:

1. Vibrant Pink & Black (Hostinger style)
   background: #000000, accent: #FF1B6D

2. Dark Blue & Mint (Modern)
   background: #1a2332, accent: #00D9A3

3. Deep Blue & White (Professional)
   background: #000080, accent: #FFFFFF

4. Dark & Neon Green (Tech)
   background: #1e1e1e, accent: #00ff88

5. Navy & Cyan (Clean)
   background: #0a0e27, accent: #00d4ff

6. Dark & Orange (Energetic)
   background: #1e1e1e, accent: #ff6b35

7. Slate & Green (Balanced)
   background: #2d3748, accent: #48bb78

8. Midnight & Amber (Premium)
   background: #0f172a, accent: #f59e0b

9. Dark Purple & Purple (Luxury)
   background: #1a1625, accent: #a855f7

10. Black & Red (Bold)
    background: #000000, accent: #ff0000

*/

// ==================== ICON REFERENCE ====================
/*

Common Icons (Emoji):

✓ ✔️ ✅ - Checkmarks
🚀 - Fast/Speed
⚡ - Lightning/Performance
🔒 🛡️ - Security
💰 💸 - Money/Savings
📊 📈 - Analytics/Growth
🌐 🌍 - Global/Domain
💻 🖥️ - Computer/Hosting
☁️ - Cloud
📧 ✉️ - Email
🎉 🎊 - Celebration
⭐ ✨ - Premium/Special
🔧 ⚙️ - Tools/Settings
📁 💾 - Storage
👥 - Users/Team
🛒 - Shopping/eCommerce
💼 - Business
🔥 - Hot/Trending
⏰ - Time/Urgent
🎯 - Target/Goal

*/
