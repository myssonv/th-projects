<?php
/**
 * Black Friday Hero Banner - Dynamic & Configurable with Promo Codes
 * Add this via Code Snippets WP Lite Plugin
 * Location: After Header / Before Content
 *
 * REQUIRES: promo-codes-config.php to be loaded first
 */

// Load promo codes configuration if not already loaded
if (!function_exists('whx_get_promo_code')) {
    require_once __DIR__ . '/promo-codes-config.php';
}

// ==================== CONFIGURATION ====================
// Page-specific banner configurations
$banner_configs = array(
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
        'countdown_end' => '2025-11-29 23:59:59', // YYYY-MM-DD HH:MM:SS
        'promo_code_group' => 'hosting', // Links to promo-codes-config.php
        'show_promo_code' => true,
    ),
    'wordpress' => array(
        'enabled' => true,
        'discount' => '50',
        'heading' => 'This Black Friday, be seen, get ready, stay ahead.',
        'subheading' => 'Get your WordPress site ready for Black Friday',
        'price' => '1.00',
        'bonus' => 'free for 6 months',
        'features' => array(
            array('icon' => '⚡', 'text' => 'Managed WordPress hosting'),
            array('icon' => '🚀', 'text' => 'Built with Rocket.net'),
        ),
        'cta_text' => 'See plans',
        'cta_url' => '#plans',
        'guarantee' => null,
        'background_color' => '#1a2332',
        'accent_color' => '#00D9A3',
        'show_graphic' => true,
        'countdown_end' => '2025-11-29 23:59:59',
        'promo_code_group' => 'hosting',
        'show_promo_code' => true,
    ),
    'cloud' => array(
        'enabled' => true,
        'discount' => '50',
        'heading' => 'Black Friday 2025',
        'subheading' => 'Up to {discount}% off a wide selection of products',
        'price' => null,
        'bonus' => null,
        'features' => array(
            array('icon' => '☁️', 'text' => 'Public Cloud hosting'),
            array('icon' => '🖥️', 'text' => 'Dedicated servers'),
            array('icon' => '🌐', 'text' => 'Domain names'),
        ),
        'cta_text' => 'View offers',
        'cta_url' => '#offers',
        'guarantee' => null,
        'background_color' => '#000080',
        'accent_color' => '#FFFFFF',
        'show_graphic' => false,
        'countdown_end' => '2025-11-29 23:59:59',
        'promo_code_group' => 'whmcs_store',
        'show_promo_code' => true,
    ),
    // VPS Configuration
    'vps' => array(
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
        ),
        'cta_text' => 'Choose VPS plan',
        'cta_url' => '/vps-hosting',
        'guarantee' => '7-day money-back guarantee',
        'background_color' => '#1e1e1e',
        'accent_color' => '#ff6b35',
        'show_graphic' => true,
        'countdown_end' => '2025-11-29 23:59:59',
        'promo_code_group' => 'vps',
        'show_promo_code' => true,
    ),
    // Domains Configuration
    'domains' => array(
        'enabled' => true,
        'discount' => '50',
        'heading' => 'Domain Name Sale',
        'subheading' => 'Get your perfect domain with {discount}% off',
        'price' => '0.99',
        'bonus' => 'First year only',
        'features' => array(
            array('icon' => '🌐', 'text' => '.com, .net, .org & more'),
            array('icon' => '🔒', 'text' => 'Free WHOIS privacy'),
            array('icon' => '📧', 'text' => 'Free email forwarding'),
        ),
        'cta_text' => 'Search domains',
        'cta_url' => '/cloud/cart.php?a=add&domain=register',
        'guarantee' => null,
        'background_color' => '#2d3748',
        'accent_color' => '#48bb78',
        'show_graphic' => true,
        'countdown_end' => '2025-11-29 23:59:59',
        'promo_code_group' => 'domains',
        'show_promo_code' => true,
    ),
    // SSL Configuration
    'ssl' => array(
        'enabled' => true,
        'discount' => '50',
        'heading' => 'Secure Your Site This Black Friday',
        'subheading' => '{discount}% off SSL certificates',
        'price' => '9.99',
        'bonus' => 'First year',
        'features' => array(
            array('icon' => '🔒', 'text' => 'Industry-standard encryption'),
            array('icon' => '✓', 'text' => 'Boost SEO rankings'),
            array('icon' => '🛡️', 'text' => 'Protect customer data'),
        ),
        'cta_text' => 'Get SSL',
        'cta_url' => '/ssl',
        'guarantee' => '30-day money-back guarantee',
        'background_color' => '#1a1625',
        'accent_color' => '#10b981',
        'show_graphic' => true,
        'countdown_end' => '2025-11-29 23:59:59',
        'promo_code_group' => 'ssl',
        'show_promo_code' => true,
    ),
);

// ==================== PAGE DETECTION ====================
// Determine which configuration to use based on current page
function bf_get_current_config($configs) {
    // Default configuration
    $default_config = array(
        'enabled' => false,
    );

    // Check for specific page slugs or query parameters
    global $post;

    // Method 1: Check by page slug
    if (is_page()) {
        $slug = $post->post_name;
        if (isset($configs[$slug])) {
            return $configs[$slug];
        }
    }

    // Method 2: Check by custom query parameter (?bf_banner=hosting)
    if (isset($_GET['bf_banner']) && isset($configs[$_GET['bf_banner']])) {
        return $configs[$_GET['bf_banner']];
    }

    // Method 3: Check by category or custom post type
    if (is_singular()) {
        $categories = get_the_category();
        if ($categories) {
            foreach ($categories as $category) {
                if (isset($configs[$category->slug])) {
                    return $configs[$category->slug];
                }
            }
        }
    }

    // Method 4: Check for specific keywords in URL
    $current_url = $_SERVER['REQUEST_URI'];
    foreach ($configs as $key => $config) {
        if (strpos($current_url, $key) !== false) {
            return $config;
        }
    }

    return $default_config;
}

// Get current page configuration
$config = bf_get_current_config($banner_configs);

// Don't show banner if not enabled
if (!$config['enabled']) {
    return;
}

// Replace placeholders in text
$heading = isset($config['heading']) ? $config['heading'] : '';
$subheading = isset($config['subheading']) ? str_replace('{discount}', $config['discount'], $config['subheading']) : '';

// Get promo code details if configured
$promo_data = null;
if (isset($config['show_promo_code']) && $config['show_promo_code'] && isset($config['promo_code_group'])) {
    $promo_data = whx_get_promo_code($config['promo_code_group']);
    // Override CTA URL with promo URL if promo code is set
    if ($promo_data && isset($promo_data['url'])) {
        $config['cta_url'] = $promo_data['url'];
    }
}

?>

<!-- Black Friday Hero Banner -->
<style>
.bf-hero-banner {
    position: relative;
    width: 100%;
    background: <?php echo $config['background_color']; ?>;
    color: #ffffff;
    overflow: hidden;
    padding: 60px 20px;
    box-sizing: border-box;
}

.bf-hero-container {
    max-width: 1200px;
    margin: 0 auto;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 60px;
    position: relative;
    z-index: 2;
}

.bf-hero-content {
    flex: 1;
    max-width: 600px;
}

.bf-hero-heading {
    font-size: 48px;
    font-weight: 700;
    margin: 0 0 20px 0;
    line-height: 1.1;
}

.bf-hero-subheading {
    font-size: 24px;
    margin: 0 0 30px 0;
    opacity: 0.9;
    color: <?php echo $config['accent_color']; ?>;
}

.bf-hero-features {
    list-style: none;
    padding: 0;
    margin: 0 0 30px 0;
}

.bf-hero-features li {
    display: flex;
    align-items: center;
    gap: 12px;
    margin-bottom: 12px;
    font-size: 18px;
}

.bf-hero-features .icon {
    color: <?php echo $config['accent_color']; ?>;
    font-size: 20px;
    font-weight: bold;
}

.bf-hero-pricing {
    margin-bottom: 30px;
}

.bf-hero-price {
    font-size: 42px;
    font-weight: 700;
    margin-bottom: 10px;
}

.bf-hero-price .currency {
    font-size: 24px;
    vertical-align: super;
}

.bf-hero-price .amount {
    font-size: 56px;
}

.bf-hero-bonus {
    font-size: 24px;
    color: <?php echo $config['accent_color']; ?>;
    font-weight: 600;
    margin-bottom: 20px;
}

.bf-hero-cta {
    display: inline-block;
    background: <?php echo $config['accent_color']; ?>;
    color: <?php echo $config['background_color']; ?>;
    padding: 18px 40px;
    border-radius: 8px;
    text-decoration: none;
    font-size: 18px;
    font-weight: 700;
    transition: transform 0.2s, box-shadow 0.2s;
    border: none;
    cursor: pointer;
}

.bf-hero-cta:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(0,0,0,0.3);
}

.bf-hero-guarantee {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-top: 20px;
    font-size: 14px;
    opacity: 0.8;
}

.bf-hero-visual {
    flex: 1;
    display: flex;
    align-items: center;
    justify-content: center;
    position: relative;
}

.bf-hero-graphic {
    font-size: 180px;
    font-weight: 900;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 50%, #f093fb 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    text-shadow: 0 0 40px rgba(102, 126, 234, 0.5);
    position: relative;
}

.bf-hero-discount-badge {
    position: absolute;
    top: -20px;
    right: -20px;
    background: <?php echo $config['accent_color']; ?>;
    color: <?php echo $config['background_color']; ?>;
    width: 100px;
    height: 100px;
    border-radius: 50%;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    font-weight: 900;
    box-shadow: 0 4px 20px rgba(0,0,0,0.3);
    animation: pulse 2s infinite;
}

.bf-hero-discount-badge .percentage {
    font-size: 32px;
    line-height: 1;
}

.bf-hero-discount-badge .off {
    font-size: 14px;
}

.bf-countdown {
    background: rgba(255, 255, 255, 0.1);
    border-radius: 8px;
    padding: 20px;
    margin-bottom: 30px;
    backdrop-filter: blur(10px);
}

.bf-countdown-label {
    font-size: 14px;
    text-transform: uppercase;
    letter-spacing: 1px;
    margin-bottom: 12px;
    opacity: 0.8;
}

.bf-countdown-timer {
    display: flex;
    gap: 20px;
}

.bf-countdown-item {
    text-align: center;
}

.bf-countdown-value {
    font-size: 36px;
    font-weight: 700;
    display: block;
    color: <?php echo $config['accent_color']; ?>;
}

.bf-countdown-unit {
    font-size: 12px;
    text-transform: uppercase;
    opacity: 0.7;
    letter-spacing: 1px;
}

/* Promo Code Section */
.bf-promo-code {
    background: rgba(255, 255, 255, 0.1);
    border-radius: 12px;
    padding: 16px 20px;
    margin-bottom: 24px;
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255, 255, 255, 0.15);
}

.bf-promo-code-label {
    font-size: 12px;
    text-transform: uppercase;
    letter-spacing: 1px;
    margin-bottom: 10px;
    opacity: 0.9;
    font-weight: 600;
}

.bf-promo-code-wrapper {
    display: flex;
    align-items: center;
    gap: 12px;
}

.bf-promo-code-display {
    flex: 1;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 12px 20px;
    background: rgba(255, 255, 255, 0.95);
    border-radius: 8px;
    font-family: 'SF Mono', Monaco, Consolas, monospace;
    font-size: 18px;
    font-weight: 700;
    color: <?php echo $config['background_color']; ?>;
    letter-spacing: 1px;
    user-select: all;
    cursor: pointer;
    transition: all 0.2s;
}

.bf-promo-code-display:hover {
    background: rgba(255, 255, 255, 1);
    transform: translateY(-1px);
}

.bf-copy-btn {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    padding: 12px 20px;
    background: <?php echo $config['accent_color']; ?>;
    color: <?php echo $config['background_color']; ?>;
    border: none;
    border-radius: 8px;
    font-size: 14px;
    font-weight: 700;
    cursor: pointer;
    transition: all 0.2s;
    white-space: nowrap;
}

.bf-copy-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.2);
}

.bf-copy-btn:active {
    transform: translateY(0);
}

.bf-copy-btn--copied {
    background: #10b981;
    color: #ffffff;
}

.bf-copy-btn svg {
    width: 16px;
    height: 16px;
}

@keyframes pulse {
    0%, 100% {
        transform: scale(1);
    }
    50% {
        transform: scale(1.05);
    }
}

/* Background decorative elements */
.bf-hero-banner::before {
    content: '';
    position: absolute;
    top: -50%;
    right: -10%;
    width: 600px;
    height: 600px;
    background: radial-gradient(circle, <?php echo $config['accent_color']; ?>20 0%, transparent 70%);
    border-radius: 50%;
    z-index: 1;
}

.bf-hero-banner::after {
    content: '';
    position: absolute;
    bottom: -30%;
    left: -5%;
    width: 400px;
    height: 400px;
    background: radial-gradient(circle, <?php echo $config['accent_color']; ?>15 0%, transparent 70%);
    border-radius: 50%;
    z-index: 1;
}

/* Responsive Design */
@media (max-width: 968px) {
    .bf-hero-container {
        flex-direction: column;
        gap: 40px;
        text-align: center;
    }

    .bf-hero-content {
        max-width: 100%;
    }

    .bf-hero-heading {
        font-size: 36px;
    }

    .bf-hero-subheading {
        font-size: 20px;
    }

    .bf-hero-features {
        display: inline-block;
        text-align: left;
    }

    .bf-hero-graphic {
        font-size: 120px;
    }
}

@media (max-width: 640px) {
    .bf-hero-banner {
        padding: 40px 20px;
    }

    .bf-hero-heading {
        font-size: 28px;
    }

    .bf-hero-subheading {
        font-size: 16px;
    }

    .bf-hero-features li {
        font-size: 16px;
    }

    .bf-hero-price .amount {
        font-size: 42px;
    }

    .bf-countdown-timer {
        gap: 12px;
    }

    .bf-countdown-value {
        font-size: 28px;
    }

    .bf-hero-graphic {
        font-size: 80px;
    }

    .bf-promo-code {
        padding: 12px 16px;
    }

    .bf-promo-code-wrapper {
        flex-direction: column;
        gap: 10px;
    }

    .bf-promo-code-display {
        width: 100%;
        font-size: 16px;
        padding: 10px 16px;
    }

    .bf-copy-btn {
        width: 100%;
        justify-content: center;
    }
}
</style>

<div class="bf-hero-banner">
    <div class="bf-hero-container">
        <div class="bf-hero-content">
            <h1 class="bf-hero-heading"><?php echo esc_html($heading); ?></h1>

            <?php if ($subheading): ?>
            <p class="bf-hero-subheading"><?php echo esc_html($subheading); ?></p>
            <?php endif; ?>

            <?php if (!empty($config['features'])): ?>
            <ul class="bf-hero-features">
                <?php foreach ($config['features'] as $feature): ?>
                <li>
                    <span class="icon"><?php echo $feature['icon']; ?></span>
                    <span><?php echo esc_html($feature['text']); ?></span>
                </li>
                <?php endforeach; ?>
            </ul>
            <?php endif; ?>

            <?php if (isset($config['countdown_end'])): ?>
            <div class="bf-countdown">
                <div class="bf-countdown-label">Offer ends in:</div>
                <div class="bf-countdown-timer" id="bf-countdown-timer" data-end="<?php echo esc_attr($config['countdown_end']); ?>">
                    <div class="bf-countdown-item">
                        <span class="bf-countdown-value" id="bf-days">00</span>
                        <span class="bf-countdown-unit">Days</span>
                    </div>
                    <div class="bf-countdown-item">
                        <span class="bf-countdown-value" id="bf-hours">00</span>
                        <span class="bf-countdown-unit">Hours</span>
                    </div>
                    <div class="bf-countdown-item">
                        <span class="bf-countdown-value" id="bf-minutes">00</span>
                        <span class="bf-countdown-unit">Mins</span>
                    </div>
                    <div class="bf-countdown-item">
                        <span class="bf-countdown-value" id="bf-seconds">00</span>
                        <span class="bf-countdown-unit">Secs</span>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <?php if ($promo_data): ?>
            <div class="bf-promo-code">
                <div class="bf-promo-code-label">🎁 Use promo code:</div>
                <div class="bf-promo-code-wrapper">
                    <div class="bf-promo-code-display" id="bf-promo-code-text" onclick="bfSelectPromoCode()">
                        <?php echo esc_html($promo_data['code']); ?>
                    </div>
                    <button class="bf-copy-btn" id="bf-copy-promo-btn" onclick="bfCopyPromoCode()">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                        </svg>
                        <span id="bf-copy-text">Copy</span>
                    </button>
                </div>
            </div>
            <?php endif; ?>

            <?php if ($config['price']): ?>
            <div class="bf-hero-pricing">
                <div class="bf-hero-price">
                    <span class="from">From </span>
                    <span class="currency">US$</span>
                    <span class="amount"><?php echo esc_html($config['price']); ?></span>
                    <span class="period">/mo</span>
                </div>
                <?php if ($config['bonus']): ?>
                <div class="bf-hero-bonus"><?php echo esc_html($config['bonus']); ?></div>
                <?php endif; ?>
            </div>
            <?php endif; ?>

            <a href="<?php echo esc_url($config['cta_url']); ?>" class="bf-hero-cta">
                <?php echo esc_html($config['cta_text']); ?>
            </a>

            <?php if ($config['guarantee']): ?>
            <div class="bf-hero-guarantee">
                <span>🛡️</span>
                <span><?php echo esc_html($config['guarantee']); ?></span>
            </div>
            <?php endif; ?>
        </div>

        <?php if ($config['show_graphic']): ?>
        <div class="bf-hero-visual">
            <div class="bf-hero-graphic">
                <?php echo esc_html($config['discount']); ?>%
                <div class="bf-hero-discount-badge">
                    <span class="percentage"><?php echo esc_html($config['discount']); ?>%</span>
                    <span class="off">OFF</span>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<script>
// Countdown Timer
(function() {
    const countdownTimer = document.getElementById('bf-countdown-timer');
    if (!countdownTimer) return;

    const endDate = new Date(countdownTimer.getAttribute('data-end')).getTime();

    const daysEl = document.getElementById('bf-days');
    const hoursEl = document.getElementById('bf-hours');
    const minutesEl = document.getElementById('bf-minutes');
    const secondsEl = document.getElementById('bf-seconds');

    function updateCountdown() {
        const now = new Date().getTime();
        const distance = endDate - now;

        if (distance < 0) {
            daysEl.textContent = '00';
            hoursEl.textContent = '00';
            minutesEl.textContent = '00';
            secondsEl.textContent = '00';
            return;
        }

        const days = Math.floor(distance / (1000 * 60 * 60 * 24));
        const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
        const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
        const seconds = Math.floor((distance % (1000 * 60)) / 1000);

        daysEl.textContent = String(days).padStart(2, '0');
        hoursEl.textContent = String(hours).padStart(2, '0');
        minutesEl.textContent = String(minutes).padStart(2, '0');
        secondsEl.textContent = String(seconds).padStart(2, '0');
    }

    updateCountdown();
    setInterval(updateCountdown, 1000);
})();

// Promo Code Functions
function bfSelectPromoCode() {
    const codeElement = document.getElementById('bf-promo-code-text');
    if (codeElement) {
        const range = document.createRange();
        range.selectNodeContents(codeElement);
        const selection = window.getSelection();
        selection.removeAllRanges();
        selection.addRange(range);
    }
}

function bfCopyPromoCode() {
    const codeElement = document.getElementById('bf-promo-code-text');
    const copyBtn = document.getElementById('bf-copy-promo-btn');
    const copyText = document.getElementById('bf-copy-text');

    if (!codeElement || !copyBtn || !copyText) return;

    const code = codeElement.textContent;

    // Modern clipboard API
    if (navigator.clipboard && navigator.clipboard.writeText) {
        navigator.clipboard.writeText(code).then(function() {
            showCopySuccess(copyBtn, copyText);
        }).catch(function(err) {
            // Fallback to old method
            fallbackCopyToClipboard(code, copyBtn, copyText);
        });
    } else {
        // Fallback for older browsers
        fallbackCopyToClipboard(code, copyBtn, copyText);
    }
}

function fallbackCopyToClipboard(text, copyBtn, copyText) {
    const textArea = document.createElement('textarea');
    textArea.value = text;
    textArea.style.position = 'fixed';
    textArea.style.left = '-999999px';
    document.body.appendChild(textArea);
    textArea.focus();
    textArea.select();

    try {
        document.execCommand('copy');
        showCopySuccess(copyBtn, copyText);
    } catch (err) {
        console.error('Failed to copy:', err);
    }

    document.body.removeChild(textArea);
}

function showCopySuccess(copyBtn, copyText) {
    copyBtn.classList.add('bf-copy-btn--copied');
    copyText.textContent = 'Copied!';

    setTimeout(function() {
        copyBtn.classList.remove('bf-copy-btn--copied');
        copyText.textContent = 'Copy';
    }, 2000);
}
</script>
