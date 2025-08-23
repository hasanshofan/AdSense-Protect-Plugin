<?php
/**
 * Plugin Name:       AdSense Protect
 * Description:       Logs user data to identify suspicious activity.
 * Version:           1.0.0
 * Author:            Your Name
 * Author URI:        https://example.com
 * License:           GPLv2 or later
 * Text Domain:       click-logger
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

//---------------------------------------------------------
// إعدادات الإضافة (قابلة للتعديل)
//---------------------------------------------------------

/**
 * تسجيل الزيارات الداخلية للمقالات والصفحات.
 *
 * قم بتعيين القيمة إلى `true` لتسجيل الزيارات الداخلية.
 * القيمة الافتراضية هي `false` لتسجيل النقرات الخارجية فقط.
 */
define('CL_LOG_INTERNAL_VISITS', false);

/**
 * الحد الأقصى لعدد السجلات في قاعدة البيانات.
 *
 * عندما يصل عدد السجلات إلى هذا الحد، ستقوم الإضافة بحذف السجلات القديمة.
 */
define('CL_MAX_LOGS_COUNT', 50000);

// Include the deactivation handler file.
require_once plugin_dir_path( __FILE__ ) . 'deactivation.php';

//---------------------------------------------------------
// دالة إنشاء الجدول
//---------------------------------------------------------
function cl_create_logger_table() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'invalid_clicks_log';
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $table_name (
        id bigint(20) NOT NULL AUTO_INCREMENT,
        ip_address varchar(45) NOT NULL,
        user_agent varchar(255) NOT NULL,
        request_time datetime NOT NULL,
        requested_page varchar(255) NOT NULL,
        PRIMARY KEY  (id)
    ) $charset_collate;";

    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
    dbDelta( $sql );
}
register_activation_hook( __FILE__, 'cl_create_logger_table' );

//---------------------------------------------------------
// تسجيل الزيارات الداخلية (حسب إعدادات المستخدم)
//---------------------------------------------------------
function cl_log_user_data() {
    // تحقق من إعداد المستخدم قبل تسجيل أي شيء
    if ( ! CL_LOG_INTERNAL_VISITS ) {
        return;
    }

    // استثناء صفحات لوحة التحكم وجميع العمليات الخلفية المعروفة
    if ( is_admin() || strpos($_SERVER['REQUEST_URI'], '/xmlrpc.php') !== false || strpos($_SERVER['REQUEST_URI'], '/wp-json/') !== false || strpos($_SERVER['REQUEST_URI'], 'wp-cron.php') !== false || strpos($_SERVER['REQUEST_URI'], 'wc-ajax') !== false || strpos($_SERVER['REQUEST_URI'], 'wp_scrape_key') !== false || in_array(pathinfo($_SERVER['REQUEST_URI'], PATHINFO_EXTENSION), array('css', 'js', 'png', 'jpg', 'gif', 'svg', 'ico', 'map', 'woff', 'ttf', 'eot', 'json', 'xml')) ) {
        return;
    }

    global $wpdb;
    $table_name = $wpdb->prefix . 'invalid_clicks_log';
    
    $wpdb->insert(
        $table_name,
        array(
            'ip_address'     => $_SERVER['REMOTE_ADDR'],
            'user_agent'     => $_SERVER['HTTP_USER_AGENT'],
            'request_time'   => current_time('mysql'),
            'requested_page' => $_SERVER['REQUEST_URI']
        )
    );
}
add_action( 'wp_loaded', 'cl_log_user_data' );

//---------------------------------------------------------
// تسجيل النقرات الخارجية
//---------------------------------------------------------

// Add the JavaScript file to the front-end
function cl_enqueue_scripts() {
    wp_enqueue_script(
        'click-tracker', 
        plugins_url('js/click-tracker.js', __FILE__), 
        array('jquery'), 
        '1.0', 
        true
    );
    wp_localize_script(
        'click-tracker', 
        'MyAjax', 
        array('ajaxurl' => admin_url('admin-ajax.php'))
    );
}
add_action('wp_enqueue_scripts', 'cl_enqueue_scripts');

// Create the PHP handler for the AJAX request
function cl_track_external_click_handler() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'invalid_clicks_log';
    
    $external_url = isset($_POST['url']) ? sanitize_text_field(wp_unslash($_POST['url'])) : '';
    $ip_address = $_SERVER['REMOTE_ADDR'];
    $user_agent = $_SERVER['HTTP_USER_AGENT'];
    
    $marked_url = '[EXTERNAL_CLICK] ' . $external_url;
    
    $wpdb->insert(
        $table_name,
        array(
            'ip_address'     => $ip_address,
            'user_agent'     => $user_agent,
            'request_time'   => current_time('mysql'),
            'requested_page' => $marked_url
        )
    );
    
    wp_die();
}
add_action('wp_ajax_track_external_click', 'cl_track_external_click_handler');
add_action('wp_ajax_nopriv_track_external_click', 'cl_track_external_click_handler');

//---------------------------------------------------------
// إدارة السجلات والحذف التلقائي
//---------------------------------------------------------
function cl_schedule_cleanup() {
    if ( ! wp_next_scheduled( 'cl_hourly_cleanup_hook' ) ) {
        wp_schedule_event( time(), 'hourly', 'cl_hourly_cleanup_hook' );
    }
}
register_activation_hook( __FILE__, 'cl_schedule_cleanup' );

function cl_deactivate_cleanup() {
    wp_clear_scheduled_hook( 'cl_hourly_cleanup_hook' );
}
register_deactivation_hook( __FILE__, 'cl_deactivate_cleanup' );

function cl_hourly_cleanup_handler() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'invalid_clicks_log';
    
    // Cleanup based on time (24 hours)
    $wpdb->query("DELETE FROM $table_name WHERE request_time < DATE_SUB(NOW(), INTERVAL 24 HOUR)");

    // Cleanup based on max logs count
    $current_count = $wpdb->get_var("SELECT COUNT(*) FROM $table_name");
    if ($current_count > CL_MAX_LOGS_COUNT) {
        $logs_to_delete = $current_count - (CL_MAX_LOGS_COUNT - 10000);
        $wpdb->query("DELETE FROM $table_name ORDER BY request_time ASC LIMIT $logs_to_delete");
    }
}
add_action( 'cl_hourly_cleanup_hook', 'cl_hourly_cleanup_handler' );

//---------------------------------------------------------
// صفحة لوحة التحكم
//---------------------------------------------------------

function cl_add_admin_menu() {
    add_menu_page(
        'AdSense Protect', 
        'AdSense Protect', 
        'manage_options', 
        'adsense-protect-logger', // مسار جديد
        'cl_display_logger_page',
        'dashicons-chart-bar'
    );
}
add_action( 'admin_menu', 'cl_add_admin_menu' );

// Handle the manual log deletion from the admin page
function cl_delete_all_logs_handler() {
    if ( !isset( $_POST['_wpnonce'] ) || !wp_verify_nonce( $_POST['_wpnonce'], 'cl_delete_all_logs_nonce' ) ) {
       wp_die( 'فشل التحقق الأمني.' );
    }

    global $wpdb;
    $table_name = $wpdb->prefix . 'invalid_clicks_log';
    $wpdb->query("TRUNCATE TABLE $table_name");
    
    wp_redirect( admin_url('admin.php?page=invalid-clicks-logger&message=deleted') );
    exit;
}
add_action( 'admin_post_cl_delete_all_logs', 'cl_delete_all_logs_handler' );


//--2. Creating the Export Function

function cl_export_external_logs_handler() {
    if ( !isset( $_POST['_wpnonce'] ) || !wp_verify_nonce( $_POST['_wpnonce'], 'cl_export_external_logs_nonce' ) ) {
       wp_die( 'فشل التحقق الأمني.' );
    }

    global $wpdb;
    $table_name = $wpdb->prefix . 'invalid_clicks_log';

    $results = $wpdb->get_results(
        "SELECT ip_address, user_agent, requested_page, COUNT(*) AS click_count
         FROM $table_name
         WHERE requested_page LIKE '[EXTERNAL_CLICK]%%'
         GROUP BY ip_address, user_agent, requested_page
         ORDER BY click_count DESC",
        ARRAY_A
    );

    $filename = 'external-clicks-' . date('Y-m-d') . '.csv';

    // Start a buffer to avoid any output before headers
    ob_start();

    // Prepare CSV headers
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');

    $output = fopen('php://output', 'w');
    fputcsv($output, array('IP Address', 'User Agent', 'Clicks Count', 'Requested Page / External Link'));

    if (!empty($results)) {
        foreach ($results as $row) {
            fputcsv($output, array(
                $row['ip_address'],
                $row['user_agent'],
                $row['click_count'],
                str_replace('[EXTERNAL_CLICK] ', '', $row['requested_page'])
            ));
        }
    }

    fclose($output);

    // End the buffer and send the output
    ob_end_flush();
    exit;
}
add_action( 'admin_post_cl_export_external_logs', 'cl_export_external_logs_handler' );



// The function for the admin page content
function cl_display_logger_page() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'invalid_clicks_log';
    
    //---------------------------------------------------------
    // إعدادات ديناميكية (يمكن تعديلها لاحقاً)
    //---------------------------------------------------------
    $logs_per_page = 100;
    $suspicious_ips_limit = 30;

    //---------------------------------------------------------
    // قسم لعرض السجلات المجمّعة مع نظام Pagination
    //---------------------------------------------------------
    $total_logs_count = $wpdb->get_var("SELECT COUNT(DISTINCT ip_address, user_agent, requested_page) FROM $table_name");
    $current_page = isset($_GET['paged']) ? max(1, intval($_GET['paged'])) : 1;
    $offset = ($current_page - 1) * $logs_per_page;
    $total_pages = ceil($total_logs_count / $logs_per_page);

    $query_all = $wpdb->prepare(
        "SELECT ip_address, user_agent, requested_page, COUNT(*) AS click_count, MIN(request_time) AS first_click, MAX(request_time) AS last_click
         FROM $table_name
         GROUP BY ip_address, user_agent, requested_page
         ORDER BY last_click DESC
         LIMIT %d OFFSET %d",
        $logs_per_page,
        $offset
    );
    
    $results_all = $wpdb->get_results( $query_all, ARRAY_A );
    
    // Get the list of suspicious IPs for highlighting
    $suspicious_ips_query = $wpdb->prepare(
        "SELECT ip_address FROM $table_name
         WHERE requested_page LIKE '[EXTERNAL_CLICK]%%'
         GROUP BY ip_address
         HAVING COUNT(*) >= 3
         ORDER BY COUNT(*) DESC
         LIMIT %d",
        $suspicious_ips_limit
    );
    $suspicious_ips_list = $wpdb->get_col($suspicious_ips_query);
    
    echo '<div class="wrap">';
    echo '<h1>سجل الزيارات الكامل</h1>';
    
    if ($total_pages > 1) {
        echo '<div class="tablenav bottom"><div class="tablenav-pages">';
        echo '<span class="displaying-pages">' . esc_html($total_logs_count) . ' سجل مجمّع</span>';
        $page_links = paginate_links(array(
            'base' => add_query_arg('paged', '%#%'),
            'format' => '',
            'prev_text' => '&laquo; السابق',
            'next_text' => 'التالي &raquo;',
            'total' => $total_pages,
            'current' => $current_page,
        ));
        echo $page_links;
        echo '</div></div>';
    }

    echo '<table class="wp-list-table widefat fixed striped">';
    echo '<thead><tr><th>IP Address</th><th>User Agent</th><th>Clicks</th><th>Requested Page / External Link</th><th>Time Range</th></tr></thead>';
    echo '<tbody>';
    
    if ( $results_all ) {
        foreach ( $results_all as $row ) {
            $is_suspicious = in_array($row['ip_address'], $suspicious_ips_list);
            $row_class = $is_suspicious ? 'class="suspicious-ip"' : '';
            $ip_html = $is_suspicious ? '<span style="color:red; font-weight:bold;">' . esc_html($row['ip_address']) . '</span>' : esc_html($row['ip_address']);
            
            echo '<tr ' . $row_class . '>';
            echo '<td>' . $ip_html . '</td>';
            echo '<td>' . esc_html( $row['user_agent'] ) . '</td>';
            echo '<td>' . ($row['click_count'] > 1 ? esc_html($row['click_count']) : '') . '</td>'; // Do not display 1
            echo '<td>' . esc_html( $row['requested_page'] ) . '</td>';
            echo '<td>' . esc_html( $row['first_click'] ) . ' - ' . esc_html( $row['last_click'] ) . '</td>';
            echo '</tr>';
        }
    } else {
        echo '<tr><td colspan="5">لا توجد سجلات بعد.</td></tr>';
    }
    
    echo '</tbody>';
    echo '</table>';
    
    echo '<hr style="margin: 40px 0;">';

    // The rest of the code (suspicious IPs table) remains the same
    
    $query_suspicious = $wpdb->prepare(
        "SELECT ip_address, COUNT(ip_address) AS hit_count
         FROM $table_name
         WHERE request_time > DATE_SUB(NOW(), INTERVAL 24 HOUR)
         AND requested_page LIKE '[EXTERNAL_CLICK]%%'
         GROUP BY ip_address
         HAVING hit_count >= 3
         ORDER BY hit_count DESC
         LIMIT %d",
        $suspicious_ips_limit
    );
    
    $suspicious_ips = $wpdb->get_results( $query_suspicious, ARRAY_A );
    
    if ( $suspicious_ips ) {
        echo '<h2>عناوين IP مشبوهة (3 نقرات خارجية أو أكثر في 24 ساعة)</h2>';
        echo '<table class="wp-list-table widefat fixed striped">';
        echo '<thead><tr><th>IP Address</th><th>عدد النقرات الخارجية</th><th>إجراء</th></tr></thead>';
        echo '<tbody>';
        foreach ( $suspicious_ips as $ip_data ) {
            echo '<tr>';
            echo '<td>' . esc_html( $ip_data['ip_address'] ) . '</td>';
            echo '<td>' . esc_html( $ip_data['hit_count'] ) . '</td>';
            echo '<td><button class="button button-primary cl-report-btn" data-ip="' . esc_attr( $ip_data['ip_address'] ) . '">إعداد تقرير</button></td>';
            echo '</tr>';
        }
    } else {
        echo '<tr><td colspan="3">لا توجد نقرات خارجية مشبوهة في آخر 24 ساعة.</td></tr>';
    }
    
    echo '</tbody>';
    echo '</table>';
    
    //---------------------------------------------------------
    // أزرار الحذف والتصدير الجديدة
    //---------------------------------------------------------
    echo '<div style="margin-top: 20px; display: flex; gap: 10px;">';
    
    // زر الحذف اليدوي
    echo '<form method="post" action="' . esc_url( admin_url('admin-post.php') ) . '">';
    echo '<input type="hidden" name="action" value="cl_delete_all_logs">';
    echo wp_nonce_field('cl_delete_all_logs_nonce', '_wpnonce', true, false);
    echo '<input type="submit" name="submit" id="submit" class="button button-danger" value="حذف السجل بالكامل" onclick="return confirm(\'هل أنت متأكد من أنك تريد حذف جميع السجلات بشكل دائم؟\');">';
    echo '</form>';
    
    // زر التصدير
    echo '<form method="post" action="' . esc_url( admin_url('admin-post.php') ) . '">';
    echo '<input type="hidden" name="action" value="cl_export_external_logs">';
    echo wp_nonce_field('cl_export_external_logs_nonce', '_wpnonce', true, false);
    echo '<input type="submit" class="button button-primary" value="تصدير النقرات الخارجية (.csv)">';
    echo '</form>';
    
    echo '</div>'; // End of flex container
    
    //---------------------------------------------------------
    // الكود الخاص بالنافذة المنبثقة والجافاسكريبت
    //---------------------------------------------------------
    echo '<div id="cl-report-modal" style="display:none; position:fixed; z-index:100; left:0; top:0; width:100%; height:100%; overflow:auto; background-color:rgba(0,0,0,0.4);">';
    echo '<div style="background-color:#fefefe; margin:15% auto; padding:20px; border:1px solid #888; width:80%;">';
    echo '<span class="close-btn" style="color:#aaa; float:right; font-size:28px; font-weight:bold; cursor:pointer;">&times;</span>';
    echo '<h2>تقرير IP مشبوه</h2>';
    echo '<p>انسخ هذه البيانات وأرسلها عبر <a href="https://support.google.com/adsense/contact/invalid_clicks_contact" target="_blank">نموذج بلاغ جوجل</a>.</p>';
    echo '<textarea id="cl-report-content" rows="10" style="width:100%;"></textarea>';
    echo '</div></div>';

    echo '<script>';
    echo 'document.addEventListener("DOMContentLoaded", function() {';
    echo '    var reportModal = document.getElementById("cl-report-modal");';
    echo '    var reportButtons = document.getElementsByClassName("cl-report-btn");';
    echo '    var closeBtn = document.getElementsByClassName("close-btn")[0];';
    
    echo '    if (reportModal) {';
    echo '        for (var i = 0; i < reportButtons.length; i++) {';
    echo '            reportButtons[i].onclick = function() {';
    echo '                var ip = this.getAttribute("data-ip");';
    echo '                reportModal.style.display = "block";';
    echo '                document.getElementById("cl-report-content").value = "Dear AdSense Team,\\n\\nI am writing to report suspicious click activity on my account. The following IP address has exhibited invalid clicking behavior:\\n\\nIP Address: " + ip + "\\n\\nI have attached my logs for your reference.\\n\\nThank you.";';
    echo '            };';
    echo '        }';
    
    echo '        if (closeBtn) {';
    echo '            closeBtn.onclick = function() {';
    echo '                reportModal.style.display = "none";';
    echo '            };';
    echo '        }';
    
    echo '        window.onclick = function(event) {';
    echo '            if (event.target == reportModal) {';
    echo '                reportModal.style.display = "none";';
    echo '            }';
    echo '        };';
    echo '    }';
    echo '});';
    echo '</script>';

    echo '</div>';
}