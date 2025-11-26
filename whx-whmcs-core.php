<?php
/**
 * WHX – WHMCS Core (v2.4 - Security Hardened)
 * - Settings page with badges
 * - Buttons at the TOP (Test + Fetch Currencies + Clear Cache)
 * - Auto-fetch currencies after a successful save
 * - Cached helpers for other snippets
 * - Settings submenu always first + highlighted
 * - Cache clearing for plugins, Cloudflare, Bunny CDN
 * - Encrypted credential storage
 * - Connection validation for all services
 * - Audit logging and rate limiting
 */

if (!defined('ABSPATH')) exit;

/* ---------------- Security Functions ---------------- */

/**
 * Encrypt sensitive data using WordPress salts
 */
function whx_encrypt($data) {
  if (empty($data)) return '';
  $key = wp_salt('auth') . wp_salt('secure_auth');
  $iv_length = openssl_cipher_iv_length('aes-256-cbc');
  $iv = openssl_random_pseudo_bytes($iv_length);
  $encrypted = openssl_encrypt($data, 'aes-256-cbc', $key, 0, $iv);
  return base64_encode($iv . $encrypted);
}

/**
 * Decrypt sensitive data
 */
function whx_decrypt($data) {
  if (empty($data)) return '';
  $key = wp_salt('auth') . wp_salt('secure_auth');
  $data = base64_decode($data);
  $iv_length = openssl_cipher_iv_length('aes-256-cbc');
  $iv = substr($data, 0, $iv_length);
  $encrypted = substr($data, $iv_length);
  return openssl_decrypt($encrypted, 'aes-256-cbc', $key, 0, $iv);
}

/**
 * Sanitize error messages to remove sensitive data
 */
function whx_sanitize_error($message) {
  // Remove potential API keys, tokens, emails
  $message = preg_replace('/[a-f0-9]{32,}/i', '[REDACTED]', $message);
  $message = preg_replace('/Bearer\s+[^\s]+/i', 'Bearer [REDACTED]', $message);
  $message = preg_replace('/[\w\-\.]+@[\w\-\.]+/i', '[EMAIL_REDACTED]', $message);
  return $message;
}

/**
 * Audit log for credential changes
 */
function whx_audit_log($action, $details = '') {
  $user = wp_get_current_user();
  $log_entry = sprintf(
    '[%s] User: %s (ID: %d) | Action: %s | Details: %s | IP: %s',
    current_time('mysql'),
    $user->user_login,
    $user->ID,
    $action,
    $details,
    $_SERVER['REMOTE_ADDR'] ?? 'Unknown'
  );
  error_log('WHX_AUDIT: ' . $log_entry);
}

/**
 * Rate limiting check
 */
function whx_rate_limit($action, $limit = 5, $window = 60) {
  $key = 'whx_ratelimit_' . $action . '_' . get_current_user_id();
  $count = get_transient($key) ?: 0;

  if ($count >= $limit) {
    return false; // Rate limit exceeded
  }

  set_transient($key, $count + 1, $window);
  return true;
}

/* ---------------- Options ---------------- */
function whx_opt_name(){ return 'whx_whmcs_core'; }
function whx_defaults(){
  return [
    'endpoint'    => '',
    'identifier'  => '',
    'secret'      => '',
    'accesskey'   => '',
    'currencyids' => '{"USD":2}',
    'timeout'     => 12,
    'verified_at' => 0,
    'last_error'  => '',
    // Cache clearing options
    'cf_zone_id'  => '',
    'cf_email'    => '',
    'cf_api_key'  => '',
    'cf_api_token'=> '',
    'cf_verified_at' => 0,
    'cf_last_error' => '',
    'bunny_access_key' => '',
    'bunny_zone_id'    => '',
    'bunny_verified_at' => 0,
    'bunny_last_error' => '',
    'auto_clear_cache' => 0,
  ];
}
function whx_get_opts(){
  $o = get_option(whx_opt_name(), []);
  $opts = is_array($o) ? array_merge(whx_defaults(), $o) : whx_defaults();

  // Decrypt sensitive fields
  if (!empty($opts['secret'])) {
    $opts['secret'] = whx_decrypt($opts['secret']);
  }
  if (!empty($opts['cf_api_key'])) {
    $opts['cf_api_key'] = whx_decrypt($opts['cf_api_key']);
  }
  if (!empty($opts['cf_api_token'])) {
    $opts['cf_api_token'] = whx_decrypt($opts['cf_api_token']);
  }
  if (!empty($opts['bunny_access_key'])) {
    $opts['bunny_access_key'] = whx_decrypt($opts['bunny_access_key']);
  }

  return $opts;
}
function whx_update_opts($o){
  $n = whx_opt_name();

  // Encrypt sensitive fields before storage
  $encrypted = $o;
  if (!empty($encrypted['secret'])) {
    $encrypted['secret'] = whx_encrypt($encrypted['secret']);
  }
  if (!empty($encrypted['cf_api_key'])) {
    $encrypted['cf_api_key'] = whx_encrypt($encrypted['cf_api_key']);
  }
  if (!empty($encrypted['cf_api_token'])) {
    $encrypted['cf_api_token'] = whx_encrypt($encrypted['cf_api_token']);
  }
  if (!empty($encrypted['bunny_access_key'])) {
    $encrypted['bunny_access_key'] = whx_encrypt($encrypted['bunny_access_key']);
  }

  if (get_option($n, null) === null) add_option($n, $encrypted, '', 'no');
  else update_option($n, $encrypted, false);
}

/* -------- Domain-aware defaults (multi-market) -------- */
function whx_market_map(){
  return [
    // East & Southern Africa
    'truehost.co.ke' => ['currency'=>'KES','cart'=>'https://truehost.co.ke/cloud/cart.php'],
    'truehost.co.za' => ['currency'=>'ZAR','cart'=>'https://truehost.co.za/cloud/cart.php'],
    'truehost.co.tz' => ['currency'=>'TZS','cart'=>'https://truehost.co.tz/cloud/cart.php'],
    'truehost.ug'    => ['currency'=>'UGX','cart'=>'https://truehost.ug/cloud/cart.php'],
    'truehost.com.ng'=> ['currency'=>'NGN','cart'=>'https://truehost.com.ng/cloud/cart.php'],
    'gh.truehost.com'=> ['currency'=>'GHS','cart'=>'https://gh.truehost.com/cloud/cart.php'],

    // Asia
    'truehost.co.in' => ['currency'=>'INR','cart'=>'https://truehost.co.in/cloud/cart.php'],
    'truehost.pk'    => ['currency'=>'PKR','cart'=>'https://truehost.pk/cloud/cart.php'],
    'truehost.ph'    => ['currency'=>'PHP','cart'=>'https://truehost.ph/cloud/cart.php'],

    // Europe
    'thetruehost.co.uk' => ['currency'=>'GBP','cart'=>'https://thetruehost.co.uk/cloud/cart.php'],

    // North America
    'truehost.ca'    => ['currency'=>'CAD','cart'=>'https://truehost.ca/cloud/cart.php'],

    // Oceania
    'au.truehost.com'=> ['currency'=>'AUD','cart'=>'https://au.truehost.com/cloud/cart.php'],

    // Global / Default
    'truehost.com'   => ['currency'=>'USD','cart'=>'https://truehost.com/cloud/cart.php'],
    'truehost.cloud' => ['currency'=>'USD','cart'=>'https://truehost.cloud/cloud/cart.php'],
    'truehost.dev'   => ['currency'=>'USD','cart'=>'https://truehost.dev/cloud/cart.php'],
  ];
}
function whx_current_host(){ return strtolower(parse_url(home_url(), PHP_URL_HOST)); }
function whx_market_currency(){ $m=whx_market_map(); $h=whx_current_host(); return $m[$h]['currency'] ?? 'USD'; }
function whx_market_cart_base(){ $m=whx_market_map(); $h=whx_current_host(); return $m[$h]['cart'] ?? home_url('/cloud/cart.php'); }

/* ---------------- Admin UI ---------------- */
add_action('admin_menu', function(){
  // Main menu
  add_menu_page(
    'WHMCS Core',
    'WHMCS Core',
    'manage_options',
    'whx-whmcs-core',
    'whx_render',
    'dashicons-admin-links',
    56
  );
  // Keep Settings visible + highlighted as first item
  add_submenu_page(
    'whx-whmcs-core',
    'WHMCS Core Settings',
    'Settings',
    'manage_options',
    'whx-whmcs-core',
    'whx_render'
  );
}, 1); // register early so we can sort before others add items

// Force "Settings" to stay first even if other snippets add submenus
add_action('admin_head', function(){
  global $submenu;
  if (isset($submenu['whx-whmcs-core'])) {
    usort($submenu['whx-whmcs-core'], function($a, $b){
      return ($a[2] === 'whx-whmcs-core') ? -1 : 1;
    });
  }
}, 99);

add_action('admin_init', function(){
  register_setting('whx_group', whx_opt_name(), ['sanitize_callback'=>'whx_sanitize']);

  // WHMCS API Section
  add_settings_section('whx_main','WHMCS API','','whx-whmcs-core');
  add_settings_field('endpoint','API endpoint','whx_field','whx-whmcs-core','whx_main',['key'=>'endpoint','type'=>'url','placeholder'=>'https://yourwhmcs.tld/includes/api.php']);
  add_settings_field('identifier','API identifier','whx_field','whx-whmcs-core','whx_main',['key'=>'identifier']);
  add_settings_field('secret','API secret','whx_field_secret','whx-whmcs-core','whx_main');
  add_settings_field('accesskey','API access key','whx_field','whx-whmcs-core','whx_main',['key'=>'accesskey']);
  add_settings_field('currencyids','Currency IDs JSON','whx_field','whx-whmcs-core','whx_main',['key'=>'currencyids','type'=>'textarea']);
  add_settings_field('timeout','Timeout (sec)','whx_field','whx-whmcs-core','whx_main',['key'=>'timeout','type'=>'number']);

  // Cache Clearing Section
  add_settings_section('whx_cache','Cache Clearing','whx_cache_section_desc','whx-whmcs-core');
  add_settings_field('auto_clear_cache','Auto-clear cache','whx_field_checkbox','whx-whmcs-core','whx_cache',['key'=>'auto_clear_cache','label'=>'Automatically clear cache when settings are saved']);

  // Cloudflare subsection
  add_settings_field('cf_zone_id','Cloudflare Zone ID','whx_field','whx-whmcs-core','whx_cache',['key'=>'cf_zone_id','placeholder'=>'e.g., a1b2c3d4e5f6...']);
  add_settings_field('cf_api_token','Cloudflare API Token','whx_field_password','whx-whmcs-core','whx_cache',['key'=>'cf_api_token','placeholder'=>'Preferred – API Token with Cache Purge permission']);
  add_settings_field('cf_email','Cloudflare Email','whx_field','whx-whmcs-core','whx_cache',['key'=>'cf_email','type'=>'email','placeholder'=>'Alternative – use with Global API Key']);
  add_settings_field('cf_api_key','Cloudflare Global API Key','whx_field_password','whx-whmcs-core','whx_cache',['key'=>'cf_api_key','placeholder'=>'Alternative – use with Email']);

  // Bunny CDN subsection
  add_settings_field('bunny_access_key','Bunny CDN Access Key','whx_field_password','whx-whmcs-core','whx_cache',['key'=>'bunny_access_key','placeholder'=>'Account API Key']);
  add_settings_field('bunny_zone_id','Bunny CDN Pull Zone ID','whx_field','whx-whmcs-core','whx_cache',['key'=>'bunny_zone_id','placeholder'=>'Pull Zone ID (numeric)']);
});

function whx_cache_section_desc(){
  echo '<p>Configure cache clearing for WordPress plugins, Cloudflare, and Bunny CDN. When enabled, caches are cleared automatically when settings change.</p>';
}

/* ---------------- Render Page ---------------- */
function whx_render(){
  if (!current_user_can('manage_options')) return;
  $o = whx_get_opts();

  // Notices
  if (!empty($_GET['whx_notice'])) {
    $code = sanitize_text_field($_GET['whx_notice']);
    if ($code==='ok')     echo '<div class="notice notice-success"><p><strong>Connected.</strong> WHMCS API responded successfully.</p></div>';
    if ($code==='err')    echo '<div class="notice notice-error"><p><strong>Connection failed:</strong> '.esc_html($o['last_error']?:'Unknown error').'</p></div>';
    if ($code==='cur_ok') echo '<div class="notice notice-success"><p><strong>Currencies loaded.</strong> Currency IDs JSON updated from WHMCS.</p></div>';
    if ($code==='cur_err')echo '<div class="notice notice-error"><p><strong>Could not load currencies:</strong> '.esc_html($o['last_error']?:'Unknown error').'</p></div>';
    if ($code==='cache_ok') echo '<div class="notice notice-success"><p><strong>Cache cleared.</strong> All available caches have been cleared successfully.</p></div>';
    if ($code==='cache_partial') echo '<div class="notice notice-warning"><p><strong>Cache partially cleared.</strong> Some cache services may not have been cleared. Check error log for details.</p></div>';
    if ($code==='cf_ok')   echo '<div class="notice notice-success"><p><strong>Cloudflare connected.</strong> API credentials verified successfully.</p></div>';
    if ($code==='cf_err')  echo '<div class="notice notice-error"><p><strong>Cloudflare connection failed:</strong> '.esc_html($o['cf_last_error']?:'Unknown error').'</p></div>';
    if ($code==='bunny_ok')  echo '<div class="notice notice-success"><p><strong>Bunny CDN connected.</strong> API credentials verified successfully.</p></div>';
    if ($code==='bunny_err') echo '<div class="notice notice-error"><p><strong>Bunny CDN connection failed:</strong> '.esc_html($o['bunny_last_error']?:'Unknown error').'</p></div>';
    if ($code==='rate_limit') echo '<div class="notice notice-warning"><p><strong>Rate limit exceeded.</strong> Please wait a moment before trying again.</p></div>';
  }

  // WHMCS Badge
  $status = 'not-verified';
  if ($o['verified_at']) $status = 'valid';
  if ($o['last_error'])  $status = 'invalid';
  $whmcs_badge = [
    'valid'=>'<span style="margin-left:8px;padding:2px 8px;background:#46b450;color:#fff;border-radius:3px;font-weight:600;">Valid</span>',
    'invalid'=>'<span style="margin-left:8px;padding:2px 8px;background:#dc3232;color:#fff;border-radius:3px;font-weight:600;">Invalid</span>',
    'not-verified'=>'<span style="margin-left:8px;padding:2px 8px;background:#999;color:#fff;border-radius:3px;font-weight:600;">Not verified</span>',
  ][$status];
  $whmcs_hint = $o['verified_at'] ? '<em>Last verified '.human_time_diff($o['verified_at'], current_time('timestamp')).' ago</em>' : '';

  echo '<div class="wrap"><h1>WHMCS Core '.$whmcs_badge.' '.$whmcs_hint.'</h1>';

  // Service Status Section
  echo '<div style="background:#f9f9f9;padding:15px;margin:15px 0;border-left:4px solid #0073aa;border-radius:3px;">';
  echo '<h3 style="margin-top:0;">Service Connections</h3>';
  echo '<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(250px,1fr));gap:15px;">';

  // Cloudflare Status
  $cf_status = 'not-configured';
  if (!empty($o['cf_zone_id'])) {
    $cf_status = 'not-verified';
    if ($o['cf_verified_at']) $cf_status = 'valid';
    if ($o['cf_last_error']) $cf_status = 'invalid';
  }
  $cf_badge = [
    'valid'=>'<span style="padding:2px 8px;background:#46b450;color:#fff;border-radius:3px;font-size:12px;font-weight:600;">✓ Connected</span>',
    'invalid'=>'<span style="padding:2px 8px;background:#dc3232;color:#fff;border-radius:3px;font-size:12px;font-weight:600;">✗ Failed</span>',
    'not-verified'=>'<span style="padding:2px 8px;background:#ffb900;color:#fff;border-radius:3px;font-size:12px;font-weight:600;">⚠ Not Tested</span>',
    'not-configured'=>'<span style="padding:2px 8px;background:#999;color:#fff;border-radius:3px;font-size:12px;font-weight:600;">Not Configured</span>',
  ][$cf_status];
  $cf_hint = $o['cf_verified_at'] ? '<small>Verified '.human_time_diff($o['cf_verified_at'], current_time('timestamp')).' ago</small>' : '';

  echo '<div style="background:#fff;padding:12px;border:1px solid #ddd;border-radius:3px;">';
  echo '<strong>Cloudflare CDN</strong><br>'.$cf_badge;
  if ($cf_hint) echo '<br>'.$cf_hint;
  if ($cf_status !== 'not-configured') {
    echo '<br><a class="button button-small" style="margin-top:8px;" href="'.esc_url(whx_test_cloudflare_url()).'">Test Connection</a>';
  }
  echo '</div>';

  // Bunny CDN Status
  $bunny_status = 'not-configured';
  if (!empty($o['bunny_access_key']) && !empty($o['bunny_zone_id'])) {
    $bunny_status = 'not-verified';
    if ($o['bunny_verified_at']) $bunny_status = 'valid';
    if ($o['bunny_last_error']) $bunny_status = 'invalid';
  }
  $bunny_badge = [
    'valid'=>'<span style="padding:2px 8px;background:#46b450;color:#fff;border-radius:3px;font-size:12px;font-weight:600;">✓ Connected</span>',
    'invalid'=>'<span style="padding:2px 8px;background:#dc3232;color:#fff;border-radius:3px;font-size:12px;font-weight:600;">✗ Failed</span>',
    'not-verified'=>'<span style="padding:2px 8px;background:#ffb900;color:#fff;border-radius:3px;font-size:12px;font-weight:600;">⚠ Not Tested</span>',
    'not-configured'=>'<span style="padding:2px 8px;background:#999;color:#fff;border-radius:3px;font-size:12px;font-weight:600;">Not Configured</span>',
  ][$bunny_status];
  $bunny_hint = $o['bunny_verified_at'] ? '<small>Verified '.human_time_diff($o['bunny_verified_at'], current_time('timestamp')).' ago</small>' : '';

  echo '<div style="background:#fff;padding:12px;border:1px solid #ddd;border-radius:3px;">';
  echo '<strong>Bunny CDN</strong><br>'.$bunny_badge;
  if ($bunny_hint) echo '<br>'.$bunny_hint;
  if ($bunny_status !== 'not-configured') {
    echo '<br><a class="button button-small" style="margin-top:8px;" href="'.esc_url(whx_test_bunny_url()).'">Test Connection</a>';
  }
  echo '</div>';

  echo '</div></div>'; // Close status grid and container

  // Main Action Buttons
  echo '<p style="margin:8px 0 16px 0">';
  echo '<a class="button button-secondary" href="'.esc_url(whx_test_url()).'">Test WHMCS</a> ';
  echo '<a class="button" href="'.esc_url(whx_fetch_currencies_url()).'">Fetch Currencies</a> ';
  echo '<a class="button button-primary" href="'.esc_url(whx_clear_cache_url()).'">Clear All Caches</a>';
  echo '</p>';

  echo '<form method="post" action="options.php">';
  settings_fields('whx_group'); do_settings_sections('whx-whmcs-core'); submit_button('Save Settings');
  echo '</form>';

  if ($o['last_error'])
    echo '<p style="margin-top:12px;color:#dc3232;"><strong>WHMCS Error:</strong> '.esc_html($o['last_error']).'</p>';
  if ($o['cf_last_error'])
    echo '<p style="margin-top:12px;color:#dc3232;"><strong>Cloudflare Error:</strong> '.esc_html($o['cf_last_error']).'</p>';
  if ($o['bunny_last_error'])
    echo '<p style="margin-top:12px;color:#dc3232;"><strong>Bunny CDN Error:</strong> '.esc_html($o['bunny_last_error']).'</p>';
  echo '</div>';
}

/* ---------------- Fields ---------------- */
function whx_field($args){
  $o = whx_get_opts(); $k=$args['key']; $t=$args['type']??'text'; $ph=$args['placeholder']??''; $v=$o[$k];
  if     ($t==='textarea') echo '<textarea name="'.esc_attr(whx_opt_name()).'['.$k.']" rows="4" class="large-text code">'.esc_textarea($v).'</textarea>';
  elseif ($t==='number')   echo '<input type="number" min="3" max="60" name="'.esc_attr(whx_opt_name()).'['.$k.']" value="'.esc_attr($v).'">';
  else                     echo '<input type="'.$t.'" name="'.esc_attr(whx_opt_name()).'['.$k.']" value="'.esc_attr($v).'" class="regular-text" placeholder="'.esc_attr($ph).'">';
}
function whx_field_secret(){
  $o = whx_get_opts();
  $label = $o['secret'] ? 'set' : 'not set';
  $color = $o['secret'] ? '#46b450' : '#999';
  if ($o['secret'] && $o['last_error'] && stripos($o['last_error'],'auth')!==false) { $label='auth failed'; $color='#dc3232'; }
  $badge = '<span style="margin-left:8px;padding:2px 8px;background:'.$color.';color:#fff;border-radius:3px;font-weight:600;">'.$label.'</span>';
  echo '<input type="password" name="'.esc_attr(whx_opt_name()).'[secret]" value="" class="regular-text" autocomplete="new-password" placeholder="•••••••• (leave blank to keep current)" />'.$badge;
}
function whx_field_checkbox($args){
  $o = whx_get_opts(); $k=$args['key']; $label=$args['label']??''; $checked = !empty($o[$k]) ? 'checked' : '';
  echo '<label><input type="checkbox" name="'.esc_attr(whx_opt_name()).'['.$k.']" value="1" '.$checked.'> '.esc_html($label).'</label>';
}
function whx_field_password($args){
  $o = whx_get_opts(); $k=$args['key']; $ph=$args['placeholder']??'';
  $is_set = !empty($o[$k]);
  $label = $is_set ? 'set' : 'not set';
  $color = $is_set ? '#46b450' : '#999';

  // Special handling for Cloudflare/Bunny to show error state
  if ($k === 'cf_api_token' || $k === 'cf_api_key') {
    if ($is_set && !empty($o['cf_last_error'])) { $label='auth failed'; $color='#dc3232'; }
  } elseif ($k === 'bunny_access_key') {
    if ($is_set && !empty($o['bunny_last_error'])) { $label='auth failed'; $color='#dc3232'; }
  }

  $badge = '<span style="margin-left:8px;padding:2px 8px;background:'.$color.';color:#fff;border-radius:3px;font-weight:600;">'.$label.'</span>';
  echo '<input type="password" name="'.esc_attr(whx_opt_name()).'['.$k.']" value="" class="regular-text" autocomplete="new-password" placeholder="'.esc_attr($ph).'" />'.$badge;
  if ($is_set) echo '<br><small style="color:#666;">Leave blank to keep current value</small>';
}

/* -------------- Save + validate + auto-test + auto-fetch -------------- */
function whx_sanitize($in){
  $cur = whx_get_opts(); $out = $cur;

  $out['endpoint']   = isset($in['endpoint']) ? esc_url_raw(trim($in['endpoint'])) : $cur['endpoint'];
  $out['identifier'] = isset($in['identifier']) ? sanitize_text_field(trim($in['identifier'])) : $cur['identifier'];
  $out['accesskey']  = isset($in['accesskey']) ? sanitize_text_field(trim($in['accesskey'])) : $cur['accesskey'];

  // Handle WHMCS secret (encrypted password field)
  $secret_changed = false;
  if (isset($in['secret']) && trim($in['secret'])!=='') {
    $out['secret'] = sanitize_text_field(trim($in['secret']));
    $secret_changed = true;
    whx_audit_log('WHMCS Secret Updated', 'Secret credentials changed');
  }

  $out['timeout']    = isset($in['timeout']) ? max(3,min(60,(int)$in['timeout'])) : $cur['timeout'];

  // Cache settings
  $out['auto_clear_cache'] = isset($in['auto_clear_cache']) ? 1 : 0;
  $out['cf_zone_id']  = isset($in['cf_zone_id']) ? sanitize_text_field(trim($in['cf_zone_id'])) : $cur['cf_zone_id'];
  $out['cf_email']    = isset($in['cf_email']) ? sanitize_email(trim($in['cf_email'])) : $cur['cf_email'];

  // Handle Cloudflare API Key (encrypted password field)
  $cf_changed = false;
  if (isset($in['cf_api_key']) && trim($in['cf_api_key'])!=='') {
    $out['cf_api_key'] = sanitize_text_field(trim($in['cf_api_key']));
    $cf_changed = true;
    whx_audit_log('Cloudflare API Key Updated', 'Global API Key changed');
  }

  // Handle Cloudflare API Token (encrypted password field)
  if (isset($in['cf_api_token']) && trim($in['cf_api_token'])!=='') {
    $out['cf_api_token'] = sanitize_text_field(trim($in['cf_api_token']));
    $cf_changed = true;
    whx_audit_log('Cloudflare API Token Updated', 'API Token changed');
  }

  // Handle Bunny CDN Access Key (encrypted password field)
  $bunny_changed = false;
  if (isset($in['bunny_access_key']) && trim($in['bunny_access_key'])!=='') {
    $out['bunny_access_key'] = sanitize_text_field(trim($in['bunny_access_key']));
    $bunny_changed = true;
    whx_audit_log('Bunny CDN Key Updated', 'Access Key changed');
  }

  $out['bunny_zone_id'] = isset($in['bunny_zone_id']) ? sanitize_text_field(trim($in['bunny_zone_id'])) : $cur['bunny_zone_id'];

  if (isset($in['currencyids']) && trim($in['currencyids'])!=='') {
    $map = json_decode(trim($in['currencyids']), true);
    if (!is_array($map) || empty($map)) { add_settings_error('whx_group','whx_json','Currency IDs JSON is invalid.','error'); $out['last_error']='Invalid currency JSON'; return $out; }
    $out['currencyids'] = wp_json_encode($map);
  }

  if (!$out['endpoint'] || stripos($out['endpoint'],'https://')!==0) { add_settings_error('whx_group','whx_ep','Endpoint must be HTTPS.','error'); $out['last_error']='Invalid endpoint'; return $out; }

  if ($out['identifier'] && $out['secret']) {
    $cid = (int)reset(json_decode($out['currencyids'], true));
    $ok  = whx_quick_test($out, $cid);
    if ($ok === true) {
      $out['verified_at']=current_time('timestamp');
      $out['last_error']='';
      add_settings_error('whx_group','whx_ok','Settings saved. Connected to WHMCS.','updated');
      $looks_default = (trim($out['currencyids']) === '{"USD":2}');
      if ($secret_changed || $looks_default) {
        $fetch = whx_fetch_currencies_now($out);
        if (is_array($fetch) && $fetch) {
          $out['currencyids'] = wp_json_encode($fetch);
          add_settings_error('whx_group','whx_cur_auto','Currencies fetched and saved.','updated');
        } elseif (is_string($fetch) && $fetch) {
          $out['last_error'] = $fetch;
          add_settings_error('whx_group','whx_cur_err','Connected, but currency fetch failed: '.$fetch,'error');
        }
      }
      // Auto-clear cache if enabled
      if ($out['auto_clear_cache']) {
        whx_clear_cache();
        add_settings_error('whx_group','whx_cache_auto','Cache cleared automatically.','updated');
      }
    } else {
      $out['last_error']= is_string($ok)?$ok:'Unknown error';
      add_settings_error('whx_group','whx_err','Settings saved, but connection failed: '.$out['last_error'],'error');
    }
  } else {
    add_settings_error('whx_group','whx_saved','Settings saved. Enter identifier & secret to connect.','updated');
  }
  return $out;
}

/* ---------- Test + Notices ---------- */
function whx_quick_test($cfg,$currencyId){
  $r = wp_remote_post($cfg['endpoint'], [
    'timeout'=>(int)$cfg['timeout'],
    'sslverify'=>true,
    'body' => [
      'action'=>'GetTLDPricing',
      'identifier'=>$cfg['identifier'],
      'secret'=>$cfg['secret'],
      'accesskey'=>$cfg['accesskey'],
      'currencyid'=>(int)$currencyId,
      'responsetype'=>'json'
    ]
  ]);
  if (is_wp_error($r)) return whx_sanitize_error($r->get_error_message());
  $j = json_decode(wp_remote_retrieve_body($r), true);
  if (!is_array($j)) return 'Non-JSON response';
  if (!empty($j['pricing'])) return true;
  if (!empty($j['message'])) return whx_sanitize_error($j['message']);
  return 'Unexpected response';
}
add_action('admin_notices', function(){
  if (isset($_GET['page']) && $_GET['page']==='whx-whmcs-core') settings_errors('whx_group');
});

/* -------------- Buttons: Test + Fetch Currencies + Clear Cache -------------- */
function whx_test_url(){ return wp_nonce_url(admin_url('admin-post.php?action=whx_test'), 'whx_test'); }
add_action('admin_post_whx_test', function(){
  if (!current_user_can('manage_options') || !check_admin_referer('whx_test')) wp_die('Not allowed.');
  $o = whx_get_opts(); $map=json_decode($o['currencyids'],true); $cid=is_array($map)?(int)reset($map):1;
  $ok = ($o['identifier'] && $o['secret']) ? whx_quick_test($o,$cid) : 'Missing identifier/secret';
  if ($ok===true){ $o['verified_at']=current_time('timestamp'); $o['last_error']=''; whx_update_opts($o); wp_safe_redirect(add_query_arg('whx_notice','ok',admin_url('admin.php?page=whx-whmcs-core'))); }
  else { $o['last_error']=is_string($ok)?$ok:'Unknown error'; whx_update_opts($o); wp_safe_redirect(add_query_arg('whx_notice','err',admin_url('admin.php?page=whx-whmcs-core'))); }
  exit;
});

function whx_fetch_currencies_url(){ return wp_nonce_url(admin_url('admin-post.php?action=whx_fetch_currencies'), 'whx_fetch_currencies'); }
add_action('admin_post_whx_fetch_currencies', function(){
  if (!current_user_can('manage_options') || !check_admin_referer('whx_fetch_currencies')) wp_die('Not allowed.');
  $o = whx_get_opts(); $map = whx_fetch_currencies_now($o);
  if (is_array($map) && $map) { $o['currencyids']=wp_json_encode($map); $o['last_error']=''; whx_update_opts($o); wp_safe_redirect(add_query_arg('whx_notice','cur_ok',admin_url('admin.php?page=whx-whmcs-core'))); }
  else { $o['last_error']= is_string($map)?$map:'Currencies list was empty'; whx_update_opts($o); wp_safe_redirect(add_query_arg('whx_notice','cur_err',admin_url('admin.php?page=whx-whmcs-core'))); }
  exit;
});

function whx_clear_cache_url(){ return wp_nonce_url(admin_url('admin-post.php?action=whx_clear_cache'), 'whx_clear_cache'); }
add_action('admin_post_whx_clear_cache', function(){
  if (!current_user_can('manage_options') || !check_admin_referer('whx_clear_cache')) wp_die('Not allowed.');

  // Rate limiting
  if (!whx_rate_limit('clear_cache', 10, 60)) {
    wp_safe_redirect(add_query_arg('whx_notice', 'rate_limit', admin_url('admin.php?page=whx-whmcs-core')));
    exit;
  }

  $result = whx_clear_cache();
  $notice = $result['success'] ? 'cache_ok' : 'cache_partial';
  whx_audit_log('Cache Cleared', 'Manual cache clear triggered');
  wp_safe_redirect(add_query_arg('whx_notice', $notice, admin_url('admin.php?page=whx-whmcs-core')));
  exit;
});

// Cloudflare test connection
function whx_test_cloudflare_url(){ return wp_nonce_url(admin_url('admin-post.php?action=whx_test_cloudflare'), 'whx_test_cloudflare'); }
add_action('admin_post_whx_test_cloudflare', function(){
  if (!current_user_can('manage_options') || !check_admin_referer('whx_test_cloudflare')) wp_die('Not allowed.');

  // Rate limiting
  if (!whx_rate_limit('test_cloudflare', 5, 60)) {
    wp_safe_redirect(add_query_arg('whx_notice', 'rate_limit', admin_url('admin.php?page=whx-whmcs-core')));
    exit;
  }

  $o = whx_get_opts();
  $test = whx_test_cloudflare_connection($o);

  if ($test === true) {
    $o['cf_verified_at'] = current_time('timestamp');
    $o['cf_last_error'] = '';
    whx_update_opts($o);
    whx_audit_log('Cloudflare Test Success', 'Cloudflare connection verified');
    wp_safe_redirect(add_query_arg('whx_notice', 'cf_ok', admin_url('admin.php?page=whx-whmcs-core')));
  } else {
    $o['cf_last_error'] = is_string($test) ? $test : 'Unknown error';
    whx_update_opts($o);
    whx_audit_log('Cloudflare Test Failed', whx_sanitize_error($o['cf_last_error']));
    wp_safe_redirect(add_query_arg('whx_notice', 'cf_err', admin_url('admin.php?page=whx-whmcs-core')));
  }
  exit;
});

// Bunny CDN test connection
function whx_test_bunny_url(){ return wp_nonce_url(admin_url('admin-post.php?action=whx_test_bunny'), 'whx_test_bunny'); }
add_action('admin_post_whx_test_bunny', function(){
  if (!current_user_can('manage_options') || !check_admin_referer('whx_test_bunny')) wp_die('Not allowed.');

  // Rate limiting
  if (!whx_rate_limit('test_bunny', 5, 60)) {
    wp_safe_redirect(add_query_arg('whx_notice', 'rate_limit', admin_url('admin.php?page=whx-whmcs-core')));
    exit;
  }

  $o = whx_get_opts();
  $test = whx_test_bunny_connection($o);

  if ($test === true) {
    $o['bunny_verified_at'] = current_time('timestamp');
    $o['bunny_last_error'] = '';
    whx_update_opts($o);
    whx_audit_log('Bunny CDN Test Success', 'Bunny CDN connection verified');
    wp_safe_redirect(add_query_arg('whx_notice', 'bunny_ok', admin_url('admin.php?page=whx-whmcs-core')));
  } else {
    $o['bunny_last_error'] = is_string($test) ? $test : 'Unknown error';
    whx_update_opts($o);
    whx_audit_log('Bunny CDN Test Failed', whx_sanitize_error($o['bunny_last_error']));
    wp_safe_redirect(add_query_arg('whx_notice', 'bunny_err', admin_url('admin.php?page=whx-whmcs-core')));
  }
  exit;
});

/* -------------- Test Connection Functions -------------- */

/**
 * Test Cloudflare API connection
 */
function whx_test_cloudflare_connection($opts) {
  $zone_id = $opts['cf_zone_id'] ?? '';
  $api_token = $opts['cf_api_token'] ?? '';
  $email = $opts['cf_email'] ?? '';
  $api_key = $opts['cf_api_key'] ?? '';

  if (empty($zone_id)) {
    return 'Zone ID is required';
  }

  // Determine authentication method
  $headers = ['Content-Type' => 'application/json'];
  if (!empty($api_token)) {
    $headers['Authorization'] = 'Bearer ' . $api_token;
  } elseif (!empty($email) && !empty($api_key)) {
    $headers['X-Auth-Email'] = $email;
    $headers['X-Auth-Key'] = $api_key;
  } else {
    return 'API Token or (Email + API Key) required';
  }

  // Verify zone access
  $response = wp_remote_get(
    "https://api.cloudflare.com/client/v4/zones/{$zone_id}",
    [
      'headers' => $headers,
      'timeout' => 15,
      'sslverify' => true,
    ]
  );

  if (is_wp_error($response)) {
    return whx_sanitize_error($response->get_error_message());
  }

  $body = json_decode(wp_remote_retrieve_body($response), true);

  if (isset($body['success']) && $body['success']) {
    return true;
  }

  if (isset($body['errors'][0]['message'])) {
    return whx_sanitize_error($body['errors'][0]['message']);
  }

  return 'Unable to verify Cloudflare credentials';
}

/**
 * Test Bunny CDN API connection
 */
function whx_test_bunny_connection($opts) {
  $access_key = $opts['bunny_access_key'] ?? '';
  $zone_id = $opts['bunny_zone_id'] ?? '';

  if (empty($access_key) || empty($zone_id)) {
    return 'Access Key and Pull Zone ID required';
  }

  // Get pull zone info
  $response = wp_remote_get(
    "https://api.bunny.net/pullzone/{$zone_id}",
    [
      'headers' => [
        'AccessKey' => $access_key,
        'Content-Type' => 'application/json',
      ],
      'timeout' => 15,
      'sslverify' => true,
    ]
  );

  if (is_wp_error($response)) {
    return whx_sanitize_error($response->get_error_message());
  }

  $status_code = wp_remote_retrieve_response_code($response);

  if ($status_code === 200) {
    return true;
  }

  if ($status_code === 401 || $status_code === 403) {
    return 'Authentication failed - Invalid Access Key';
  }

  if ($status_code === 404) {
    return 'Pull Zone not found - Invalid Zone ID';
  }

  $body = wp_remote_retrieve_body($response);
  return 'HTTP ' . $status_code . ': ' . whx_sanitize_error($body);
}

/* -------------- Fetch currencies (shared) -------------- */
function whx_parse_currencies($json){
  $items = $json['currencies']['currency'] ?? [];
  if ($items && isset($items['id'])) $items = [ $items ];
  $map = [];
  foreach ((array)$items as $cur) {
    $code = isset($cur['code']) ? strtoupper(trim($cur['code'])) : '';
    $id   = isset($cur['id'])   ? (int)$cur['id'] : 0;
    if ($code && $id) $map[$code] = $id;
  }
  return $map;
}
function whx_fetch_currencies_now($cfg){
  if (empty($cfg['endpoint']) || empty($cfg['identifier']) || empty($cfg['secret'])) return 'Missing endpoint/identifier/secret';
  $r = wp_remote_post($cfg['endpoint'], [
    'timeout'=>(int)$cfg['timeout'],
    'sslverify'=>true,
    'body'=>[
      'action'=>'GetCurrencies',
      'identifier'=>$cfg['identifier'],
      'secret'=>$cfg['secret'],
      'accesskey'=>$cfg['accesskey'],
      'responsetype'=>'json'
    ]
  ]);
  if (is_wp_error($r)) return whx_sanitize_error($r->get_error_message());
  $j = json_decode(wp_remote_retrieve_body($r), true);
  if (!is_array($j)) return 'Non-JSON response';
  if (!empty($j['message']) && stripos($j['message'],'auth')!==false) return 'Authentication failed – check API user/IP allowlist';
  $map = whx_parse_currencies($j);
  return $map ?: 'Currencies list was empty';
}

/* ============================================================
   CACHE CLEARING FUNCTIONS
   ============================================================ */

/**
 * Main cache clearing function - clears all available caches
 * Can be called by other snippets when they make changes
 *
 * @return array ['success' => bool, 'cleared' => array, 'failed' => array]
 */
function whx_clear_cache() {
  $results = [
    'success' => true,
    'cleared' => [],
    'failed'  => [],
  ];

  // WordPress object cache
  if (function_exists('wp_cache_flush')) {
    if (wp_cache_flush()) {
      $results['cleared'][] = 'WordPress Object Cache';
    } else {
      $results['failed'][] = 'WordPress Object Cache';
      $results['success'] = false;
    }
  }

  // Clear WHX transients
  whx_clear_transients();
  $results['cleared'][] = 'WHX Transients';

  // WordPress plugins
  $results = whx_clear_plugin_caches($results);

  // CDN caches
  $results = whx_clear_cloudflare_cache($results);
  $results = whx_clear_bunny_cache($results);

  // Log results
  if (!empty($results['failed'])) {
    error_log('WHX Cache Clear - Failed to clear: ' . implode(', ', $results['failed']));
  }
  if (!empty($results['cleared'])) {
    error_log('WHX Cache Clear - Successfully cleared: ' . implode(', ', $results['cleared']));
  }

  return $results;
}

/**
 * Clear WHX-specific transients
 */
function whx_clear_transients() {
  global $wpdb;

  // Delete all WHX transients using prepared statements for security
  $wpdb->query($wpdb->prepare(
    "DELETE FROM {$wpdb->options} WHERE option_name LIKE %s OR option_name LIKE %s",
    '_transient_whx_%',
    '_transient_timeout_whx_%'
  ));

  // Also clear any locked transients
  $wpdb->query($wpdb->prepare(
    "DELETE FROM {$wpdb->options} WHERE option_name LIKE %s",
    '_transient_whx_%_lock'
  ));
}

/**
 * Clear caches for common WordPress cache plugins
 */
function whx_clear_plugin_caches($results) {

  // WP Super Cache
  if (function_exists('wp_cache_clear_cache')) {
    wp_cache_clear_cache();
    $results['cleared'][] = 'WP Super Cache';
  }

  // W3 Total Cache
  if (function_exists('w3tc_flush_all')) {
    w3tc_flush_all();
    $results['cleared'][] = 'W3 Total Cache';
  } elseif (class_exists('W3_Plugin_TotalCacheAdmin')) {
    $plugin = w3_instance('W3_Plugin_TotalCacheAdmin');
    $plugin->flush_all();
    $results['cleared'][] = 'W3 Total Cache';
  }

  // WP Rocket
  if (function_exists('rocket_clean_domain')) {
    rocket_clean_domain();
    $results['cleared'][] = 'WP Rocket';
  }
  if (function_exists('rocket_clean_minify')) {
    rocket_clean_minify();
  }

  // LiteSpeed Cache
  if (class_exists('LiteSpeed\Purge')) {
    do_action('litespeed_purge_all');
    $results['cleared'][] = 'LiteSpeed Cache';
  } elseif (class_exists('LiteSpeed_Cache_API') && method_exists('LiteSpeed_Cache_API', 'purge_all')) {
    LiteSpeed_Cache_API::purge_all();
    $results['cleared'][] = 'LiteSpeed Cache';
  }

  // WP Fastest Cache
  if (class_exists('WpFastestCache')) {
    global $wp_fastest_cache;
    if (method_exists($wp_fastest_cache, 'deleteCache')) {
      $wp_fastest_cache->deleteCache(true);
      $results['cleared'][] = 'WP Fastest Cache';
    }
  }

  // Cache Enabler
  if (class_exists('Cache_Enabler')) {
    if (method_exists('Cache_Enabler', 'clear_complete_cache')) {
      Cache_Enabler::clear_complete_cache();
      $results['cleared'][] = 'Cache Enabler';
    }
  }

  // Autoptimize
  if (class_exists('autoptimizeCache')) {
    autoptimizeCache::clearall();
    $results['cleared'][] = 'Autoptimize';
  }

  // SG Optimizer (SiteGround)
  if (function_exists('sg_cachepress_purge_cache')) {
    sg_cachepress_purge_cache();
    $results['cleared'][] = 'SG Optimizer';
  }

  // WP-Optimize
  if (class_exists('WP_Optimize') && method_exists('WP_Optimize', 'get_page_cache')) {
    $wpo_cache = WP_Optimize::get_page_cache();
    if (method_exists($wpo_cache, 'purge')) {
      $wpo_cache->purge();
      $results['cleared'][] = 'WP-Optimize';
    }
  }

  // Comet Cache
  if (class_exists('comet_cache') && method_exists('comet_cache', 'clear')) {
    comet_cache::clear();
    $results['cleared'][] = 'Comet Cache';
  }

  // Hummingbird
  if (class_exists('Hummingbird\WP_Hummingbird')) {
    do_action('wphb_clear_page_cache');
    $results['cleared'][] = 'Hummingbird';
  }

  // Swift Performance
  if (class_exists('Swift_Performance_Cache')) {
    Swift_Performance_Cache::clear_all_cache();
    $results['cleared'][] = 'Swift Performance';
  }

  // Breeze (Cloudways)
  if (class_exists('Breeze_Admin')) {
    do_action('breeze_clear_all_cache');
    $results['cleared'][] = 'Breeze';
  }

  return $results;
}

/**
 * Clear Cloudflare cache
 */
function whx_clear_cloudflare_cache($results) {
  $opts = whx_get_opts();

  $zone_id = $opts['cf_zone_id'] ?? '';
  $api_token = $opts['cf_api_token'] ?? '';
  $email = $opts['cf_email'] ?? '';
  $api_key = $opts['cf_api_key'] ?? '';

  // Skip if not configured
  if (empty($zone_id)) {
    return $results;
  }

  // Determine authentication method
  $headers = ['Content-Type' => 'application/json'];
  if (!empty($api_token)) {
    // Preferred: API Token
    $headers['Authorization'] = 'Bearer ' . $api_token;
  } elseif (!empty($email) && !empty($api_key)) {
    // Alternative: Email + Global API Key
    $headers['X-Auth-Email'] = $email;
    $headers['X-Auth-Key'] = $api_key;
  } else {
    // Not configured
    return $results;
  }

  // Purge everything
  $response = wp_remote_post(
    "https://api.cloudflare.com/client/v4/zones/{$zone_id}/purge_cache",
    [
      'headers' => $headers,
      'body'    => wp_json_encode(['purge_everything' => true]),
      'timeout' => 30,
      'sslverify' => true,
    ]
  );

  if (is_wp_error($response)) {
    $sanitized_error = whx_sanitize_error($response->get_error_message());
    $results['failed'][] = 'Cloudflare: ' . $sanitized_error;
    $results['success'] = false;
    error_log('WHX: Cloudflare cache clear failed: ' . $sanitized_error);
  } else {
    $body = json_decode(wp_remote_retrieve_body($response), true);
    if (isset($body['success']) && $body['success']) {
      $results['cleared'][] = 'Cloudflare CDN';
    } else {
      $error_msg = isset($body['errors'][0]['message']) ? $body['errors'][0]['message'] : 'Unknown error';
      $sanitized_error = whx_sanitize_error($error_msg);
      $results['failed'][] = 'Cloudflare: ' . $sanitized_error;
      $results['success'] = false;
      error_log('WHX: Cloudflare cache clear failed: ' . $sanitized_error);
    }
  }

  return $results;
}

/**
 * Clear Bunny CDN cache
 */
function whx_clear_bunny_cache($results) {
  $opts = whx_get_opts();

  $access_key = $opts['bunny_access_key'] ?? '';
  $zone_id = $opts['bunny_zone_id'] ?? '';

  // Skip if not configured
  if (empty($access_key) || empty($zone_id)) {
    return $results;
  }

  // Bunny CDN Purge API
  $response = wp_remote_post(
    "https://api.bunny.net/pullzone/{$zone_id}/purgeCache",
    [
      'headers' => [
        'AccessKey' => $access_key,
        'Content-Type' => 'application/json',
      ],
      'timeout' => 30,
      'sslverify' => true,
    ]
  );

  if (is_wp_error($response)) {
    $sanitized_error = whx_sanitize_error($response->get_error_message());
    $results['failed'][] = 'Bunny CDN: ' . $sanitized_error;
    $results['success'] = false;
    error_log('WHX: Bunny CDN cache clear failed: ' . $sanitized_error);
  } else {
    $status_code = wp_remote_retrieve_response_code($response);
    if ($status_code === 200 || $status_code === 204) {
      $results['cleared'][] = 'Bunny CDN';
    } else {
      $body = wp_remote_retrieve_body($response);
      $sanitized_error = whx_sanitize_error($body);
      $results['failed'][] = 'Bunny CDN: HTTP ' . $status_code;
      $results['success'] = false;
      error_log('WHX: Bunny CDN cache clear failed: HTTP ' . $status_code . ' - ' . $sanitized_error);
    }
  }

  return $results;
}

/* -------------- Public helpers for other snippets -------------- */
function whx_config(){
  $o = whx_get_opts();
  $m=json_decode($o['currencyids'],true);
  if(!is_array($m)) $m=['USD'=>2];
  return [
    'endpoint'=>$o['endpoint'],
    'id'=>$o['identifier'],
    'secret'=>$o['secret'],
    'accesskey'=>$o['accesskey'],
    'currency'=>array_change_key_case($m,CASE_UPPER),
    'timeout'=>(int)$o['timeout']
  ];
}
function whx_currency_id($code='USD'){ $m=whx_config()['currency']; return (int)($m[strtoupper($code)] ?? reset($m)); }
function whx_request($action,$params=[]){
  $c=whx_config(); if(!$c['endpoint']||!$c['id']||!$c['secret']) return [];
  $b=array_merge(['action'=>$action,'identifier'=>$c['id'],'secret'=>$c['secret'],'accesskey'=>$c['accesskey'],'responsetype'=>'json'],$params);
  $r=wp_remote_post($c['endpoint'],['timeout'=>$c['timeout'],'sslverify'=>true,'body'=>$b]); if(is_wp_error($r)) return [];
  $j=json_decode(wp_remote_retrieve_body($r),true); return is_array($j)?$j:[];
}
function whx_get_tld_pricing($currency=null,$ttl=30){
  $currency = $currency ?: whx_market_currency();
  $key='whx_tld_'.strtoupper($currency);
  $cached=get_transient($key); if(is_array($cached)) return $cached;
  if (get_transient($key.'_lock')) return $cached ?: [];
  set_transient($key.'_lock',1,30);
  $cid=whx_currency_id($currency); $data=whx_request('GetTLDPricing',['currencyid'=>$cid]);
  $out=[]; if(!empty($data['pricing'])) foreach($data['pricing'] as $tld=>$row){
    $p=null; if(isset($row['register'][1]) && $row['register'][1]!==-1) $p=(float)$row['register'][1];
    elseif(isset($row['register'][2]) && $row['register'][2]!==-1) $p=(float)$row['register'][2];
    if($p!==null) $out[ltrim($tld,'.')]=$p;
  }
  set_transient($key,$out,$ttl*MINUTE_IN_SECONDS); delete_transient($key.'_lock'); return $out;
}

/* -------------- Currency formatter -------------- */
function whx_money($amount,$ccy){
  $ccy = strtoupper($ccy);
  $amount = floatval($amount);

  switch ($ccy) {
    case 'KES': return 'KSh ' . number_format($amount, 0);          // Kenya
    case 'ZAR': return 'R ' . number_format($amount, 0);            // South Africa
    case 'NGN': return '₦' . number_format($amount, 0);             // Nigeria
    case 'USD': return '$' . number_format($amount, 2);             // United States
    case 'INR': return '₹' . number_format($amount, 2);             // India
    case 'CAD': return 'CA$' . number_format($amount, 2);           // Canada
    case 'TZS': return 'TSh ' . number_format($amount, 0);          // Tanzania
    case 'UGX': return 'USh ' . number_format($amount, 0);          // Uganda
    case 'GBP': return '£' . number_format($amount, 2);             // United Kingdom
    case 'AUD': return 'A$' . number_format($amount, 2);            // Australia
    case 'GHS': return 'GH₵' . number_format($amount, 2);           // Ghana
    case 'PKR': return '₨' . number_format($amount, 2);             // Pakistan
    case 'PHP': return '₱' . number_format($amount, 2);             // Philippines
    default:     return number_format($amount, 2) . ' ' . $ccy;     // Fallback
  }
}
