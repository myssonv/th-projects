# Black Friday Hero Banner - Setup Guide

A dynamic, configurable hero banner system for WordPress that displays Black Friday deals with countdown timers, pricing, features, and CTAs.

## Features

✅ **Fully Configurable** - Easy-to-edit settings array for different pages
✅ **Countdown Timer** - Real-time countdown to deal expiration
✅ **Responsive Design** - Works on all devices
✅ **Page-Specific** - Different banners for hosting, WordPress, cloud, etc.
✅ **Multiple Detection Methods** - By page slug, URL parameter, or category
✅ **No Database Required** - All settings in code for easy version control

## Installation Methods

### Method 1: Code Snippets Plugin (Recommended)

1. Install **Code Snippets** plugin from WordPress repository
2. Go to **Snippets > Add New**
3. Copy the entire content of `black-friday-hero-banner.php`
4. Paste into the Code field
5. Set **Run snippet everywhere** (or use the location filter)
6. Add this code to show it after the header:

```php
add_action('wp_body_open', function() {
    include('path-to-black-friday-hero-banner.php');
});
```

Or use conditional insertion:
```php
add_action('wp_body_open', function() {
    if (is_page(['hosting', 'wordpress', 'cloud'])) {
        include('path-to-black-friday-hero-banner.php');
    }
});
```

7. Save and activate the snippet

### Method 2: Theme Functions

Add to your child theme's `functions.php`:

```php
add_action('wp_body_open', function() {
    include(get_stylesheet_directory() . '/black-friday-hero-banner.php');
});
```

### Method 3: Template File

Add directly to your theme template (e.g., `header.php`):

```php
<?php include(get_template_directory() . '/black-friday-hero-banner.php'); ?>
```

## Configuration

### Page-Specific Settings

The banner uses a configuration array with page-specific settings:

```php
$banner_configs = array(
    'hosting' => array(
        'enabled' => true,                           // Show banner on this page
        'discount' => '85',                          // Discount percentage
        'heading' => 'Black Friday sale',            // Main heading
        'subheading' => 'Up to {discount}% off...',  // {discount} auto-replaced
        'price' => '1.95',                           // Price (null to hide)
        'bonus' => '+3 months free',                 // Bonus text (null to hide)
        'features' => array(                         // Feature list
            array('icon' => '✓', 'text' => 'Build your site fast with AI'),
            array('icon' => '✓', 'text' => 'Free domain'),
        ),
        'cta_text' => 'Claim deal',                  // Button text
        'cta_url' => '#pricing',                     // Button URL
        'guarantee' => '30-day money-back guarantee', // Guarantee text
        'background_color' => '#000000',             // Background color
        'accent_color' => '#FF1B6D',                 // Accent/CTA color
        'show_graphic' => true,                      // Show percentage graphic
        'countdown_end' => '2025-11-29 23:59:59',   // Countdown end date
    ),
);
```

### Adding a New Page Configuration

Simply add a new key to the `$banner_configs` array:

```php
'vps' => array(
    'enabled' => true,
    'discount' => '50',
    'heading' => 'VPS Black Friday Sale',
    'subheading' => 'Get {discount}% off all VPS plans',
    'features' => array(
        array('icon' => '⚡', 'text' => 'SSD Storage'),
        array('icon' => '🚀', 'text' => 'Fast Setup'),
        array('icon' => '🔒', 'text' => 'DDoS Protection'),
    ),
    'cta_text' => 'View VPS Plans',
    'cta_url' => '/vps-hosting',
    'background_color' => '#1a1a2e',
    'accent_color' => '#00ff88',
    'countdown_end' => '2025-11-29 23:59:59',
),
```

## Page Detection Methods

The banner automatically detects which configuration to use:

### 1. Page Slug (Default)
If you have a page with slug "hosting", it will use the 'hosting' configuration.

### 2. URL Parameter
Add `?bf_banner=wordpress` to any URL to force the WordPress configuration.

**Example:** `https://yoursite.com/pricing?bf_banner=hosting`

### 3. Category Slug
For posts in a category with slug "hosting", uses the 'hosting' configuration.

### 4. URL Contains Keyword
If URL contains `/hosting/`, uses the 'hosting' configuration.

## Customization Examples

### Example 1: Simple Banner (No Price)

```php
'simple' => array(
    'enabled' => true,
    'discount' => '50',
    'heading' => 'Black Friday 2025',
    'subheading' => 'Up to {discount}% off all products',
    'price' => null,  // Hide pricing
    'bonus' => null,  // Hide bonus
    'features' => array(
        array('icon' => '🎉', 'text' => 'Huge savings'),
        array('icon' => '⏰', 'text' => 'Limited time only'),
    ),
    'cta_text' => 'Shop Now',
    'cta_url' => '/shop',
    'background_color' => '#000000',
    'accent_color' => '#FF0000',
    'show_graphic' => true,
    'countdown_end' => '2025-11-29 23:59:59',
),
```

### Example 2: Banner with Custom Colors

```php
'premium' => array(
    'enabled' => true,
    'discount' => '70',
    'heading' => 'Premium Hosting Sale',
    'subheading' => 'Save {discount}% on Premium Plans',
    'features' => array(
        array('icon' => '⭐', 'text' => 'Premium support'),
        array('icon' => '🔒', 'text' => 'Free SSL'),
        array('icon' => '📊', 'text' => 'Advanced analytics'),
    ),
    'cta_text' => 'Upgrade Now',
    'cta_url' => '/premium',
    'background_color' => '#0f0f23',  // Dark blue
    'accent_color' => '#FFD700',      // Gold
    'show_graphic' => true,
    'countdown_end' => '2025-11-29 23:59:59',
),
```

### Example 3: Top Banner Strip (Minimal)

For a minimal top banner (like example 1), create this variation:

```php
'minimal' => array(
    'enabled' => true,
    'discount' => '85',
    'heading' => 'Black Friday: Get extra months free',
    'subheading' => null,
    'price' => null,
    'features' => array(),  // No features
    'cta_text' => null,     // No CTA
    'show_graphic' => false, // No graphic
    'countdown_end' => '2025-11-29 23:59:59',
),
```

## Styling Customization

### Change Colors Globally

Find and replace in the CSS section:

```css
.bf-hero-banner {
    background: <?php echo $config['background_color']; ?>;
}

.bf-hero-cta {
    background: <?php echo $config['accent_color']; ?>;
}
```

### Adjust Spacing

```css
.bf-hero-banner {
    padding: 80px 20px;  /* Increase vertical padding */
}
```

### Change Font Sizes

```css
.bf-hero-heading {
    font-size: 56px;  /* Larger heading */
}
```

## Testing Different Configurations

### Quick Testing with URL Parameters

- `?bf_banner=hosting` - Test hosting banner
- `?bf_banner=wordpress` - Test WordPress banner
- `?bf_banner=cloud` - Test cloud banner

### Debug Mode

Add this to see which config is being used:

```php
// Add after: $config = bf_get_current_config($banner_configs);
echo '<!-- BF Banner Config: ' . print_r($config, true) . ' -->';
```

## Advanced Customization

### Custom Icons

Use any emoji or HTML entity:

```php
'features' => array(
    array('icon' => '🚀', 'text' => 'Fast hosting'),
    array('icon' => '💰', 'text' => 'Money back guarantee'),
    array('icon' => '⚡', 'text' => 'Lightning fast'),
),
```

Or use Font Awesome (if loaded):

```php
'features' => array(
    array('icon' => '<i class="fas fa-check"></i>', 'text' => 'Feature 1'),
),
```

### Multiple Banners on One Page

Modify the detection function to return an array of configs and loop through them.

### A/B Testing

Add random selection:

```php
$variants = ['hosting', 'hosting_variant_b'];
$config = $banner_configs[$variants[array_rand($variants)]];
```

## Countdown Timer Settings

Set the countdown end date:

```php
'countdown_end' => '2025-11-29 23:59:59',  // Format: YYYY-MM-DD HH:MM:SS
```

Timezone is based on WordPress settings (Settings > General > Timezone).

## Responsive Breakpoints

- **Desktop:** Full layout with side-by-side content and graphic
- **Tablet (< 968px):** Stacked layout, centered
- **Mobile (< 640px):** Compact layout, smaller fonts

## Troubleshooting

### Banner Not Showing

1. Check `'enabled' => true` in your config
2. Verify the page slug matches the config key
3. Check hook placement (`wp_body_open` or `get_header`)
4. Test with URL parameter: `?bf_banner=your-config-key`

### Countdown Not Working

1. Check date format: `YYYY-MM-DD HH:MM:SS`
2. Verify date is in the future
3. Check browser console for JavaScript errors

### Colors Not Changing

1. Verify `background_color` and `accent_color` in config
2. Check for theme CSS conflicts (use `!important` if needed)
3. Clear cache if using caching plugin

### Styling Issues

1. Check for theme CSS conflicts
2. Increase CSS specificity
3. Add `!important` to critical styles
4. Check browser console for errors

## Performance

- Minimal JavaScript (countdown only)
- No external dependencies
- Inline CSS for faster loading
- No database queries
- Mobile-optimized

## Browser Support

- Chrome/Edge (modern)
- Firefox (modern)
- Safari (modern)
- Mobile browsers (iOS Safari, Chrome Mobile)

## Security

- All output is escaped (`esc_html`, `esc_url`, `esc_attr`)
- No user input processing
- No database operations
- Safe for production use

## Support

For issues or questions:
1. Check this README
2. Review the code comments
3. Test with URL parameters
4. Check browser console for errors

## License

Free to use and modify for your projects.
