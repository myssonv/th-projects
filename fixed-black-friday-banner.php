if (!defined('ABSPATH')) exit;

/**
 * Final Black Friday Banner - All features
 * - Admin table UI with bulk management (enable/disable all)
 * - Per-product enable/disable (no defaults, empty placeholders)
 * - Optional promo (blank allowed) -> "No promo code required"
 * - Blog combined banner on single posts (uses same schedule)
 * - Countdown based on admin start/end (datetime-local)
 * - WHMCS safety & client-side rewrite (override promocode always)
 * - Entire banner is clickable (button styled as div to avoid nested clickables)
 * - Banner inserted after #navigation with proper positioning
 * - Click tracking with analytics dashboard (loads instantly via AJAX)
 */

/* ------------------------
   HELPERS: defaults & groups
   ------------------------ */
function th_bf_default_config() {
    return [
        'enable' => '1',
        'start' => '',
        'end' => '',
        'default_cta' => '/promos',
        'show_on' => [
            'homepage' => '1',
            'archives' => '0',
            'blog_page' => '0',
            'single_posts' => '1',
            'pages' => '0',
            'all_pages' => '0'
        ],
        'exclude_urls' => '',
        'custom_products' => [],
        'groups' => [
            // Domains
            'general_domains' => ['show'=>'0','promo'=>'','title'=>'','sub'=>'','cta_text'=>'','cta_url'=>''],
            'ke_domains' => ['show'=>'0','promo'=>'','title'=>'','sub'=>'','cta_text'=>'','cta_url'=>''],
            'domain_transfer' => ['show'=>'0','promo'=>'','title'=>'','sub'=>'','cta_text'=>'','cta_url'=>''],
            'free_domains' => ['show'=>'0','promo'=>'','title'=>'','sub'=>'','cta_text'=>'','cta_url'=>''],
            'whois' => ['show'=>'0','promo'=>'','title'=>'','sub'=>'','cta_text'=>'','cta_url'=>''],
            // TLDs
            'tld_com' => ['show'=>'0','promo'=>'','title'=>'','sub'=>'','cta_text'=>'','cta_url'=>''],
            'tld_za' => ['show'=>'0','promo'=>'','title'=>'','sub'=>'','cta_text'=>'','cta_url'=>''],
            'tld_ng' => ['show'=>'0','promo'=>'','title'=>'','sub'=>'','cta_text'=>'','cta_url'=>''],
            'tld_us' => ['show'=>'0','promo'=>'','title'=>'','sub'=>'','cta_text'=>'','cta_url'=>''],
            // Hosting
            'general_hosting' => ['show'=>'0','promo'=>'','title'=>'','sub'=>'','cta_text'=>'','cta_url'=>''],
            'cpanel_hosting' => ['show'=>'0','promo'=>'','title'=>'','sub'=>'','cta_text'=>'','cta_url'=>''],
            'cyberpanel_hosting' => ['show'=>'0','promo'=>'','title'=>'','sub'=>'','cta_text'=>'','cta_url'=>''],
            'windows_hosting' => ['show'=>'0','promo'=>'','title'=>'','sub'=>'','cta_text'=>'','cta_url'=>''],
            'reseller_hosting' => ['show'=>'0','promo'=>'','title'=>'','sub'=>'','cta_text'=>'','cta_url'=>''],
            'free_hosting' => ['show'=>'0','promo'=>'','title'=>'','sub'=>'','cta_text'=>'','cta_url'=>''],
            'dedicated_servers' => ['show'=>'0','promo'=>'','title'=>'','sub'=>'','cta_text'=>'','cta_url'=>''],
            // Email, VPS, SSL, services
            'email_hosting' => ['show'=>'0','promo'=>'','title'=>'','sub'=>'','cta_text'=>'','cta_url'=>''],
            'vps_hosting' => ['show'=>'0','promo'=>'','title'=>'','sub'=>'','cta_text'=>'','cta_url'=>''],
            'managed_vps' => ['show'=>'0','promo'=>'','title'=>'','sub'=>'','cta_text'=>'','cta_url'=>''],
            'ssl' => ['show'=>'0','promo'=>'','title'=>'','sub'=>'','cta_text'=>'','cta_url'=>''],
            'ai_builder' => ['show'=>'0','promo'=>'','title'=>'','sub'=>'','cta_text'=>'','cta_url'=>''],
            'online_store' => ['show'=>'0','promo'=>'','title'=>'','sub'=>'','cta_text'=>'','cta_url'=>''],
            'local_seo' => ['show'=>'0','promo'=>'','title'=>'','sub'=>'','cta_text'=>'','cta_url'=>''],
            // WHMCS
            'whmcs_cart' => ['show'=>'0','promo'=>'','title'=>'','sub'=>'','cta_text'=>'','cta_url'=>''],
            'whmcs_store' => ['show'=>'0','promo'=>'','title'=>'','sub'=>'','cta_text'=>'','cta_url'=>''],
            // Blog combo
            'blog_combo' => ['show'=>'0','promo'=>'','title'=>'','sub'=>'','cta_text'=>'','cta_url'=>'']
        ]
    ];
}

function th_bf_group_keys() {
    return array_keys(th_bf_default_config()['groups']);
}

/* ------------------------
   2) ADMIN: menu + settings page (table UI) + sanitization
   ------------------------ */
add_action('admin_menu', function(){
    add_options_page('Black Friday Banner', 'Black Friday Banner', 'manage_options', 'th-bf-banner', 'th_bf_admin_page');
});

add_action('admin_init', function(){
    register_setting('th_bf_group', 'th_bf_opts', ['sanitize_callback' => 'th_bf_sanitize']);

    add_settings_section('th_bf_main', 'Black Friday banner settings', function(){ echo '<p>Configure schedule and per-product banners. Leave promo empty to show "No promo code required". Use the "Show" checkbox to hide the banner for a product.</p>'; }, 'th-bf-banner');

    add_settings_field('th_bf_enable', 'Enable banner', 'th_bf_field_enable', 'th-bf-banner', 'th_bf_main');
    add_settings_field('th_bf_start', 'Start date/time (local)', 'th_bf_field_start', 'th-bf-banner', 'th_bf_main');
    add_settings_field('th_bf_end', 'End date/time (local)', 'th_bf_field_end', 'th-bf-banner', 'th_bf_main');
    add_settings_field('th_bf_default_cta', 'Default CTA URL', 'th_bf_field_default_cta', 'th-bf-banner', 'th_bf_main');
});

// admin fields
function th_bf_field_enable(){ $opts = get_option('th_bf_opts'); $opts = is_array($opts) ? $opts : th_bf_default_config(); $v = isset($opts['enable']) ? $opts['enable'] : '1'; echo '<label><input type="checkbox" name="th_bf_opts[enable]" value="1" '.checked($v,'1',false).' /> Enable Black Friday banner</label>'; }
function th_bf_field_start(){ $opts = get_option('th_bf_opts'); $val = is_array($opts) && isset($opts['start']) ? esc_attr($opts['start']) : (th_bf_default_config()['start']); echo '<input type="datetime-local" name="th_bf_opts[start]" value="'. $val .'" style="width:320px;"> <p class="description">Leave empty to show immediately.</p>'; }
function th_bf_field_end(){ $opts = get_option('th_bf_opts'); $val = is_array($opts) && isset($opts['end']) ? esc_attr($opts['end']) : (th_bf_default_config()['end']); echo '<input type="datetime-local" name="th_bf_opts[end]" value="'. $val .'" style="width:320px;"> <p class="description">Leave empty to never auto-hide.</p>'; }
function th_bf_field_default_cta(){ $opts = get_option('th_bf_opts'); $val = is_array($opts) && isset($opts['default_cta']) ? esc_attr($opts['default_cta']) : th_bf_default_config()['default_cta']; echo '<input type="text" name="th_bf_opts[default_cta]" value="'. $val .'" style="width:360px;"> <p class="description">Default CTA when group CTA is empty (eg: /promos)</p>'; }

// render admin table
function th_bf_admin_page(){
    // load saved or defaults only on first-run
    $saved = get_option('th_bf_opts');
    $initial = [];
    if (!is_array($saved)) {
        // first run: populate with defaults but don't force them later
        $initial = th_bf_default_config();
    } else {
        $initial = $saved;
    }

    $groups = th_bf_default_config()['groups'];
    // merge groups structure if needed but keep saved exact values if present
    $saved_groups = is_array($saved) && isset($saved['groups']) ? $saved['groups'] : [];
    // ensure keys exist in saved_groups for table rendering, but don't overlay values
    foreach ($groups as $k => $v) {
        if (!isset($saved_groups[$k])) {
            $saved_groups[$k] = $v; // initial defaults for the row display only
        } else {
            // keep saved as-is
            $saved_groups[$k] = array_merge($v, $saved_groups[$k]);
        }
    }

    // build form
    ?>
    <div class="wrap">
        <h1>Black Friday Banner</h1>

        <h2 class="nav-tab-wrapper">
            <a href="#" data-tab="settings" class="nav-tab nav-tab-active">Settings</a>
            <a href="#" data-tab="stats" class="nav-tab">Click Stats</a>
        </h2>

        <div id="th-bf-tab-settings" class="th-bf-tab-content">
        <form method="post" action="options.php" id="th-bf-settings-form">
            <?php settings_fields('th_bf_group'); ?>

            <h2>General Settings</h2>
            <table class="form-table" role="presentation">
                <tbody>
                    <tr><th scope="row">Enable Banner</th><td><?php th_bf_field_enable(); ?></td></tr>
                    <tr><th scope="row">Start Date/Time</th><td><?php th_bf_field_start(); ?></td></tr>
                    <tr><th scope="row">End Date/Time</th><td><?php th_bf_field_end(); ?></td></tr>
                    <tr><th scope="row">Default CTA URL</th><td><?php th_bf_field_default_cta(); ?></td></tr>
                </tbody>
            </table>

            <h2>Display Locations</h2>
            <p style="color:#666;">Choose where the banner should appear on your site.</p>
            <table class="form-table" role="presentation">
                <tbody>
                    <?php
                    $show_on = isset($initial['show_on']) ? $initial['show_on'] : th_bf_default_config()['show_on'];
                    ?>
                    <tr>
                        <th scope="row">Show on Homepage</th>
                        <td><label><input type="checkbox" name="th_bf_opts[show_on][homepage]" value="1" <?php checked($show_on['homepage'], '1'); ?>> Enable on homepage</label></td>
                    </tr>
                    <tr>
                        <th scope="row">Show on Archives</th>
                        <td><label><input type="checkbox" name="th_bf_opts[show_on][archives]" value="1" <?php checked($show_on['archives'], '1'); ?>> Enable on category/tag archives</label></td>
                    </tr>
                    <tr>
                        <th scope="row">Show on Blog Page</th>
                        <td><label><input type="checkbox" name="th_bf_opts[show_on][blog_page]" value="1" <?php checked($show_on['blog_page'], '1'); ?>> Enable on main blog page</label></td>
                    </tr>
                    <tr>
                        <th scope="row">Show on Single Posts</th>
                        <td><label><input type="checkbox" name="th_bf_opts[show_on][single_posts]" value="1" <?php checked($show_on['single_posts'], '1'); ?>> Enable on individual blog posts</label></td>
                    </tr>
                    <tr>
                        <th scope="row">Show on Pages</th>
                        <td><label><input type="checkbox" name="th_bf_opts[show_on][pages]" value="1" <?php checked($show_on['pages'], '1'); ?>> Enable on WordPress pages (About, Contact, etc.)</label></td>
                    </tr>
                    <tr>
                        <th scope="row">Show on All Pages</th>
                        <td><label><input type="checkbox" name="th_bf_opts[show_on][all_pages]" value="1" <?php checked($show_on['all_pages'], '1'); ?>> Enable on all pages (overrides above settings)</label></td>
                    </tr>
                    <tr>
                        <th scope="row">Exclude URLs</th>
                        <td>
                            <textarea name="th_bf_opts[exclude_urls]" rows="3" style="width:100%; max-width:500px;" placeholder="/contact&#10;/cart&#10;/checkout"><?php echo esc_textarea(isset($initial['exclude_urls']) ? $initial['exclude_urls'] : ''); ?></textarea>
                            <p class="description">Enter URLs to exclude (one per line). Banner will not show on these pages. Example: /contact or /cart</p>
                        </td>
                    </tr>
                </tbody>
            </table>

            <h2>Per-product settings</h2>
            <p style="color:#666;">Use the Show checkbox to hide the banner on a product. Leave promo empty to allow "No promo code required".</p>

            <div style="margin-bottom:12px;">
                <button type="button" class="button" id="th-bf-enable-all">Enable All</button>
                <button type="button" class="button" id="th-bf-disable-all">Disable All</button>
                <button type="button" class="button button-primary" id="th-bf-add-product">+ Add Custom Product</button>
            </div>

<table class="widefat" style="max-width:100%;" id="th-bf-products-table">
                <thead><tr>
                    <th style="width:40px;">Show</th>
                    <th style="width:150px;">Product key</th>
                    <th>Promo code</th>
                    <th>Title</th>
                    <th>Subtitle</th>
                    <th>CTA text</th>
                    <th>CTA URL</th>
                    <th style="width:50px;">Action</th>
                </tr></thead>
                <tbody id="th-bf-products-tbody">
                <?php
                // Use $saved_groups for initial values so cleared values persist
                foreach ($saved_groups as $key => $row) {
                    $show = isset($row['show']) ? $row['show'] : '0';
                    $promo = isset($row['promo']) ? esc_attr($row['promo']) : '';
                    $title = isset($row['title']) ? esc_attr($row['title']) : '';
                    $sub = isset($row['sub']) ? esc_attr($row['sub']) : '';
                    $cta_text = isset($row['cta_text']) ? esc_attr($row['cta_text']) : '';
                    $cta_url = isset($row['cta_url']) ? esc_attr($row['cta_url']) : '';
                    echo '<tr data-product-type="default">';
                    echo '<td style="text-align:center;"><input type="checkbox" name="th_bf_opts[groups]['.esc_attr($key).'][show]" value="1" class="th-bf-auto-save" '. checked($show,'1',false).'></td>';
                    echo '<td><strong>'.esc_html($key).'</strong></td>';
                    echo '<td><input type="text" name="th_bf_opts[groups]['.esc_attr($key).'][promo]" value="'. $promo .'" placeholder="e.g., BF25SAVE" style="width:120px;" class="th-bf-auto-save"></td>';
                    echo '<td><input type="text" name="th_bf_opts[groups]['.esc_attr($key).'][title]" value="'. $title .'" placeholder="e.g., Black Friday Deal" style="width:220px;" class="th-bf-auto-save"></td>';
                    echo '<td><input type="text" name="th_bf_opts[groups]['.esc_attr($key).'][sub]" value="'. $sub .'" placeholder="e.g., Save big this season" style="width:260px;" class="th-bf-auto-save"></td>';
                    echo '<td><input type="text" name="th_bf_opts[groups]['.esc_attr($key).'][cta_text]" value="'. $cta_text .'" placeholder="e.g., Claim Deal" style="width:120px;" class="th-bf-auto-save"></td>';
                    echo '<td><input type="text" name="th_bf_opts[groups]['.esc_attr($key).'][cta_url]" value="'. $cta_url .'" placeholder="e.g., /deals" style="width:180px;" class="th-bf-auto-save"></td>';
                    echo '<td></td>';
                    echo '</tr>';
                }

                // Render custom products
                $custom_products = isset($initial['custom_products']) ? $initial['custom_products'] : [];
                foreach ($custom_products as $idx => $custom) {
                    $key = isset($custom['key']) ? esc_attr($custom['key']) : '';
                    $show = isset($custom['show']) ? $custom['show'] : '0';
                    $promo = isset($custom['promo']) ? esc_attr($custom['promo']) : '';
                    $title = isset($custom['title']) ? esc_attr($custom['title']) : '';
                    $sub = isset($custom['sub']) ? esc_attr($custom['sub']) : '';
                    $cta_text = isset($custom['cta_text']) ? esc_attr($custom['cta_text']) : '';
                    $cta_url = isset($custom['cta_url']) ? esc_attr($custom['cta_url']) : '';

                    echo '<tr data-product-type="custom">';
                    echo '<td style="text-align:center;"><input type="checkbox" name="th_bf_opts[custom_products]['.$idx.'][show]" value="1" class="th-bf-auto-save" '. checked($show,'1',false).'></td>';
                    echo '<td><input type="text" name="th_bf_opts[custom_products]['.$idx.'][key]" value="'. $key .'" placeholder="e.g., tld_uk" style="width:140px;" class="th-bf-auto-save"></td>';
                    echo '<td><input type="text" name="th_bf_opts[custom_products]['.$idx.'][promo]" value="'. $promo .'" placeholder="e.g., BF25SAVE" style="width:120px;" class="th-bf-auto-save"></td>';
                    echo '<td><input type="text" name="th_bf_opts[custom_products]['.$idx.'][title]" value="'. $title .'" placeholder="e.g., Black Friday Deal" style="width:220px;" class="th-bf-auto-save"></td>';
                    echo '<td><input type="text" name="th_bf_opts[custom_products]['.$idx.'][sub]" value="'. $sub .'" placeholder="e.g., Save big this season" style="width:260px;" class="th-bf-auto-save"></td>';
                    echo '<td><input type="text" name="th_bf_opts[custom_products]['.$idx.'][cta_text]" value="'. $cta_text .'" placeholder="e.g., Claim Deal" style="width:120px;" class="th-bf-auto-save"></td>';
                    echo '<td><input type="text" name="th_bf_opts[custom_products]['.$idx.'][cta_url]" value="'. $cta_url .'" placeholder="e.g., /deals" style="width:180px;" class="th-bf-auto-save"></td>';
                    echo '<td><button type="button" class="button button-small th-bf-delete-product" style="color:#a00;">Delete</button></td>';
                    echo '</tr>';
                }
                ?>
                </tbody>
            </table>

            <?php submit_button('Save banner settings'); ?>
        </form>
        </div>

        <div id="th-bf-tab-stats" class="th-bf-tab-content" style="display:none;">
        <div style="margin-top:20px;">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:15px;">
                <h2 style="margin:0;">Banner Click Analytics</h2>
                <button type="button" class="button button-secondary" id="th-bf-refresh-stats">
                    <span class="dashicons dashicons-update" style="vertical-align:middle;"></span> Refresh
                </button>
                <button type="button" class="button button-secondary" id="th-bf-reset-stats" style="color:#a00;">
                    <span class="dashicons dashicons-trash" style="vertical-align:middle;"></span> Reset All Stats
                </button>
            </div>

            <div id="th-bf-stats-loader" style="text-align:center; padding:40px;">
                <span class="spinner is-active" style="float:none; margin:0;"></span>
                <p>Loading stats...</p>
            </div>

            <div id="th-bf-stats-container" style="display:none;">
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th style="width:30%;">Product</th>
                            <th style="width:15%;">Product Key</th>
                            <th style="width:20%;">Promo Code</th>
                            <th style="width:15%;">Total Clicks</th>
                            <th style="width:20%;">Last Click</th>
                        </tr>
                    </thead>
                    <tbody id="th-bf-stats-body">
                    </tbody>
                </table>

                <div id="th-bf-stats-empty" style="display:none; padding:40px; text-align:center; color:#666;">
                    <span class="dashicons dashicons-chart-line" style="font-size:48px; opacity:0.3;"></span>
                    <p>No click data yet. Clicks will be tracked when users interact with the banner.</p>
                </div>
            </div>
        </div>

        <script>
        (function(){
            var ajaxurl = '<?php echo admin_url('admin-ajax.php'); ?>';

            function loadStats() {
                document.getElementById('th-bf-stats-loader').style.display = 'block';
                document.getElementById('th-bf-stats-container').style.display = 'none';

                fetch(ajaxurl + '?action=th_bf_get_stats', {
                    method: 'POST',
                    credentials: 'same-origin'
                })
                .then(r => r.json())
                .then(function(response){
                    document.getElementById('th-bf-stats-loader').style.display = 'none';
                    document.getElementById('th-bf-stats-container').style.display = 'block';

                    if (response.success && response.data.length > 0) {
                        renderStats(response.data);
                        document.getElementById('th-bf-stats-empty').style.display = 'none';
                        document.querySelector('#th-bf-stats-container table').style.display = 'table';
                    } else {
                        document.getElementById('th-bf-stats-empty').style.display = 'block';
                        document.querySelector('#th-bf-stats-container table').style.display = 'none';
                    }
                })
                .catch(function(err){
                    document.getElementById('th-bf-stats-loader').style.display = 'none';
                    alert('Error loading stats: ' + err.message);
                });
            }

            function renderStats(data) {
                var tbody = document.getElementById('th-bf-stats-body');
                tbody.innerHTML = '';

                data.forEach(function(row){
                    var tr = document.createElement('tr');
                    tr.innerHTML =
                        '<td><strong>' + escapeHtml(row.title || row.key) + '</strong></td>' +
                        '<td><code>' + escapeHtml(row.key) + '</code></td>' +
                        '<td>' + (row.promo ? '<code>' + escapeHtml(row.promo) + '</code>' : '<em>No code</em>') + '</td>' +
                        '<td><strong style="color:#2271b1; font-size:16px;">' + row.clicks + '</strong></td>' +
                        '<td>' + (row.last_click || 'Never') + '</td>';
                    tbody.appendChild(tr);
                });
            }

            function escapeHtml(s) {
                var div = document.createElement('div');
                div.textContent = s;
                return div.innerHTML;
            }

            document.getElementById('th-bf-refresh-stats').addEventListener('click', loadStats);

            document.getElementById('th-bf-reset-stats').addEventListener('click', function(){
                if (!confirm('Are you sure you want to reset all click statistics? This cannot be undone.')) {
                    return;
                }

                fetch(ajaxurl, {
                    method: 'POST',
                    headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                    body: 'action=th_bf_reset_stats',
                    credentials: 'same-origin'
                })
                .then(r => r.json())
                .then(function(response){
                    if (response.success) {
                        alert('Stats reset successfully!');
                        loadStats();
                    }
                });
            });

            // Load stats on page load if stats tab is active
            if (document.getElementById('th-bf-tab-stats').style.display !== 'none') {
                loadStats();
            }
        })();
        </script>
        </div>
        </div>

<script>
        // Tab switching, bulk actions, custom products, and AJAX auto-save
        (function(){
            // Wait for DOM to be ready
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', initAdminScript);
            } else {
                initAdminScript();
            }

            function initAdminScript() {
            var ajaxurl = '<?php echo admin_url('admin-ajax.php'); ?>';
            var tabs = document.querySelectorAll('.nav-tab-wrapper .nav-tab');
            var tabContents = document.querySelectorAll('.th-bf-tab-content');
            var customProductIndex = <?php echo count($custom_products); ?>;
            var saveTimer = null;
            var saveIndicator = null;

            // Create save indicator
            function createSaveIndicator() {
                if (!saveIndicator) {
                    saveIndicator = document.createElement('div');
                    saveIndicator.style.cssText = 'position:fixed; top:32px; right:20px; background:#fff; border:1px solid #ccc; border-radius:4px; padding:8px 12px; box-shadow:0 2px 8px rgba(0,0,0,0.1); z-index:100000; display:none; font-size:13px;';
                    document.body.appendChild(saveIndicator);
                }
                return saveIndicator;
            }

            function showSaveStatus(message, type) {
                var indicator = createSaveIndicator();
                indicator.textContent = message;
                indicator.style.borderColor = type === 'success' ? '#46b450' : (type === 'error' ? '#dc3232' : '#ccc');
                indicator.style.color = type === 'success' ? '#46b450' : (type === 'error' ? '#dc3232' : '#666');
                indicator.style.display = 'block';

                setTimeout(function(){
                    indicator.style.display = 'none';
                }, 2000);
            }

            // AJAX auto-save
            function autoSave() {
                if (saveTimer) {
                    clearTimeout(saveTimer);
                }

                saveTimer = setTimeout(function(){
                    var form = document.getElementById('th-bf-settings-form');
                    var formData = new FormData(form);
                    formData.append('action', 'th_bf_save_settings');

                    showSaveStatus('Saving...', 'pending');

                    fetch(ajaxurl, {
                        method: 'POST',
                        body: formData,
                        credentials: 'same-origin'
                    })
                    .then(r => r.json())
                    .then(function(response){
                        if (response.success) {
                            showSaveStatus('Settings saved', 'success');
                        } else {
                            showSaveStatus('Save failed', 'error');
                        }
                    })
                    .catch(function(){
                        showSaveStatus('Save error', 'error');
                    });
                }, 800); // Debounce 800ms
            }

            // Tab switching
            tabs.forEach(function(tab){
                tab.addEventListener('click', function(e){
                    e.preventDefault();
                    var targetTab = this.getAttribute('data-tab');

                    // Update active tab
                    tabs.forEach(function(t){ t.classList.remove('nav-tab-active'); });
                    this.classList.add('nav-tab-active');

                    // Show/hide content
                    tabContents.forEach(function(content){
                        content.style.display = 'none';
                    });

                    var targetContent = document.getElementById('th-bf-tab-' + targetTab);
                    if (targetContent) {
                        targetContent.style.display = 'block';

                        // Load stats if switching to stats tab
                        if (targetTab === 'stats') {
                            var loadStatsBtn = document.getElementById('th-bf-refresh-stats');
                            if (loadStatsBtn) {
                                loadStatsBtn.click();
                            }
                        }
                    }
                });
            });

            // Bulk actions
            var enableAllBtn = document.getElementById('th-bf-enable-all');
            var disableAllBtn = document.getElementById('th-bf-disable-all');
            var addProductBtn = document.getElementById('th-bf-add-product');

            if (enableAllBtn) {
                enableAllBtn.addEventListener('click', function(){
                    var checkboxes = document.querySelectorAll('input[type="checkbox"][name^="th_bf_opts[groups]"][name$="[show]"], input[type="checkbox"][name^="th_bf_opts[custom_products]"][name$="[show]"]');
                    checkboxes.forEach(function(cb){ cb.checked = true; });
                    autoSave();
                });
            }

            if (disableAllBtn) {
                disableAllBtn.addEventListener('click', function(){
                    var checkboxes = document.querySelectorAll('input[type="checkbox"][name^="th_bf_opts[groups]"][name$="[show]"], input[type="checkbox"][name^="th_bf_opts[custom_products]"][name$="[show]"]');
                    checkboxes.forEach(function(cb){ cb.checked = false; });
                    autoSave();
                });
            }

            // Add custom product
            if (addProductBtn) {
                addProductBtn.addEventListener('click', function(){
                var tbody = document.getElementById('th-bf-products-tbody');
                var tr = document.createElement('tr');
                tr.setAttribute('data-product-type', 'custom');

                var idx = customProductIndex++;

                tr.innerHTML =
                    '<td style="text-align:center;"><input type="checkbox" name="th_bf_opts[custom_products][' + idx + '][show]" value="1" class="th-bf-auto-save"></td>' +
                    '<td><input type="text" name="th_bf_opts[custom_products][' + idx + '][key]" value="" placeholder="e.g., tld_uk" style="width:140px;" class="th-bf-auto-save"></td>' +
                    '<td><input type="text" name="th_bf_opts[custom_products][' + idx + '][promo]" value="" placeholder="e.g., BF25SAVE" style="width:120px;" class="th-bf-auto-save"></td>' +
                    '<td><input type="text" name="th_bf_opts[custom_products][' + idx + '][title]" value="" placeholder="e.g., Black Friday Deal" style="width:220px;" class="th-bf-auto-save"></td>' +
                    '<td><input type="text" name="th_bf_opts[custom_products][' + idx + '][sub]" value="" placeholder="e.g., Save big this season" style="width:260px;" class="th-bf-auto-save"></td>' +
                    '<td><input type="text" name="th_bf_opts[custom_products][' + idx + '][cta_text]" value="" placeholder="e.g., Claim Deal" style="width:120px;" class="th-bf-auto-save"></td>' +
                    '<td><input type="text" name="th_bf_opts[custom_products][' + idx + '][cta_url]" value="" placeholder="e.g., /deals" style="width:180px;" class="th-bf-auto-save"></td>' +
                    '<td><button type="button" class="button button-small th-bf-delete-product" style="color:#a00;">Delete</button></td>';

                tbody.appendChild(tr);

                // Attach auto-save listeners to new inputs
                tr.querySelectorAll('.th-bf-auto-save').forEach(function(input){
                    input.addEventListener('input', autoSave);
                    input.addEventListener('change', autoSave);
                });

                // Attach delete handler
                tr.querySelector('.th-bf-delete-product').addEventListener('click', function(){
                    if (confirm('Delete this custom product?')) {
                        tr.remove();
                        autoSave();
                    }
                });
                });
            }

            // Delete custom product (for existing rows)
            document.addEventListener('click', function(e){
                if (e.target.classList.contains('th-bf-delete-product')) {
                    if (confirm('Delete this custom product?')) {
                        e.target.closest('tr').remove();
                        autoSave();
                    }
                }
            });

            // Auto-save on input change
            document.querySelectorAll('.th-bf-auto-save').forEach(function(input){
                input.addEventListener('input', autoSave);
                input.addEventListener('change', autoSave);
            });

            // Also auto-save display location checkboxes and exclude URLs
            document.querySelectorAll('input[name^="th_bf_opts[show_on]"], textarea[name="th_bf_opts[exclude_urls]"]').forEach(function(input){
                input.addEventListener('input', autoSave);
                input.addEventListener('change', autoSave);
            });
            } // end initAdminScript
        })();
        </script>
    <?php
}

// sanitize callback - respects cleared values (no forced defaults)
function th_bf_sanitize($input) {
    $defaults = th_bf_default_config();
    $out = [];

    $out['enable'] = isset($input['enable']) && $input['enable'] == '1' ? '1' : '0';
    $out['start'] = isset($input['start']) ? sanitize_text_field($input['start']) : '';
    $out['end'] = isset($input['end']) ? sanitize_text_field($input['end']) : '';
    $out['default_cta'] = isset($input['default_cta']) ? esc_url_raw($input['default_cta']) : $defaults['default_cta'];

    // Sanitize display location settings
    $out['show_on'] = [
        'homepage' => isset($input['show_on']['homepage']) && $input['show_on']['homepage'] == '1' ? '1' : '0',
        'archives' => isset($input['show_on']['archives']) && $input['show_on']['archives'] == '1' ? '1' : '0',
        'blog_page' => isset($input['show_on']['blog_page']) && $input['show_on']['blog_page'] == '1' ? '1' : '0',
        'single_posts' => isset($input['show_on']['single_posts']) && $input['show_on']['single_posts'] == '1' ? '1' : '0',
        'pages' => isset($input['show_on']['pages']) && $input['show_on']['pages'] == '1' ? '1' : '0',
        'all_pages' => isset($input['show_on']['all_pages']) && $input['show_on']['all_pages'] == '1' ? '1' : '0'
    ];

    // Sanitize exclude URLs
    $out['exclude_urls'] = isset($input['exclude_urls']) ? sanitize_textarea_field($input['exclude_urls']) : '';

    // Sanitize default groups
    $out['groups'] = [];
    $keys = array_keys($defaults['groups']);
    foreach ($keys as $k) {
        $in = isset($input['groups'][$k]) ? $input['groups'][$k] : [];
        $out['groups'][$k] = [
            'show' => isset($in['show']) && $in['show'] == '1' ? '1' : '0',
            'promo' => isset($in['promo']) ? sanitize_text_field($in['promo']) : '',
            'title' => isset($in['title']) ? sanitize_text_field($in['title']) : '',
            'sub' => isset($in['sub']) ? sanitize_text_field($in['sub']) : '',
            'cta_text' => isset($in['cta_text']) ? sanitize_text_field($in['cta_text']) : '',
            'cta_url' => isset($in['cta_url']) ? esc_url_raw($in['cta_url']) : ''
        ];
    }

    // Sanitize custom products
    $out['custom_products'] = [];
    if (isset($input['custom_products']) && is_array($input['custom_products'])) {
        foreach ($input['custom_products'] as $custom) {
            // Skip empty product keys
            if (empty($custom['key'])) {
                continue;
            }

            $out['custom_products'][] = [
                'key' => sanitize_text_field($custom['key']),
                'show' => isset($custom['show']) && $custom['show'] == '1' ? '1' : '0',
                'promo' => isset($custom['promo']) ? sanitize_text_field($custom['promo']) : '',
                'title' => isset($custom['title']) ? sanitize_text_field($custom['title']) : '',
                'sub' => isset($custom['sub']) ? sanitize_text_field($custom['sub']) : '',
                'cta_text' => isset($custom['cta_text']) ? sanitize_text_field($custom['cta_text']) : '',
                'cta_url' => isset($custom['cta_url']) ? esc_url_raw($custom['cta_url']) : ''
            ];
        }
    }

    return $out;
}

/* ------------------------
   AJAX: Click tracking
   ------------------------ */

// Record click
add_action('wp_ajax_th_bf_track_click', 'th_bf_track_click');
add_action('wp_ajax_nopriv_th_bf_track_click', 'th_bf_track_click');

function th_bf_track_click() {
    $group_key = isset($_POST['group_key']) ? sanitize_text_field($_POST['group_key']) : '';
    $promo = isset($_POST['promo']) ? sanitize_text_field($_POST['promo']) : '';

    if (empty($group_key)) {
        wp_send_json_error('Invalid group key');
        return;
    }

    // Get current stats
    $stats = get_option('th_bf_click_stats', []);

    if (!isset($stats[$group_key])) {
        $stats[$group_key] = [
            'clicks' => 0,
            'promo' => $promo,
            'last_click' => ''
        ];
    }

    $stats[$group_key]['clicks']++;
    $stats[$group_key]['promo'] = $promo;
    $stats[$group_key]['last_click'] = current_time('mysql');

    update_option('th_bf_click_stats', $stats);

    wp_send_json_success(['clicks' => $stats[$group_key]['clicks']]);
}

// Get stats
add_action('wp_ajax_th_bf_get_stats', 'th_bf_get_stats');

function th_bf_get_stats() {
    $stats = get_option('th_bf_click_stats', []);
    $opts = get_option('th_bf_opts', []);

    $result = [];
    foreach ($stats as $key => $data) {
        $group = isset($opts['groups'][$key]) ? $opts['groups'][$key] : [];
        $result[] = [
            'key' => $key,
            'title' => isset($group['title']) ? $group['title'] : $key,
            'promo' => $data['promo'],
            'clicks' => $data['clicks'],
            'last_click' => $data['last_click']
        ];
    }

    // Sort by clicks descending
    usort($result, function($a, $b) {
        return $b['clicks'] - $a['clicks'];
    });

    wp_send_json_success($result);
}

// Reset stats
add_action('wp_ajax_th_bf_reset_stats', 'th_bf_reset_stats');

function th_bf_reset_stats() {
    delete_option('th_bf_click_stats');
    wp_send_json_success('Stats reset');
}

// AJAX save settings
add_action('wp_ajax_th_bf_save_settings', 'th_bf_save_settings');

function th_bf_save_settings() {
    // Check nonce for security
    if (!isset($_POST['_wpnonce']) || !wp_verify_nonce($_POST['_wpnonce'], 'th_bf_group-options')) {
        wp_send_json_error('Invalid nonce');
        return;
    }

    // Check user permissions
    if (!current_user_can('manage_options')) {
        wp_send_json_error('Permission denied');
        return;
    }

    // Sanitize and save
    $input = isset($_POST['th_bf_opts']) ? $_POST['th_bf_opts'] : [];
    $sanitized = th_bf_sanitize($input);

    update_option('th_bf_opts', $sanitized);

    wp_send_json_success('Settings saved');
}

/* ------------------------
   3) FRONTEND: output template (client-side insertion) - footer hook
   ------------------------ */
add_action('wp_footer', function(){
    // ensure we DO NOT run server-side on WHMCS /cloud/ requests; we will still render on WP pages that link to WHMCS
    $uri = $_SERVER['REQUEST_URI'] ?? '';
    if (strpos($uri, '/cloud/') === 0 || strpos($uri, '/cloud/') !== false) {
        return;
    }

    $opts = get_option('th_bf_opts');
    $defaults = th_bf_default_config();

    // Apply initial default only when no saved option exists (first run). If saved exists, use saved exactly.
    if (!is_array($opts)) {
        $opts = $defaults;
    }

    if (!isset($opts['enable']) || $opts['enable'] !== '1') return;

    // Check display location settings
    $show_on = isset($opts['show_on']) ? $opts['show_on'] : $defaults['show_on'];
    $exclude_urls = isset($opts['exclude_urls']) ? $opts['exclude_urls'] : '';

    // Parse excluded URLs
    $excluded_paths = array_filter(array_map('trim', explode("\n", $exclude_urls)));
    foreach ($excluded_paths as $excluded_path) {
        if (!empty($excluded_path) && strpos($uri, $excluded_path) !== false) {
            return; // Don't show banner on excluded URLs
        }
    }

    // Check if banner should be shown based on page type
    $should_show = false;

    if ($show_on['all_pages'] === '1') {
        $should_show = true;
    } else {
        if (is_front_page() && $show_on['homepage'] === '1') {
            $should_show = true;
        } elseif (is_archive() && $show_on['archives'] === '1') {
            $should_show = true;
        } elseif (is_home() && $show_on['blog_page'] === '1') {
            $should_show = true;
        } elseif (is_singular('post') && $show_on['single_posts'] === '1') {
            $should_show = true;
        } elseif (is_page() && $show_on['pages'] === '1') {
            $should_show = true;
        }
    }

    // If location check fails, still proceed with URL-based matching for backward compatibility
    if (!$should_show && !is_singular('post')) {
        // Will check URL patterns below
    }

    // Build mapping patterns -> group key
    $map = [
        'domain_register' => ['/cloud/cart.php?a=add&domain=register'],
        'domain_transfer' => ['/cloud/cart.php?a=add&domain=transfer'],
        'ke_domains' => ['/ke-domain'],
        'free_domains' => ['/domains/free'],
        'whois' => ['/domains/whois'],
        'tld_com' => ['/domains/tlds/com','/domains/tlds/com/'],
        'tld_za' => ['/domains/tlds/za','/domains/tlds/za/'],
        'tld_ng' => ['/domains/tlds/ng','/domains/tlds/ng/'],
        'tld_us' => ['/domains/tlds/us','/domains/tlds/us/'],
        'general_domains' => ['/domains','/domain','/domains/tlds','/domains/'],
        'general_hosting' => ['/hosting','/hosting/'],
        'cpanel_hosting' => ['/hosting/cpanel'],
        'cyberpanel_hosting' => ['/hosting/cyberpanel'],
        'windows_hosting' => ['/hosting/windows'],
        'reseller_hosting' => ['/hosting/reseller'],
        'free_hosting' => ['/hosting/free'],
        'dedicated_servers' => ['/hosting/dedicated-servers'],
        'email_hosting' => ['/hosting/email','/email-hosting'],
        'vps_hosting' => ['/vps-hosting','/vps'],
        'managed_vps' => ['/vps-hosting/managed'],
        'ssl' => ['/ssl'],
        'ai_builder' => ['/ai-website-builder','/website-builder'],
        'online_store' => ['/online-store'],
        'local_seo' => ['/local-seo'],
        'whmcs_cart' => ['/cloud/cart.php','/cloud/cart.php?a=view'],
        'whmcs_store' => ['/cloud/store','/cloud/index.php?rp=/store']
    ];

    $uri = $_SERVER['REQUEST_URI'];

    // If single blog post -> use blog_combo group if enabled
    if (is_singular('post')) {
        $group_key = 'blog_combo';
        $group = isset($opts['groups'][$group_key]) ? $opts['groups'][$group_key] : $defaults['groups'][$group_key];
        // Only render if show enabled
        if (isset($group['show']) && $group['show'] === '1') {
            // output template for blog
            ?>
            <div id="th-bf-template" style="display:none"
                 data-group_key="<?php echo esc_attr($group_key); ?>"
                 data-title="<?php echo esc_attr($group['title']); ?>"
                 data-sub="<?php echo esc_attr($group['sub']); ?>"
                 data-promo="<?php echo esc_attr($group['promo']); ?>"
                 data-cta_text="<?php echo esc_attr($group['cta_text']); ?>"
                 data-cta_url="<?php echo esc_attr($group['cta_url'] ? $group['cta_url'] : $opts['default_cta']); ?>"
                 data-start="<?php echo esc_attr($opts['start']); ?>"
                 data-end="<?php echo esc_attr($opts['end']); ?>">
            </div>
            <?php
            return;
        } else {
            return;
        }
    }

    // find matching group based on URI patterns
    $found = false;
    $is_custom = false;

    foreach ($map as $key => $patterns) {
        foreach ($patterns as $p) {
            if (strpos($uri, $p) !== false) {
                $found = $key;
                break 2;
            }
        }
    }

    // Check custom products if no default match found
    if (!$found) {
        $custom_products = isset($opts['custom_products']) ? $opts['custom_products'] : [];
        foreach ($custom_products as $custom) {
            $custom_key = isset($custom['key']) ? $custom['key'] : '';
            if (!empty($custom_key) && strpos($uri, '/' . $custom_key) !== false) {
                $found = $custom_key;
                $is_custom = true;
                $group = $custom;
                break;
            }
        }
    }

    if (!$found && !$should_show) return;

    if (!$is_custom && $found) {
        $group_key = $found;
        // use exact saved group if exists; otherwise fallback to default row (but don't write it)
        $group = isset($opts['groups'][$group_key]) ? $opts['groups'][$group_key] : (isset($defaults['groups'][$group_key]) ? $defaults['groups'][$group_key] : null);
        if (!$group) return;
        // Only render if show checkbox is enabled for this group
        if (!isset($group['show']) || $group['show'] !== '1') return;
    } elseif ($is_custom) {
        $group_key = $found;
        // Only render if show checkbox is enabled for custom product
        if (!isset($group['show']) || $group['show'] !== '1') return;
    } elseif ($should_show && !$found) {
        // If should_show is true but no specific product matched, use first enabled product as fallback
        $group_key = null;
        $group = null;

        // Find first enabled product from groups
        foreach ($opts['groups'] as $key => $prod) {
            if (isset($prod['show']) && $prod['show'] === '1' && !empty($prod['title'])) {
                $group_key = $key;
                $group = $prod;
                break;
            }
        }

        // If no enabled group found, try custom products
        if (!$group) {
            $custom_products = isset($opts['custom_products']) ? $opts['custom_products'] : [];
            foreach ($custom_products as $custom) {
                if (isset($custom['show']) && $custom['show'] === '1' && !empty($custom['title'])) {
                    $group_key = isset($custom['key']) ? $custom['key'] : 'custom_banner';
                    $group = $custom;
                    break;
                }
            }
        }

        // If still no group found, don't render
        if (!$group || !$group_key) return;
    } else {
        return;
    }

    $title = isset($group['title']) ? $group['title'] : '';
    $subtitle = isset($group['sub']) ? $group['sub'] : '';
    $promo = isset($group['promo']) ? $group['promo'] : '';
    $cta_text = isset($group['cta_text']) && $group['cta_text'] !== '' ? $group['cta_text'] : 'Claim Deal';
    $cta_url = isset($group['cta_url']) && $group['cta_url'] !== '' ? $group['cta_url'] : $opts['default_cta'];

    // emit hidden template for JS to build UI
    ?>
    <div id="th-bf-template" style="display:none"
         data-group_key="<?php echo esc_attr($group_key); ?>"
         data-title="<?php echo esc_attr($title); ?>"
         data-sub="<?php echo esc_attr($subtitle); ?>"
         data-promo="<?php echo esc_attr($promo); ?>"
         data-cta_text="<?php echo esc_attr($cta_text); ?>"
         data-cta_url="<?php echo esc_attr($cta_url); ?>"
         data-start="<?php echo esc_attr($opts['start']); ?>"
         data-end="<?php echo esc_attr($opts['end']); ?>">
    </div>
    <?php
});

/* ------------------------
   4) FRONTEND: CSS + JS to build banner client-side, countdown, copy, and WHMCS rewrites
   ------------------------ */
add_action('wp_footer', function(){
    ?>
<style>
.th-bf-wrap {
    width:100%;
    box-sizing:border-box;
    display:flex;
    justify-content:center;
    position:relative;
    z-index:999;
    margin:0;
    padding:0;
}

.th-bf {
    width:100%;
    padding:24px 20px;
    box-sizing:border-box;
    margin-top:0;
}

/* Strip */
.th-bf__strip {
    width:70%;
    margin: 0 auto;
    background:#ffffff;
    border-radius:18px;
    padding:24px;
    display:flex;
    align-items:center;
    justify-content:space-between;
    gap:22px;
    box-shadow:0 20px 48px rgba(12,20,40,0.08);
    border:1px solid rgba(0,0,0,0.04);
    box-sizing:border-box;
    cursor:pointer;
    transition:transform 0.2s ease, box-shadow 0.2s ease;
}

.th-bf__strip:hover {
    transform:translateY(-2px);
    box-shadow:0 24px 56px rgba(12,20,40,0.12);
}

/* LEFT CARD */
.th-bf__left {
    background:linear-gradient(135deg, #1b1b1b, #2e2e2e);
    padding:24px 28px;
    border-radius:16px;
    min-width:300px;
    flex:1 1 auto;
    box-shadow:0 10px 25px rgba(0,0,0,0.16);
    color:#fff;
    max-width:35%;
}

.th-bf__title {
    font-weight:800;
    font-size:29px;
    margin:0;
    color:#ffffff;
}

.th-bf__sub {
    font-size:18px;
    margin-top:6px;
    opacity:0.9;
    color:#f2f2f2;
}

.th-bf__count {
    font-size:17px;
    margin-top:10px;
    color:#ffcf4d;
    font-weight:700;
}

/* RIGHT side (CTA + promo) */
.th-bf__actions {
    display:flex;
    flex-direction:column;
    justify-content:center;
    align-items:flex-end;
    gap:12px;
    flex-shrink:0;
}

/* CTA button */
.th-bf__cta {
    background:linear-gradient(90deg,#ff7a00,#ff0a78);
    color:#fff;
    padding:14px 26px;
    border-radius:10px;
    font-weight:800;
    text-decoration:none;
    font-size:15px;
    box-shadow:0 10px 30px rgba(255,10,120,0.18);
    display:inline-block;
    cursor:pointer;
    user-select:none;
}

/* Promo box */
.th-bf__promo {
    background:#fff;
    border-radius:10px;
    padding:10px 14px;
    display:flex;
    align-items:center;
    gap:10px;
    font-weight:700;
    color:#111;
    box-shadow:0 8px 20px rgba(0,0,0,0.06);
    border:1px solid rgba(0,0,0,0.06);
    white-space:nowrap;
}

.th-bf__promo-text {
    font-weight:700;
    color:#111;
}

/* Copy icon */
.th-bf__copy {
    cursor:pointer;
    display:inline-flex;
    align-items:center;
    justify-content:center;
    width:34px;
    height:34px;
    border-radius:8px;
    background:#f7f7f7;
    border:1px solid rgba(0,0,0,0.08);
}

.th-bf__copy svg {
    width:16px;
    height:16px;
    fill:#111;
}

/* Tooltip */
.th-bf-tooltip {
    position:fixed;
    background:#111;
    color:#fff;
    padding:8px 10px;
    font-size:13px;
    border-radius:6px;
    opacity:0;
    pointer-events:none;
    transition:opacity .16s;
    z-index:99999;
}

/* Responsive */
@media(max-width:920px){
    .th-bf__strip {
        flex-direction:column;
        padding:18px;
    }
    .th-bf__left {
        max-width:100%;
        width:100%;
    }
    .th-bf__actions {
        flex-direction:column;
        align-items:flex-start;
        width:100%;
    }
    .th-bf__cta {
        width:100%;
        text-align:center;
    }
}

@media(max-width:420px){
    .th-bf {
        padding:14px;
    }
    .th-bf__left {
        padding:18px 20px;
    }
    .th-bf__title {
        font-size:18px;
    }
    .th-bf__cta {
        padding:12px 16px;
        font-size:14px;
    }
}
</style>

<script>
(function(){
    function ready(fn){ if (document.readyState !== 'loading') fn(); else document.addEventListener('DOMContentLoaded', fn); }

    // Define utility functions FIRST before they're used
    function escapeHtml(s){
        return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
    }

    function escapeAttr(s){
        return String(s).replace(/"/g,'&quot;').replace(/'/g,"&#39;");
    }

    function copyIconSVG(){
        return '<svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" aria-hidden="true"><path d="M16 1H8a2 2 0 0 0-2 2v2H6a2 2 0 0 0-2 2v11a2 2 0 0 0 2 2h8.5a2 2 0 0 0 2-2V5a2 2 0 0 0-2-2h-0.5V3a2 2 0 0 0-2-2zM8 3h8v2H8V3z" /></svg>';
    }

    ready(function(){
        var tpl = document.getElementById('th-bf-template');
        if (!tpl) return;

        var groupKey = tpl.dataset.group_key || '';
        var title = tpl.dataset.title || '';
        var sub = tpl.dataset.sub || '';
        var promo = tpl.dataset.promo || '';
        var ctaText = tpl.dataset.cta_text || 'Claim Deal';
        var ctaUrl = tpl.dataset.cta_url || '/promos';
        var start = tpl.dataset.start || '';
        var end = tpl.dataset.end || '';

        var startTs = start ? (new Date(start)).getTime() : null;
        var endTs = end ? (new Date(end)).getTime() : null;
        if (start && isNaN(startTs)) startTs = null;
        if (end && isNaN(endTs)) endTs = null;

        // Build DOM using proper concatenation
        var wrap = document.createElement('div');
        wrap.className = 'th-bf-wrap';

        var inner = document.createElement('div');
        inner.className = 'th-bf';

        var stripHTML = '<div class="th-bf__strip" role="region" aria-label="Black Friday banner" style="cursor:pointer;">' +
            '<div class="th-bf__left">' +
                '<div class="th-bf__title">' + escapeHtml(title) + '</div>' +
                '<div class="th-bf__sub">' + escapeHtml(sub) + '</div>' +
                '<div class="th-bf__count" aria-live="polite" id="th-bf-count"></div>' +
            '</div>' +
            '<div class="th-bf__actions">' +
                '<div class="th-bf__cta" id="th-bf-cta">' +
                    escapeHtml(ctaText) +
                '</div>' +
                '<div class="th-bf__promo" id="th-bf-promo">' +
                    '<span class="th-bf__promo-text" id="th-bf-promo-text"></span>' +
                    '<span class="th-bf__copy" id="th-bf-copy" role="button" aria-label="Copy promo code" title="Copy promo code" style="display:none;">' +
                        copyIconSVG() +
                    '</span>' +
                '</div>' +
            '</div>' +
        '</div>';

        inner.innerHTML = stripHTML;
        wrap.appendChild(inner);

        // insert after #navigation
        var nav = document.querySelector('#navigation');
        if (nav && nav.parentNode) {
            nav.parentNode.insertBefore(wrap, nav.nextSibling);

            setTimeout(function(){
                var navStyle = getComputedStyle(nav);
                var navHt = nav.getBoundingClientRect().height || nav.offsetHeight || 0;

                wrap.style.marginTop = '0';
				wrap.style.paddingTop = '0';


                // Ensure next element has reasonable margin
                var next = wrap.nextElementSibling;
                if (next) {
                    var mt = parseFloat(getComputedStyle(next).marginTop) || 0;
                    if (mt > 24) next.style.marginTop = '24px';
                }
            }, 70);
        } else {
            document.body.insertBefore(wrap, document.body.firstChild);
        }

        // promo UI: show promo or "No promo code required"
        var promoTextEl = document.getElementById('th-bf-promo-text');
        var copyBtn = document.getElementById('th-bf-copy');
        if (promo && promo.trim() !== '') {
            promoTextEl.textContent = 'CODE: ' + promo;
            copyBtn.style.display = 'inline-flex';
        } else {
            promoTextEl.textContent = 'No promo code required';
            copyBtn.style.display = 'none';
        }

        // countdown and visibility rules
        var countEl = document.getElementById('th-bf-count');
        var stripEl = wrap.querySelector('.th-bf__strip');

        function update() {
            var now = Date.now();
            if (startTs && now < startTs) {
                // hide before start
                stripEl.style.display = 'none';
                return;
            }
            if (endTs && now > endTs) {
                stripEl.style.display = 'none';
                return;
            }
            // show
            stripEl.style.display = 'flex';
            if (endTs) {
                var diff = endTs - now;
                if (diff < 0) diff = 0;
                var days = Math.floor(diff / (1000*60*60*24));
                var hours = Math.floor((diff % (1000*60*60*24)) / (1000*60*60));
                var mins = Math.floor((diff % (1000*60*60)) / (1000*60));
                var secs = Math.floor((diff % (1000*60)) / 1000);
                countEl.textContent = 'Ends in ' + pad(days) + 'd : ' + pad(hours) + 'h : ' + pad(mins) + 'm : ' + pad(secs) + 's';
            } else {
                countEl.textContent = '';
            }
        }

        function pad(n){ return (n<10 ? '0'+n : ''+n); }

        update();
        var iv = endTs ? setInterval(update, 1000) : setInterval(update, 10000);

        // Make entire banner clickable
        stripEl.addEventListener('click', function(e){
            // Don't navigate if clicking copy button
            if (e.target.closest('#th-bf-copy')) {
                return;
            }

            // Track click
            if (groupKey) {
                trackClick(groupKey, promo);
            }

            if (ctaUrl && ctaUrl.trim() !== '') {
                var targetUrl = ctaUrl;
                // WHMCS CTA rewrite: always override promocode if CTA targets /cloud/
                if (targetUrl.indexOf('/cloud/') !== -1) {
                    targetUrl = rewritePromocode(targetUrl, promo);
                }
                window.location.href = targetUrl;
            }
        });

        // Track click function
        function trackClick(key, promoCode) {
            var ajaxUrl = '<?php echo admin_url('admin-ajax.php'); ?>';
            var formData = new FormData();
            formData.append('action', 'th_bf_track_click');
            formData.append('group_key', key);
            formData.append('promo', promoCode || '');

            // Use sendBeacon for reliable tracking even if user navigates away
            if (navigator.sendBeacon) {
                navigator.sendBeacon(ajaxUrl, formData);
            } else {
                // Fallback to fetch
                fetch(ajaxUrl, {
                    method: 'POST',
                    body: formData,
                    keepalive: true
                }).catch(function(){});
            }
        }

        // copy handler
        if (copyBtn) {
            copyBtn.addEventListener('click', function(e){
                e.preventDefault();
                e.stopPropagation(); // Prevent banner click
                if (!promo || promo.trim() === '') return;
                copyToClipboard(promo, function(ok){
                    showTooltip(copyBtn, ok ? 'Copied!' : 'Copy failed');
                });
            });
        }

        function copyToClipboard(text, cb){
            if (!navigator.clipboard) {
                var ta = document.createElement('textarea');
                ta.value = text;
                document.body.appendChild(ta);
                ta.select();
                try {
                    var ok = document.execCommand('copy');
                    ta.remove();
                    cb(!!ok);
                } catch(e){
                    ta.remove();
                    cb(false);
                }
                return;
            }
            navigator.clipboard.writeText(text).then(function(){ cb(true); }, function(){ cb(false); });
        }

        function showTooltip(anchor, text){
            var tip = document.createElement('div');
            tip.className = 'th-bf-tooltip';
            tip.innerText = text;
            document.body.appendChild(tip);
            var rect = anchor.getBoundingClientRect();
            tip.style.left = (Math.max(8, rect.left + (rect.width/2) - (tip.offsetWidth/2))) + 'px';
            tip.style.top = (Math.max(8, rect.top - tip.offsetHeight - 8)) + 'px';
            setTimeout(function(){ tip.style.opacity = '1'; }, 10);
            setTimeout(function(){
                tip.style.opacity = '0';
                setTimeout(function(){ tip.remove(); }, 200);
            }, 1200);
        }

        function rewritePromocode(url, promoCode) {
            try {
                var a = document.createElement('a');
                a.href = url;
                var params = new URLSearchParams(a.search.replace(/^\?/,''));
                // always override (if empty, remove param)
                if (promoCode && promoCode.trim() !== '') {
                    params.set('promocode', promoCode);
                } else {
                    params.delete('promocode');
                }
                var base = a.protocol + '//' + a.host + a.pathname;
                var newSearch = params.toString() ? '?' + params.toString() : '';
                return base + newSearch + (a.hash || '');
            } catch (err) {
                // fallback
                if (url.indexOf('?') === -1) {
                    return url + (promoCode ? '?promocode=' + encodeURIComponent(promoCode) : '');
                }
                // remove existing promocode param
                var cleaned = url.replace(/([?&])promocode=[^&]*(&?)/, function(m,p1,p2){
                    return p2 ? p1 : '';
                });
                return cleaned + (cleaned.indexOf('?') === -1 ?
                    (promoCode ? '?promocode=' + encodeURIComponent(promoCode) : '') :
                    (promoCode ? '&promocode=' + encodeURIComponent(promoCode) : ''));
            }
        }

        // finished
        tpl.remove();
    });
})();
</script>
    <?php
});
