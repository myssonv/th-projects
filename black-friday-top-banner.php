<?php
/**
 * Black Friday Top Banner - Compact Header Version with Promo Code
 * Add this via Code Snippets WP Lite Plugin
 * Location: Before Header / Top of Page
 *
 * This is a slim, compact banner that sits at the very top of the page
 * REQUIRES: promo-codes-config.php to be loaded first
 */

// Load promo codes configuration if not already loaded
if (!function_exists('whx_get_promo_code')) {
    require_once __DIR__ . '/promo-codes-config.php';
}

// ==================== CONFIGURATION ====================
$top_banner_config = array(
    'enabled' => true,
    'message' => 'Black Friday: Get extra months free',
    'background_color' => '#FF1B6D',
    'text_color' => '#FFFFFF',
    'countdown_end' => '2025-11-29 23:59:59',
    'show_countdown' => true,
    'link_url' => '#pricing',  // Optional - set to null to disable link
    'height' => '60px',
    'dismiss_button' => true,  // Allow users to close the banner
    'promo_code_group' => 'default',  // Links to promo-codes-config.php
    'show_promo_code' => true,
);

// Don't show if disabled
if (!$top_banner_config['enabled']) {
    return;
}

// Get promo code details if configured
$promo_data = null;
if (isset($top_banner_config['show_promo_code']) && $top_banner_config['show_promo_code'] && isset($top_banner_config['promo_code_group'])) {
    $promo_data = whx_get_promo_code($top_banner_config['promo_code_group']);
    // Override link URL with promo URL if promo code is set
    if ($promo_data && isset($promo_data['url'])) {
        $top_banner_config['link_url'] = $promo_data['url'];
    }
}

// Check if user dismissed the banner (uses session storage via JS)
?>

<!-- Black Friday Top Banner -->
<style>
.bf-top-banner {
    position: relative;
    width: 100%;
    background: <?php echo $top_banner_config['background_color']; ?>;
    color: <?php echo $top_banner_config['text_color']; ?>;
    height: <?php echo $top_banner_config['height']; ?>;
    display: flex;
    align-items: center;
    justify-content: center;
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
    font-size: 16px;
    font-weight: 600;
    z-index: 9999;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

.bf-top-banner.hidden {
    display: none;
}

.bf-top-banner-content {
    display: flex;
    align-items: center;
    gap: 30px;
    padding: 0 20px;
}

.bf-top-banner-message {
    white-space: nowrap;
}

.bf-top-banner-countdown {
    display: flex;
    gap: 15px;
    align-items: center;
}

.bf-top-banner-countdown-item {
    display: flex;
    align-items: baseline;
    gap: 2px;
}

.bf-top-banner-countdown-value {
    font-size: 20px;
    font-weight: 700;
    min-width: 28px;
    text-align: center;
}

.bf-top-banner-countdown-unit {
    font-size: 12px;
    text-transform: uppercase;
    opacity: 0.9;
    font-weight: 500;
}

.bf-top-banner-link {
    color: inherit;
    text-decoration: none;
    display: flex;
    align-items: center;
    width: 100%;
    justify-content: center;
    gap: 30px;
}

.bf-top-banner-link:hover {
    opacity: 0.9;
}

.bf-top-banner-dismiss {
    position: absolute;
    right: 20px;
    top: 50%;
    transform: translateY(-50%);
    background: none;
    border: none;
    color: <?php echo $top_banner_config['text_color']; ?>;
    font-size: 24px;
    cursor: pointer;
    padding: 5px 10px;
    opacity: 0.8;
    transition: opacity 0.2s;
    line-height: 1;
}

.bf-top-banner-dismiss:hover {
    opacity: 1;
}

/* Promo Code - Compact Inline Version */
.bf-top-promo-code {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 4px 12px;
    background: rgba(255, 255, 255, 0.2);
    border-radius: 20px;
    font-size: 13px;
    font-weight: 700;
    letter-spacing: 0.5px;
    backdrop-filter: blur(10px);
}

.bf-top-promo-code-label {
    font-size: 11px;
    opacity: 0.9;
    font-weight: 600;
    text-transform: uppercase;
}

.bf-top-promo-code-text {
    font-family: 'SF Mono', Monaco, Consolas, monospace;
    font-size: 14px;
    font-weight: 700;
    cursor: pointer;
    user-select: all;
    padding: 2px 8px;
    background: rgba(255, 255, 255, 0.3);
    border-radius: 4px;
    transition: all 0.2s;
}

.bf-top-promo-code-text:hover {
    background: rgba(255, 255, 255, 0.4);
}

.bf-top-copy-btn {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    padding: 4px 10px;
    background: rgba(255, 255, 255, 0.9);
    color: <?php echo $top_banner_config['background_color']; ?>;
    border: none;
    border-radius: 12px;
    font-size: 11px;
    font-weight: 700;
    cursor: pointer;
    transition: all 0.2s;
}

.bf-top-copy-btn:hover {
    background: rgba(255, 255, 255, 1);
    transform: scale(1.05);
}

.bf-top-copy-btn--copied {
    background: #10b981;
    color: #ffffff;
}

.bf-top-copy-btn svg {
    width: 12px;
    height: 12px;
}

@media (max-width: 768px) {
    .bf-top-banner {
        height: auto;
        min-height: <?php echo $top_banner_config['height']; ?>;
        padding: 10px 0;
    }

    .bf-top-banner-content {
        flex-direction: column;
        gap: 10px;
        padding: 0 50px 0 20px;
    }

    .bf-top-banner-message {
        font-size: 14px;
        white-space: normal;
        text-align: center;
    }

    .bf-top-banner-countdown {
        gap: 10px;
    }

    .bf-top-banner-countdown-value {
        font-size: 18px;
        min-width: 24px;
    }

    .bf-top-banner-countdown-unit {
        font-size: 11px;
    }

    .bf-top-promo-code {
        font-size: 12px;
        padding: 3px 10px;
    }

    .bf-top-promo-code-text {
        font-size: 12px;
        padding: 2px 6px;
    }

    .bf-top-copy-btn {
        font-size: 10px;
        padding: 3px 8px;
    }
}

@media (max-width: 480px) {
    .bf-top-banner-countdown {
        gap: 8px;
    }

    .bf-top-banner-countdown-value {
        font-size: 16px;
        min-width: 20px;
    }

    .bf-top-banner-message {
        font-size: 13px;
    }

    .bf-top-promo-code {
        font-size: 11px;
        gap: 6px;
    }

    .bf-top-promo-code-label {
        display: none; /* Hide label on very small screens */
    }
}
</style>

<div class="bf-top-banner" id="bf-top-banner">
    <?php if ($top_banner_config['link_url']): ?>
    <a href="<?php echo esc_url($top_banner_config['link_url']); ?>" class="bf-top-banner-link">
    <?php endif; ?>

        <div class="bf-top-banner-content">
            <div class="bf-top-banner-message">
                <?php echo esc_html($top_banner_config['message']); ?>
            </div>

            <?php if ($promo_data): ?>
            <div class="bf-top-promo-code">
                <span class="bf-top-promo-code-label">Code:</span>
                <span class="bf-top-promo-code-text" id="bf-top-promo-text" onclick="bfTopSelectPromoCode()">
                    <?php echo esc_html($promo_data['code']); ?>
                </span>
                <button class="bf-top-copy-btn" id="bf-top-copy-btn" onclick="bfTopCopyPromoCode()">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                    </svg>
                    <span id="bf-top-copy-text">Copy</span>
                </button>
            </div>
            <?php endif; ?>

            <?php if ($top_banner_config['show_countdown']): ?>
            <div class="bf-top-banner-countdown" id="bf-top-countdown" data-end="<?php echo esc_attr($top_banner_config['countdown_end']); ?>">
                <div class="bf-top-banner-countdown-item">
                    <span class="bf-top-banner-countdown-value" id="bf-top-days">00</span>
                    <span class="bf-top-banner-countdown-unit">D</span>
                </div>
                <div class="bf-top-banner-countdown-item">
                    <span class="bf-top-banner-countdown-value" id="bf-top-hours">00</span>
                    <span class="bf-top-banner-countdown-unit">H</span>
                </div>
                <div class="bf-top-banner-countdown-item">
                    <span class="bf-top-banner-countdown-value" id="bf-top-minutes">00</span>
                    <span class="bf-top-banner-countdown-unit">M</span>
                </div>
                <div class="bf-top-banner-countdown-item">
                    <span class="bf-top-banner-countdown-value" id="bf-top-seconds">00</span>
                    <span class="bf-top-banner-countdown-unit">S</span>
                </div>
            </div>
            <?php endif; ?>
        </div>

    <?php if ($top_banner_config['link_url']): ?>
    </a>
    <?php endif; ?>

    <?php if ($top_banner_config['dismiss_button']): ?>
    <button class="bf-top-banner-dismiss" id="bf-top-dismiss" aria-label="Close banner">&times;</button>
    <?php endif; ?>
</div>

<script>
(function() {
    // Check if banner was dismissed
    if (sessionStorage.getItem('bf_top_banner_dismissed') === 'true') {
        document.getElementById('bf-top-banner').classList.add('hidden');
        return;
    }

    // Dismiss functionality
    const dismissBtn = document.getElementById('bf-top-dismiss');
    if (dismissBtn) {
        dismissBtn.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            document.getElementById('bf-top-banner').classList.add('hidden');
            sessionStorage.setItem('bf_top_banner_dismissed', 'true');
        });
    }

    // Countdown timer
    const countdown = document.getElementById('bf-top-countdown');
    if (!countdown) return;

    const endDate = new Date(countdown.getAttribute('data-end')).getTime();

    const daysEl = document.getElementById('bf-top-days');
    const hoursEl = document.getElementById('bf-top-hours');
    const minutesEl = document.getElementById('bf-top-minutes');
    const secondsEl = document.getElementById('bf-top-seconds');

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

// Promo Code Functions for Top Banner
function bfTopSelectPromoCode() {
    const codeElement = document.getElementById('bf-top-promo-text');
    if (codeElement) {
        const range = document.createRange();
        range.selectNodeContents(codeElement);
        const selection = window.getSelection();
        selection.removeAllRanges();
        selection.addRange(range);
    }
}

function bfTopCopyPromoCode() {
    const codeElement = document.getElementById('bf-top-promo-text');
    const copyBtn = document.getElementById('bf-top-copy-btn');
    const copyText = document.getElementById('bf-top-copy-text');

    if (!codeElement || !copyBtn || !copyText) return;

    const code = codeElement.textContent.trim();

    // Modern clipboard API
    if (navigator.clipboard && navigator.clipboard.writeText) {
        navigator.clipboard.writeText(code).then(function() {
            showTopCopySuccess(copyBtn, copyText);
        }).catch(function(err) {
            // Fallback to old method
            fallbackTopCopyToClipboard(code, copyBtn, copyText);
        });
    } else {
        // Fallback for older browsers
        fallbackTopCopyToClipboard(code, copyBtn, copyText);
    }
}

function fallbackTopCopyToClipboard(text, copyBtn, copyText) {
    const textArea = document.createElement('textarea');
    textArea.value = text;
    textArea.style.position = 'fixed';
    textArea.style.left = '-999999px';
    document.body.appendChild(textArea);
    textArea.focus();
    textArea.select();

    try {
        document.execCommand('copy');
        showTopCopySuccess(copyBtn, copyText);
    } catch (err) {
        console.error('Failed to copy:', err);
    }

    document.body.removeChild(textArea);
}

function showTopCopySuccess(copyBtn, copyText) {
    copyBtn.classList.add('bf-top-copy-btn--copied');
    copyText.textContent = '✓';

    setTimeout(function() {
        copyBtn.classList.remove('bf-top-copy-btn--copied');
        copyText.textContent = 'Copy';
    }, 2000);
}
</script>
