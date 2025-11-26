<?php
/**
 * Black Friday Top Banner - Compact Header Version
 * Add this via Code Snippets WP Lite Plugin
 * Location: Before Header / Top of Page
 *
 * This is a slim, compact banner that sits at the very top of the page
 */

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
);

// Don't show if disabled
if (!$top_banner_config['enabled']) {
    return;
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
</script>
