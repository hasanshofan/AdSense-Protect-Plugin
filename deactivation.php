<?php
// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function cl_deactivation_hook_handler() {
    // Check for user consent to delete data
    // This is a simplified approach. In a full plugin, you'd use an admin page with buttons.
    // This script will prompt the user to make a choice.

    $delete_choice = get_option('cl_delete_on_deactivate', 'no'); // Default to 'no'
    
    // If the user has chosen to delete, then proceed with deletion
    if ($delete_choice === 'yes') {
        global $wpdb;
        $table_name = $wpdb->prefix . 'invalid_clicks_log';
        $wpdb->query("DROP TABLE IF EXISTS $table_name");
        delete_option('cl_delete_on_deactivate');
    }
}
register_deactivation_hook( __FILE__, 'cl_deactivation_hook_handler' );

// This is the code for the admin page to set the user's choice.
// You need to add this to your main plugin file.
add_action('admin_init', 'cl_deactivate_options_init');
function cl_deactivate_options_init() {
    register_setting( 'cl_options_group', 'cl_delete_on_deactivate' );
    add_settings_section(
        'cl_main_section', 
        'خيارات الحذف عند التعطيل', 
        'cl_main_section_callback', 
        'invalid-clicks-logger'
    );
    add_settings_field(
        'cl_delete_on_deactivate_field', 
        'حذف البيانات', 
        'cl_delete_on_deactivate_field_callback', 
        'invalid-clicks-logger', 
        'cl_main_section'
    );
}

function cl_main_section_callback() {
    echo 'اختر ما إذا كنت تريد حذف سجلات النقرات عند تعطيل الإضافة.';
}

function cl_delete_on_deactivate_field_callback() {
    $option = get_option('cl_delete_on_deactivate', 'no');
    echo '<input type="radio" name="cl_delete_on_deactivate" value="yes" ' . checked('yes', $option, false) . '> نعم، احذف البيانات.';
    echo '<br><input type="radio" name="cl_delete_on_deactivate" value="no" ' . checked('no', $option, false) . '> لا، احتفظ بالبيانات.';
}