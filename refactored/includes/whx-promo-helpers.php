<?php
/**
 * WHX Promo Cards - Helper Functions
 *
 * Refactored helper functions with improved efficiency
 * and reduced code complexity.
 */

if (!defined('ABSPATH')) {
    die('Direct access not permitted');
}

// ---- Category Detection (Array-Based Refactor) ----

/**
 * Categorize Product by Group Name
 * Refactored from 150+ lines to ~60 lines using array-based lookup
 */
function whx_categorize_product_advanced($group, $metadata = []) {
    $group_lower = strtolower($group);
    $categories = [];

    // Define all patterns in one consolidated map
    $pattern_map = [
        'hosting' => [
            'patterns' => ['web hosting', 'shared hosting', 'cloud hosting', 'managed cloud hosting', 'free hosting', 'free domain with ssd hosting', 'ssd hosting'],
            'subcategories' => [
                'cpanel' => ['cpanel'],
                'cyberpanel' => ['cyberpanel', 'litespeed'],
                'free' => ['free'],
            ]
        ],
        'windows' => [
            'patterns' => ['windows hosting', 'asp.net'],
            'parent' => 'hosting', // Also mark as hosting
        ],
        'reseller' => [
            'patterns' => ['reseller hosting', 'reseller api'],
            'parent' => 'hosting',
        ],
        'vps' => [
            'patterns' => ['vps hosting', 'kenya vps', 'vps', 'virtual private', 'cloud server', 'linux cloud servers', 'windows vps'],
            'subcategories' => [
                'managed' => ['managed', 'managed cloud'],
                'windows' => ['windows'],
                'kenya' => ['kenya'],
            ]
        ],
        'dedicated' => [
            'patterns' => ['dedicated', 'bare metal'],
        ],
        'ssl' => [
            'patterns' => ['ssl', 'certificate'],
        ],
        'email' => [
            'patterns' => ['email', 'workplace', 'workspace', 'workplace pro emails'],
        ],
        'domains' => [
            'patterns' => ['domain'],
            'subcategories' => [
                'transfer' => ['transfer'],
                'free' => ['free'],
            ]
        ],
        'builder' => [
            'patterns' => ['websites', 'website builder', 'ai'],
            'aliases' => ['ai'], // Also set ai category
        ],
        'shop' => [
            'patterns' => ['online shop', 'shop', 'ecommerce'],
            'aliases' => ['ecommerce'],
        ],
        'seo' => [
            'patterns' => ['seo', 'local seo'],
        ],
        'developer' => [
            'patterns' => ['hire developer'],
        ],
        'vault' => [
            'patterns' => ['cloudoon vault'],
        ],
    ];

    // Match patterns
    foreach ($pattern_map as $category => $config) {
        foreach ($config['patterns'] as $pattern) {
            if (str_contains($group_lower, $pattern)) {
                $categories[$category] = true;

                // Set parent category if defined
                if (isset($config['parent'])) {
                    $categories[$config['parent']] = true;
                }

                // Set aliases if defined
                if (isset($config['aliases'])) {
                    foreach ($config['aliases'] as $alias) {
                        $categories[$alias] = true;
                    }
                }

                // Check subcategories if defined
                if (isset($config['subcategories'])) {
                    foreach ($config['subcategories'] as $subcat => $subpatterns) {
                        foreach ($subpatterns as $subpattern) {
                            if (str_contains($group_lower, $subpattern)) {
                                $categories[$subcat] = true;
                            }
                        }
                    }
                }

                break; // Found match, move to next category
            }
        }
    }

    // Metadata override (allows manual category assignment)
    if (!empty($metadata['category'])) {
        $cats = array_map('trim', explode(',', $metadata['category']));
        foreach ($cats as $cat) {
            if (!empty($cat)) {
                $categories[strtolower($cat)] = true;
            }
        }
    }

    return $categories ?: ['other' => true];
}

// ---- Helper Functions for HTML Generation ----

/**
 * Render info box with consistent styling
 */
function whx_render_info_box($message, $type = 'info') {
    $colors = [
        'info' => ['bg' => '#E7F3FF', 'border' => '#0891B2', 'icon' => 'ℹ️'],
        'warning' => ['bg' => '#FEF3C7', 'border' => '#F59E0B', 'icon' => '⚠️'],
        'success' => ['bg' => '#D1FAE5', 'border' => '#10B981', 'icon' => '✓'],
        'error' => ['bg' => '#FEE2E2', 'border' => '#EF4444', 'icon' => '✗'],
    ];

    $color = $colors[$type] ?? $colors['info'];

    echo '<div style="padding:12px;background:' . esc_attr($color['bg']) . ';border-left:4px solid ' . esc_attr($color['border']) . ';border-radius:4px;margin:15px 0;">';
    echo wp_kses_post($message);
    echo '</div>';
}

/**
 * Render setting row
 */
function whx_render_setting_row($args) {
    $defaults = [
        'label' => '',
        'name' => '',
        'type' => 'text',
        'value' => '',
        'description' => '',
        'options' => [],
    ];

    $args = wp_parse_args($args, $defaults);
    ?>
    <tr>
        <th scope="row" style="width:250px;"><?php echo esc_html($args['label']); ?></th>
        <td>
            <?php if ($args['type'] === 'checkbox'): ?>
                <label>
                    <input type="checkbox" name="<?php echo esc_attr($args['name']); ?>" <?php checked($args['value']); ?>>
                    <?php echo esc_html($args['description']); ?>
                </label>
            <?php elseif ($args['type'] === 'select'): ?>
                <select name="<?php echo esc_attr($args['name']); ?>">
                    <?php foreach ($args['options'] as $val => $label): ?>
                        <option value="<?php echo esc_attr($val); ?>" <?php selected($args['value'], $val); ?>>
                            <?php echo esc_html($label); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <?php if ($args['description']): ?>
                    <p class="description"><?php echo esc_html($args['description']); ?></p>
                <?php endif; ?>
            <?php elseif ($args['type'] === 'number'): ?>
                <input type="number" name="<?php echo esc_attr($args['name']); ?>" value="<?php echo esc_attr($args['value']); ?>" class="small-text" min="1">
                <?php if ($args['description']): ?>
                    <p class="description"><?php echo esc_html($args['description']); ?></p>
                <?php endif; ?>
            <?php else: ?>
                <input type="text" name="<?php echo esc_attr($args['name']); ?>" value="<?php echo esc_attr($args['value']); ?>" class="regular-text">
                <?php if ($args['description']): ?>
                    <p class="description"><?php echo esc_html($args['description']); ?></p>
                <?php endif; ?>
            <?php endif; ?>
        </td>
    </tr>
    <?php
}

/**
 * Render pricing display
 */
function whx_render_pricing_display($pricing) {
    if (empty($pricing)) return '';

    ob_start();
    ?>
    <div class="whx-promo-card__pricing">
        <?php if (!empty($pricing['price_now'])): ?>
            <span class="whx-promo-card__price-now"><?php echo esc_html($pricing['price_now']); ?></span>
            <?php if (!empty($pricing['price_period'])): ?>
                <span class="whx-promo-card__price-period">/<?php echo esc_html($pricing['price_period']); ?></span>
            <?php elseif (!empty($pricing['is_onetime'])): ?>
                <span class="whx-promo-card__price-period">one-time</span>
            <?php endif; ?>
        <?php endif; ?>

        <?php if (!empty($pricing['price_was']) && $pricing['price_was'] !== $pricing['price_now']): ?>
            <span class="whx-promo-card__price-was"><?php echo esc_html($pricing['price_was']); ?></span>
        <?php endif; ?>
    </div>
    <?php
    return ob_get_clean();
}
