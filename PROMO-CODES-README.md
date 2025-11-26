# Black Friday Banner System with Promo Codes

A dynamic, configurable Black Friday banner system with integrated promo code management for WordPress/WHMCS websites.

## 📁 Files Overview

### Core Files
- **`promo-codes-config.php`** - Centralized promo code configuration
- **`black-friday-hero-banner.php`** - Main hero banner with promo code support
- **`black-friday-top-banner.php`** - Compact top banner with promo code support
- **`banner-examples.php`** - Ready-to-use banner style examples

## 🚀 Quick Start

### Step 1: Install Promo Codes Configuration

1. Open **Code Snippets WP Lite Plugin** in WordPress
2. Create a new snippet called "Promo Codes Config"
3. Copy the entire contents of `promo-codes-config.php`
4. Set location to **"Everywhere"** (important!)
5. Activate the snippet

### Step 2: Install Hero Banner (Optional)

1. Create a new snippet called "Black Friday Hero Banner"
2. Copy the entire contents of `black-friday-hero-banner.php`
3. Set location to **"After Header / Before Content"**
4. Activate the snippet

### Step 3: Install Top Banner (Optional)

1. Create a new snippet called "Black Friday Top Banner"
2. Copy the entire contents of `black-friday-top-banner.php`
3. Set location to **"Before Header / Top of Page"**
4. Activate the snippet

## 🎯 Features

### ✅ Promo Code Management
- **Centralized configuration** - Manage all promo codes in one place
- **Product group mapping** - Assign different codes for domains, hosting, VPS, etc.
- **Auto-applied URLs** - Promo codes automatically applied via URL parameters
- **Copy-to-clipboard** - One-click copy functionality with visual feedback

### ✅ Banner Features
- **Page-specific banners** - Different banners for different product pages
- **Countdown timers** - Create urgency with live countdown
- **Responsive design** - Mobile-first, works on all devices
- **Customizable colors** - Brand colors for each banner configuration
- **Flexible content** - Features list, pricing, guarantees, etc.

## 📝 How to Configure Promo Codes

Edit `promo-codes-config.php` to manage your promo codes:

```php
'hosting' => array(
    'code' => 'BFHOST85',           // The promo code
    'discount' => '85',             // Discount percentage (for display)
    'description' => 'Up to 85% off web hosting',
    'url' => '/hosting?promocode=BFHOST85',  // URL with promo code applied
),
```

### Available Product Groups

The following product groups are pre-configured:

#### Domains
- `domains` - General domain registrations
- `domains_ke` - Kenya (.KE) domains
- `domains_transfer` - Domain transfers
- `domains_free` - Free domain offers

#### Hosting
- `hosting` - General web hosting
- `hosting_cpanel` - cPanel hosting
- `hosting_cyberpanel` - CyberPanel hosting
- `hosting_windows` - Windows hosting
- `hosting_reseller` - Reseller hosting
- `hosting_free` - Free hosting
- `hosting_dedicated` - Dedicated servers
- `hosting_email` - Email hosting

#### VPS & Cloud
- `vps` - VPS hosting
- `vps_managed` - Managed VPS

#### Other Services
- `ssl` - SSL certificates
- `ai_builder` - AI Website Builder
- `online_store` - Online Store
- `local_seo` - Local SEO services

#### WHMCS
- `whmcs_cart` - WHMCS cart
- `whmcs_store` - WHMCS store

#### Default
- `default` - Fallback promo code

## 🎨 Configuring Banners

### Hero Banner Configuration

Each page can have its own banner configuration. Edit `black-friday-hero-banner.php`:

```php
'hosting' => array(
    'enabled' => true,
    'discount' => '85',
    'heading' => 'Black Friday sale',
    'subheading' => 'Up to {discount}% off Hosting + Website Builder',
    'price' => '1.95',
    'bonus' => '+3 months free',
    'features' => array(
        array('icon' => '✓', 'text' => 'Build your site fast with AI'),
        array('icon' => '✓', 'text' => 'Free domain'),
    ),
    'cta_text' => 'Claim deal',
    'cta_url' => '#pricing',
    'guarantee' => '30-day money-back guarantee',
    'background_color' => '#000000',
    'accent_color' => '#FF1B6D',
    'show_graphic' => true,
    'countdown_end' => '2025-11-29 23:59:59',
    'promo_code_group' => 'hosting',  // ← Links to promo-codes-config.php
    'show_promo_code' => true,        // ← Enable/disable promo code display
),
```

### Top Banner Configuration

Edit `black-friday-top-banner.php`:

```php
$top_banner_config = array(
    'enabled' => true,
    'message' => 'Black Friday: Get extra months free',
    'background_color' => '#FF1B6D',
    'text_color' => '#FFFFFF',
    'countdown_end' => '2025-11-29 23:59:59',
    'show_countdown' => true,
    'link_url' => '#pricing',
    'height' => '60px',
    'dismiss_button' => true,
    'promo_code_group' => 'default',  // ← Links to promo-codes-config.php
    'show_promo_code' => true,        // ← Enable/disable promo code display
);
```

## 🔧 Helper Functions

The promo code system provides several helper functions:

### Get Full Promo Details
```php
$promo = whx_get_promo_code('hosting');
// Returns: array('code' => 'BFHOST85', 'discount' => '85', ...)
```

### Get Just the Code String
```php
$code = whx_get_promo_code_string('hosting');
// Returns: "BFHOST85"
```

### Get URL with Promo Code
```php
$url = whx_get_promo_url('hosting');
// Returns: "/hosting?promocode=BFHOST85"
```

### Get All Promo Codes
```php
$all_promos = whx_get_all_promo_codes();
// Returns: array of all promo codes
```

### Auto-Detect from URL
```php
$promo = whx_auto_detect_promo_code();
// Automatically detects based on current page URL
```

## 📱 Responsive Design

Both banners are fully responsive:

- **Desktop (>968px)** - Full layout with all features
- **Tablet (640-968px)** - Stacked layout, centered content
- **Mobile (<640px)** - Compact layout, optimized touch targets

## 🎯 Page Detection

Banners automatically display on the correct pages using multiple detection methods:

1. **Page Slug** - Matches WordPress page slug (e.g., `/hosting`)
2. **Query Parameter** - Use `?bf_banner=hosting` to force a specific banner
3. **Category** - Matches post category slugs
4. **URL Keywords** - Searches for keywords in the URL path

## 💾 URL Structure

All URLs follow this pattern for consistency:

### WordPress Landing Pages
```
/hosting
/hosting/cpanel
/vps-hosting
/ssl
```

### WHMCS Direct Links
```
/cloud/cart.php?a=add&domain=register&promocode=BFDOMAIN50
/cloud/store/vps-hosting?promocode=BFVPS65
```

### With Promo Code Applied
```
/hosting?promocode=BFHOST85
/ssl?promocode=SSL50
```

## 🔄 How It Works

### 1. Promo Code Auto-Apply Flow

```
User sees banner → Clicks CTA button → Redirected to URL with promo code →
WHMCS/Cart automatically applies the promo code
```

### 2. Copy-to-Clipboard Flow

```
User clicks "Copy" button → Code copied to clipboard →
Visual feedback (button turns green, shows "Copied!") →
User can paste code manually in checkout
```

## 🎨 Customization

### Change Colors

In banner configuration:
```php
'background_color' => '#000000',  // Banner background
'accent_color' => '#FF1B6D',      // Highlight color (buttons, badges)
```

### Change Countdown End Date

```php
'countdown_end' => '2025-11-29 23:59:59',  // YYYY-MM-DD HH:MM:SS
```

### Disable Promo Code Display

```php
'show_promo_code' => false,
```

### Disable Banner

```php
'enabled' => false,
```

## 📊 Usage Examples

### Example 1: Hosting Page Banner with Promo Code

A user visits `/hosting`:
1. System detects page = "hosting"
2. Loads hosting banner configuration
3. Gets promo code from `promo_code_group: 'hosting'`
4. Displays banner with promo code "BFHOST85"
5. CTA button links to `/hosting?promocode=BFHOST85`

### Example 2: VPS Page with Different Promo

A user visits `/vps-hosting`:
1. System detects page = "vps"
2. Loads VPS banner configuration
3. Gets promo code from `promo_code_group: 'vps'`
4. Displays banner with promo code "BFVPS65"
5. CTA button links to `/vps-hosting?promocode=BFVPS65`

### Example 3: Top Banner with Default Promo

Top banner displays on all pages:
1. Uses `promo_code_group: 'default'`
2. Shows promo code "BLACKFRIDAY2025"
3. User can copy code from any page
4. Links to homepage with promo code applied

## 🐛 Troubleshooting

### Promo Code Not Showing

1. **Check promo-codes-config.php is loaded first**
   - It must be activated in Code Snippets
   - Location must be set to "Everywhere"

2. **Check banner configuration**
   ```php
   'show_promo_code' => true,  // Must be true
   'promo_code_group' => 'hosting',  // Must match a group in promo-codes-config.php
   ```

### Banner Not Displaying

1. **Check enabled status**
   ```php
   'enabled' => true,
   ```

2. **Check page detection**
   - Try adding `?bf_banner=hosting` to the URL
   - Check if page slug matches configuration key

3. **Check snippet location**
   - Hero banner: "After Header / Before Content"
   - Top banner: "Before Header / Top of Page"

### Copy Button Not Working

1. **Check JavaScript console** for errors
2. **Browser compatibility** - Modern browsers required for clipboard API
3. **HTTPS required** - Clipboard API requires secure context

### Promo Code Not Applied to Cart

1. **Check URL parameter** - Should be `?promocode=CODE`
2. **Verify in WHMCS** - Promo code must exist and be active
3. **Check product eligibility** - Promo must apply to the product

## 📞 Support & Customization

For custom modifications or support:
- Check the code comments in each file
- Review `banner-examples.php` for different style options
- Test on staging site before deploying to production

## 🔐 Security Notes

- All output is escaped using `esc_html()` and `esc_url()`
- No user input is processed without sanitization
- Promo codes are server-side configured (not user-editable)

## 📈 Best Practices

1. **Keep promo codes short** - Easier to remember and type
2. **Use descriptive codes** - "BFHOST85" tells users what it's for
3. **Set realistic discounts** - Match the discount % in config to actual WHMCS promo
4. **Test before launch** - Verify all promo codes work in WHMCS
5. **Update countdown dates** - Ensure they match your actual promotion period
6. **Monitor performance** - Check banner loading doesn't slow down site

## 🎁 Features Summary

### Promo Code System
✅ Centralized configuration
✅ Per-product-group codes
✅ Auto-apply via URL
✅ Copy-to-clipboard
✅ Mobile-friendly
✅ Visual feedback

### Banner System
✅ Page-specific content
✅ Countdown timers
✅ Responsive design
✅ Customizable colors
✅ Multiple layouts
✅ Easy configuration

---

**Version:** 1.0.0
**Last Updated:** November 2025
**Compatibility:** WordPress 5.0+, WHMCS 7.0+
