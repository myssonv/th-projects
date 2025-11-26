# Black Friday Banner - Installation Guide

Complete step-by-step guide to install and configure the Black Friday banner on your WordPress site.

## What You Get

1. **black-friday-hero-banner.php** - Full hero banner with all features
2. **black-friday-top-banner.php** - Compact top strip banner
3. **banner-examples.php** - 10+ ready-to-use configurations
4. **Documentation** - Complete setup and customization guide

## Prerequisites

- WordPress website
- Code Snippets plugin (free) OR access to theme files
- Basic understanding of WordPress admin

## Installation Method 1: Code Snippets Plugin (Recommended)

### Step 1: Install Code Snippets Plugin

1. Log in to WordPress admin
2. Go to **Plugins > Add New**
3. Search for **"Code Snippets"**
4. Install and activate **Code Snippets** by Code Snippets Pro

### Step 2: Create New Snippet for Hero Banner

1. Go to **Snippets > Add New**
2. Give it a name: "Black Friday Hero Banner"
3. Copy the entire content from `black-friday-hero-banner.php`
4. Paste into the **Code** field
5. In the dropdown, select **Only run in site front-end**
6. Set **Run snippet everywhere** to ON
7. Click **Save Changes and Activate**

### Step 3: Configure Your Banner

1. In the snippet code editor, find the `$banner_configs` array (around line 10)
2. Modify the settings for your needs:

```php
$banner_configs = array(
    'hosting' => array(
        'enabled' => true,
        'discount' => '85',
        'heading' => 'Black Friday sale',
        'subheading' => 'Up to {discount}% off Hosting',
        'price' => '1.95',
        'countdown_end' => '2025-11-29 23:59:59',
        // ... more settings
    ),
);
```

3. Update the countdown end date to your sale end date
4. Customize colors, text, features, and CTA
5. Click **Update**

### Step 4: Add Top Banner (Optional)

1. Go to **Snippets > Add New**
2. Name it: "Black Friday Top Banner"
3. Copy content from `black-friday-top-banner.php`
4. Paste and configure
5. Set to run on **Only run in site front-end**
6. Save and activate

## Installation Method 2: Theme Functions

### For Child Theme (Recommended)

1. Access your WordPress files via FTP or File Manager
2. Navigate to `/wp-content/themes/your-child-theme/`
3. Upload `black-friday-hero-banner.php` to the theme folder
4. Open `functions.php` in your child theme
5. Add this code:

```php
// Black Friday Hero Banner
add_action('wp_body_open', function() {
    if (is_page(['hosting', 'wordpress', 'pricing'])) {
        include(get_stylesheet_directory() . '/black-friday-hero-banner.php');
    }
});
```

6. Save the file

### Important Notes for Theme Method

- Use a child theme to prevent updates from overwriting your changes
- The `wp_body_open` hook requires WordPress 5.2+
- Adjust the page slugs in the `is_page()` condition

## Installation Method 3: Template Files

### Direct Template Integration

1. Locate your theme's template file (usually `header.php`)
2. Find where you want the banner to appear (usually after `<body>` tag)
3. Add this line:

```php
<?php include(get_template_directory() . '/black-friday-hero-banner.php'); ?>
```

4. Upload `black-friday-hero-banner.php` to your theme folder

## Configuration Guide

### Page-Specific Configuration

The banner automatically detects which configuration to show based on:

#### 1. Page Slug
Create a page with slug "hosting" → uses 'hosting' config

#### 2. URL Parameter (Testing)
Add `?bf_banner=wordpress` to any URL → uses 'wordpress' config

#### 3. Category Slug
Post in "hosting" category → uses 'hosting' config

### Quick Configuration

Edit the configuration array for each page:

```php
$banner_configs = array(
    'hosting' => array(
        'enabled' => true,              // Show banner
        'discount' => '85',             // Discount %
        'heading' => 'Your Heading',    // Main title
        'countdown_end' => '2025-11-29 23:59:59',
        // Copy settings from banner-examples.php
    ),
    'wordpress' => array(
        // Different config for WordPress page
    ),
);
```

### Using Pre-Made Examples

1. Open `banner-examples.php`
2. Find a style you like (e.g., `$hostinger_style`)
3. Copy the entire array
4. Paste into your `$banner_configs`:

```php
$banner_configs = array(
    'hosting' => array(
        'enabled' => true,
        'discount' => '85',
        'heading' => 'Black Friday sale',
        // ... rest of configuration
    ),
);
```

## Testing Your Banner

### Method 1: URL Parameter
Visit your site with: `https://yoursite.com?bf_banner=hosting`

### Method 2: Create Test Page
1. Create a new page
2. Set slug to match your config key (e.g., "hosting")
3. Publish and view

### Method 3: Debug Mode
Add this line after line 67 in the banner file:

```php
$config = bf_get_current_config($banner_configs);
var_dump($config); // Shows current config
```

Remove after testing!

## Customization Examples

### Change Colors

```php
'background_color' => '#000000',  // Black background
'accent_color' => '#FF1B6D',      // Pink accent
```

### Hide Elements

```php
'price' => null,           // Hide pricing
'bonus' => null,           // Hide bonus text
'guarantee' => null,       // Hide guarantee
'show_graphic' => false,   // Hide percentage graphic
```

### Add More Features

```php
'features' => array(
    array('icon' => '✓', 'text' => 'Feature 1'),
    array('icon' => '✓', 'text' => 'Feature 2'),
    array('icon' => '✓', 'text' => 'Feature 3'),
    array('icon' => '✓', 'text' => 'Feature 4'),
),
```

### Multiple Countdowns

Each page config can have a different countdown end date:

```php
'hosting' => array(
    'countdown_end' => '2025-11-29 23:59:59',
),
'vps' => array(
    'countdown_end' => '2025-12-02 23:59:59',  // Cyber Monday
),
```

## Positioning Options

### Show After Header

```php
add_action('wp_body_open', function() {
    include('black-friday-hero-banner.php');
});
```

### Show Before Content

```php
add_action('the_content', function($content) {
    ob_start();
    include('black-friday-hero-banner.php');
    $banner = ob_get_clean();
    return $banner . $content;
});
```

### Show on Specific Pages Only

```php
add_action('wp_body_open', function() {
    if (is_page(['hosting', 'pricing', 'wordpress'])) {
        include('black-friday-hero-banner.php');
    }
});
```

### Show on Homepage Only

```php
add_action('wp_body_open', function() {
    if (is_front_page()) {
        include('black-friday-hero-banner.php');
    }
});
```

## Advanced Features

### Show Different Banner for Logged-In Users

```php
if (is_user_logged_in()) {
    // Show returning customer offer
    $config = $banner_configs['returning'];
} else {
    // Show new customer offer
    $config = $banner_configs['new'];
}
```

### A/B Testing

```php
$variants = ['version_a', 'version_b'];
$selected = $variants[rand(0, 1)];
$config = $banner_configs[$selected];
```

### Show Based on Referrer

```php
if (strpos($_SERVER['HTTP_REFERER'], 'facebook.com') !== false) {
    $config = $banner_configs['social'];
} else {
    $config = $banner_configs['default'];
}
```

## Troubleshooting

### Banner Not Showing

**Check 1:** Is the snippet activated?
- Go to Snippets and verify it's active

**Check 2:** Is enabled set to true?
```php
'enabled' => true,
```

**Check 3:** Test with URL parameter
```
?bf_banner=hosting
```

**Check 4:** Check for PHP errors
- Enable WP_DEBUG in wp-config.php
- Check error logs

### Countdown Not Working

**Issue:** Date format
```php
// Wrong
'countdown_end' => '11/29/2025',

// Correct
'countdown_end' => '2025-11-29 23:59:59',
```

**Issue:** Date in the past
- Make sure the date is in the future

### Styling Issues

**Issue:** Banner conflicts with theme
- Add higher CSS specificity
- Use `!important` if needed:

```css
.bf-hero-banner {
    background: #000000 !important;
}
```

**Issue:** Banner too wide
```css
.bf-hero-container {
    max-width: 1000px !important;
}
```

### Mobile Issues

Test responsive design:
- Desktop: Full width browser
- Tablet: < 968px width
- Mobile: < 640px width

## Performance Optimization

### Lazy Load Banner

Only load on specific pages:

```php
add_action('wp_body_open', function() {
    if (is_page(['hosting', 'pricing'])) {
        include('black-friday-hero-banner.php');
    }
});
```

### Minify CSS

Copy the CSS section and minify it using online tools.

### Conditional Loading

Don't show after sale ends:

```php
$sale_end = strtotime('2025-11-29 23:59:59');
if (time() < $sale_end) {
    include('black-friday-hero-banner.php');
}
```

## Going Live Checklist

- [ ] Configure all page-specific settings
- [ ] Set correct countdown end dates
- [ ] Test on desktop, tablet, mobile
- [ ] Verify CTA links are correct
- [ ] Test on different browsers
- [ ] Check loading speed
- [ ] Verify colors match brand
- [ ] Proofread all text
- [ ] Test dismiss functionality (top banner)
- [ ] Set up analytics tracking
- [ ] Create backup before going live
- [ ] Test in staging environment first

## Post-Sale Cleanup

### Disable Banner

**Method 1:** Set enabled to false
```php
'enabled' => false,
```

**Method 2:** Deactivate snippet
- Go to Snippets
- Click Deactivate

**Method 3:** Delete snippet
- Only if you won't reuse it

### Archive for Next Year

1. Export snippet (Code Snippets > Export)
2. Save file for next Black Friday
3. Update dates and offers next year
4. Re-import and activate

## Support Resources

- Review the README.md for detailed documentation
- Check banner-examples.php for inspiration
- Test different configurations
- Use browser DevTools for CSS debugging

## Quick Start Summary

1. Install Code Snippets plugin
2. Create new snippet
3. Copy `black-friday-hero-banner.php` code
4. Configure your settings in `$banner_configs`
5. Set countdown end date
6. Save and activate
7. Test with `?bf_banner=your-key`
8. Go live!

---

**Need Help?**
- Check the README.md for detailed docs
- Review code comments for inline help
- Test with URL parameters for debugging
- Use browser console to check for errors
