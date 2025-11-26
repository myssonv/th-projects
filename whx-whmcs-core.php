<?php
/**
 * WHX – WHMCS Core (v2.3)
 * - Settings page with badges
 * - Buttons at the TOP (Test + Fetch Currencies + Clear Cache)
 * - Auto-fetch currencies after a successful save
 * - Cached helpers for other snippets
 * - Settings submenu always first + highlighted
 * - Cache clearing for plugins, Cloudflare, Bunny CDN
 */

if (!defined('ABSPATH')) exit;

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
    'bunny_access_key' => '',
    'bunny_zone_id'    => '',
    'auto_clear_cache' => 0,
  ];
}
function whx_get_opts(){
  $o = get_option(whx_opt_name(), []);
  return is_array($o) ? array_merge(whx_defaults(), $o) : whx_defaults();
}
function whx_update_opts($o){
  $n = whx_opt_name();
  if (get_option($n, null) === null) add_option($n, $o, '', 'no');
  else update_option($n, $o, false);
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
  add_settings_field('cf_zone_id','Cloudflare Zone ID','whx_field','whx-whmcs-core','whx_cache',['key'=>'cf_zone_id','placeholder'=>'Optional – for Cloudflare cache clearing']);
  add_settings_field('cf_api_token','Cloudflare API Token','whx_field','whx-whmcs-core','whx_cache',['key'=>'cf_api_token','placeholder'=>'Preferred – API Token with Cache Purge permission']);
  add_settings_field('cf_email','Cloudflare Email','whx_field','whx-whmcs-core','whx_cache',['key'=>'cf_email','type'=>'email','placeholder'=>'Alternative – use with Global API Key']);
  add_settings_field('cf_api_key','Cloudflare Global API Key','whx_field','whx-whmcs-core','whx_cache',['key'=>'cf_api_key','placeholder'=>'Alternative – use with Email']);
  add_settings_field('bunny_access_key','Bunny CDN Access Key','whx_field','whx-whmcs-core','whx_cache',['key'=>'bunny_access_key','placeholder'=>'Optional – Bunny CDN API Key']);
  add_settings_field('bunny_zone_id','Bunny CDN Pull Zone ID','whx_field','whx-whmcs-core','whx_cache',['key'=>'bunny_zone_id','placeholder'=>'Optional – Pull Zone ID or hostname']);
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
  }

  // Badge
  $status = 'not-verified';
  if ($o['verified_at']) $status = 'valid';
  if ($o['last_error'])  $status = 'invalid';
  $badge = [
    'valid'=>'<span style="margin-left:8px;padding:2px 8px;background:#46b450;color:#fff;border-radius:3px;font-weight:600;">Valid</span>',
    'invalid'=>'<span style="margin-left:8px;padding:2px 8px;background:#dc3232;color:#fff;border-radius:3px;font-weight:600;">Invalid</span>',
    'not-verified'=>'<span style="margin-left:8px;padding:2px 8px;background:#999;color:#fff;border-radius:3px;font-weight:600;">Not verified</span>',
  ][$status];
  $hint = $o['verified_at'] ? '<em>Last verified '.human_time_diff($o['verified_at'], current_time('timestamp')).' ago</em>' : '';

  echo '<div class="wrap"><h1>WHMCS Core '.$badge.' '.$hint.'</h1>';

  // Buttons
  echo '<p style="margin:8px 0 16px 0">';
  echo '<a class="button button-secondary" href="'.esc_url(whx_test_url()).'">Test Connection</a> ';
  echo '<a class="button" href="'.esc_url(whx_fetch_currencies_url()).'">Fetch Currencies</a> ';
  echo '<a class="button button-primary" href="'.esc_url(whx_clear_cache_url()).'">Clear All Caches</a>';
  echo '</p>';

  echo '<form method="post" action="options.php">';
  settings_fields('whx_group'); do_settings_sections('whx-whmcs-core'); submit_button('Save Settings');
  echo '</form>';

  if ($o['last_error'])
    echo '<p style="margin-top:12px;color:#dc3232;"><strong>Last error:</strong> '.esc_html($o['last_error']).'</p>';
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

/* -------------- Save + validate + auto-test + auto-fetch -------------- */
function whx_sanitize($in){
  $cur = whx_get_opts(); $out = $cur;

  $out['endpoint']   = isset($in['endpoint']) ? esc_url_raw(trim($in['endpoint'])) : $cur['endpoint'];
  $out['identifier'] = isset($in['identifier']) ? sanitize_text_field(trim($in['identifier'])) : $cur['identifier'];
  $out['accesskey']  = isset($in['accesskey']) ? sanitize_text_field(trim($in['accesskey'])) : $cur['accesskey'];
  $secret_changed = false;
  if (isset($in['secret']) && trim($in['secret'])!=='') { $out['secret'] = sanitize_text_field(trim($in['secret'])); $secret_changed = true; }
  $out['timeout']    = isset($in['timeout']) ? max(3,min(60,(int)$in['timeout'])) : $cur['timeout'];

  // Cache settings
  $out['auto_clear_cache'] = isset($in['auto_clear_cache']) ? 1 : 0;
  $out['cf_zone_id']  = isset($in['cf_zone_id']) ? sanitize_text_field(trim($in['cf_zone_id'])) : $cur['cf_zone_id'];
  $out['cf_email']    = isset($in['cf_email']) ? sanitize_email(trim($in['cf_email'])) : $cur['cf_email'];
  $out['cf_api_key']  = isset($in['cf_api_key']) ? sanitize_text_field(trim($in['cf_api_key'])) : $cur['cf_api_key'];
  $out['cf_api_token']= isset($in['cf_api_token']) ? sanitize_text_field(trim($in['cf_api_token'])) : $cur['cf_api_token'];
  $out['bunny_access_key'] = isset($in['bunny_access_key']) ? sanitize_text_field(trim($in['bunny_access_key'])) : $cur['bunny_access_key'];
  $out['bunny_zone_id']    = isset($in['bunny_zone_id']) ? sanitize_text_field(trim($in['bunny_zone_id'])) : $cur['bunny_zone_id'];

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
    'body' => [
      'action'=>'GetTLDPricing',
      'identifier'=>$cfg['identifier'],
      'secret'=>$cfg['secret'],
      'accesskey'=>$cfg['accesskey'],
      'currencyid'=>(int)$currencyId,
      'responsetype'=>'json'
    ]
  ]);
  if (is_wp_error($r)) return $r->get_error_message();
  $j = json_decode(wp_remote_retrieve_body($r), true);
  if (!is_array($j)) return 'Non-JSON response';
  if (!empty($j['pricing'])) return true;
  if (!empty($j['message'])) return $j['message'];
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
  $result = whx_clear_cache();
  $notice = $result['success'] ? 'cache_ok' : 'cache_partial';
  wp_safe_redirect(add_query_arg('whx_notice', $notice, admin_url('admin.php?page=whx-whmcs-core')));
  exit;
});

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
    'body'=>[
      'action'=>'GetCurrencies',
      'identifier'=>$cfg['identifier'],
      'secret'=>$cfg['secret'],
      'accesskey'=>$cfg['accesskey'],
      'responsetype'=>'json'
    ]
  ]);
  if (is_wp_error($r)) return $r->get_error_message();
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

  // Delete all WHX transients
  $wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_whx_%' OR option_name LIKE '_transient_timeout_whx_%'");

  // Also clear any locked transients
  $wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_whx_%_lock'");
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
    ]
  );

  if (is_wp_error($response)) {
    $results['failed'][] = 'Cloudflare: ' . $response->get_error_message();
    $results['success'] = false;
    error_log('Cloudflare cache clear failed: ' . $response->get_error_message());
  } else {
    $body = json_decode(wp_remote_retrieve_body($response), true);
    if (isset($body['success']) && $body['success']) {
      $results['cleared'][] = 'Cloudflare CDN';
    } else {
      $error_msg = isset($body['errors'][0]['message']) ? $body['errors'][0]['message'] : 'Unknown error';
      $results['failed'][] = 'Cloudflare: ' . $error_msg;
      $results['success'] = false;
      error_log('Cloudflare cache clear failed: ' . $error_msg);
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
    ]
  );

  if (is_wp_error($response)) {
    $results['failed'][] = 'Bunny CDN: ' . $response->get_error_message();
    $results['success'] = false;
    error_log('Bunny CDN cache clear failed: ' . $response->get_error_message());
  } else {
    $status_code = wp_remote_retrieve_response_code($response);
    if ($status_code === 200 || $status_code === 204) {
      $results['cleared'][] = 'Bunny CDN';
    } else {
      $body = wp_remote_retrieve_body($response);
      $results['failed'][] = 'Bunny CDN: HTTP ' . $status_code;
      $results['success'] = false;
      error_log('Bunny CDN cache clear failed: HTTP ' . $status_code . ' - ' . $body);
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
  $r=wp_remote_post($c['endpoint'],['timeout'=>$c['timeout'],'body'=>$b]); if(is_wp_error($r)) return [];
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
