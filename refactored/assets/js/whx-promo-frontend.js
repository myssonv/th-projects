/**
 * WHX Promo Cards - Frontend JavaScript
 *
 * Handles domain search, copy buttons, and promocode persistence
 * Extracted from inline scripts for better caching and maintainability
 */
(function() {
    'use strict';

    // ---- Domain Search Handler ----
    /**
     * Direct cart add - Domain search form submission
     * Submits directly to WHMCS cart with domain parameters
     */
    function initDomainSearch() {
        document.addEventListener('DOMContentLoaded', function() {
            var forms = document.querySelectorAll('.whx-domain-search-spaceship');

            forms.forEach(function(form) {
                form.addEventListener('submit', function(e) {
                    e.preventDefault();

                    var input = form.querySelector('.whx-search-input');
                    var sld = input.value.trim().replace(/[^a-z0-9\-]/gi, '');
                    var tld = input.getAttribute('data-tld');
                    var promocode = form.querySelector('input[name="promocode"]').value;

                    if (sld && tld && promocode) {
                        var url = form.action +
                                  '?a=add' +
                                  '&domain=register' +
                                  '&sld=' + encodeURIComponent(sld) +
                                  '&tld=.' + encodeURIComponent(tld) +
                                  '&promocode=' + encodeURIComponent(promocode);

                        window.location.href = url;
                    } else {
                        alert('Please enter a valid domain name');
                    }
                });
            });
        });
    }

    // ---- Copy Button Handler ----
    /**
     * Copy promocode to clipboard with visual feedback
     */
    function initCopyButtons() {
        document.addEventListener('click', function(e) {
            var btn = e.target.closest('.whx-copy-btn');

            if (btn && navigator.clipboard) {
                var code = btn.getAttribute('data-code');

                navigator.clipboard.writeText(code).then(function() {
                    var origHTML = btn.innerHTML;

                    // Show checkmark icon
                    btn.innerHTML = '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"></polyline></svg>';
                    btn.classList.add('whx-copy-btn--copied');

                    // Reset after 2 seconds
                    setTimeout(function() {
                        btn.innerHTML = origHTML;
                        btn.classList.remove('whx-copy-btn--copied');
                    }, 2000);
                });
            }
        });
    }

    // ---- Promocode Bridge ----
    /**
     * WordPress → WHMCS promocode persistence
     * Captures promocode from URL and applies to all WHMCS links
     */
    var PromocodeBridge = {
        /**
         * Capture promocode from URL and store it
         */
        capture: function() {
            var urlParams = new URLSearchParams(window.location.search);
            var promocode = urlParams.get('promocode');

            if (promocode) {
                // Store in sessionStorage (survives page navigation)
                sessionStorage.setItem('whx_promocode', promocode);

                // Store in localStorage with 7-day expiry
                var expiry = new Date().getTime() + (7 * 24 * 60 * 60 * 1000);
                localStorage.setItem('whx_promocode', JSON.stringify({
                    code: promocode,
                    expiry: expiry
                }));

                console.log('[WHX] Promocode captured:', promocode);

                // Apply to WHMCS links on page
                this.applyToLinks(promocode);
            }
        },

        /**
         * Apply promocode to all WHMCS links
         */
        applyToLinks: function(promocode) {
            document.addEventListener('DOMContentLoaded', function() {
                var links = document.querySelectorAll('a[href*="/cloud"]');

                links.forEach(function(link) {
                    var href = link.getAttribute('href');

                    if (href && href.indexOf('promocode=') === -1) {
                        var separator = href.indexOf('?') !== -1 ? '&' : '?';
                        link.setAttribute('href', href + separator + 'promocode=' + encodeURIComponent(promocode));
                    }
                });

                console.log('[WHX] Promocode applied to', links.length, 'WHMCS links');
            });
        },

        /**
         * Auto-apply stored promocode on WHMCS pages
         */
        autoApply: function() {
            if (window.location.pathname.indexOf('/cloud') === -1) {
                return; // Not on WHMCS page
            }

            var storedPromo = sessionStorage.getItem('whx_promocode');

            // Check localStorage if not in sessionStorage
            if (!storedPromo) {
                var localData = localStorage.getItem('whx_promocode');

                if (localData) {
                    try {
                        var parsed = JSON.parse(localData);

                        // Check if not expired
                        if (parsed.expiry > new Date().getTime()) {
                            storedPromo = parsed.code;
                            sessionStorage.setItem('whx_promocode', storedPromo);
                        } else {
                            // Expired - remove it
                            localStorage.removeItem('whx_promocode');
                        }
                    } catch (e) {
                        console.error('[WHX] Error parsing stored promocode:', e);
                    }
                }
            }

            // Add promocode to URL if not already present
            if (storedPromo) {
                var urlParams = new URLSearchParams(window.location.search);

                if (!urlParams.get('promocode')) {
                    var newUrl = window.location.href;
                    var separator = newUrl.indexOf('?') !== -1 ? '&' : '?';

                    window.history.replaceState({}, '', newUrl + separator + 'promocode=' + encodeURIComponent(storedPromo));
                    console.log('[WHX] Promocode auto-applied:', storedPromo);
                }
            }
        }
    };

    // ---- Initialize All Features ----
    initDomainSearch();
    initCopyButtons();
    PromocodeBridge.capture();
    PromocodeBridge.autoApply();

})();
