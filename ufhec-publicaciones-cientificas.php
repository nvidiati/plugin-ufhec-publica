<?php
/**
 * Plugin Name: UFHEC Publicaciones Científicas
 * Plugin URI: https://ufhec.edu.do
 * Description: Sistema de gestión de publicaciones científicas para UFHEC
 * Version: 1.2.1
 * Author: UFHEC
 * Text Domain: ufhec-pub
 */

if (!defined('ABSPATH')) exit;

define('UFHEC_PUB_VERSION', '1.2.1');
define('UFHEC_PUB_DIR', plugin_dir_path(__FILE__));
define('UFHEC_PUB_URL', plugin_dir_url(__FILE__));

// Include files
require_once UFHEC_PUB_DIR . 'includes/post-type.php';
require_once UFHEC_PUB_DIR . 'includes/meta-boxes.php';
require_once UFHEC_PUB_DIR . 'includes/frontend-form.php';
require_once UFHEC_PUB_DIR . 'includes/admin-functions.php';
require_once UFHEC_PUB_DIR . 'includes/export-excel.php';
require_once UFHEC_PUB_DIR . 'includes/shortcodes.php';
require_once UFHEC_PUB_DIR . 'includes/user-role.php';

// Activation
register_activation_hook(__FILE__, 'ufhec_pub_activate');
function ufhec_pub_activate() {
    ufhec_register_pub_post_type();
    ufhec_register_pub_status();
    flush_rewrite_rules();
    set_transient('ufhec_pub_notice', true, 30);
}

// Deactivation
register_deactivation_hook(__FILE__, 'ufhec_pub_deactivate');
function ufhec_pub_deactivate() {
    flush_rewrite_rules();
}

// Plugin action links
add_filter('plugin_action_links_' . plugin_basename(__FILE__), 'ufhec_pub_links');
function ufhec_pub_links($links) {
    $new_links = array(
        '<a href="' . admin_url('edit.php?post_type=ufhec_pub') . '">Ver Publicaciones</a>',
        '<a href="' . admin_url('edit.php?post_type=ufhec_pub&page=ufhec-export') . '">Exportar</a>'
    );
    return array_merge($new_links, $links);
}

// Activation notice
add_action('admin_notices', 'ufhec_pub_notice');
function ufhec_pub_notice() {
    if (get_transient('ufhec_pub_notice')) {
        ?>
        <div class="notice notice-success is-dismissible">
            <p><strong>UFHEC Publicaciones activado correctamente.</strong></p>
            <p>Paso importante: Ve a <a href="<?php echo admin_url('options-permalink.php'); ?>"><strong>Ajustes → Enlaces permanentes</strong></a> y haz clic en "Guardar cambios".</p>
            <p>Luego busca el menú <strong>"Publicaciones UFHEC"</strong> 📚 en el panel lateral.</p>
        </div>
        <?php
        delete_transient('ufhec_pub_notice');
    }
}

// Enqueue frontend
add_action('wp_enqueue_scripts', 'ufhec_pub_enqueue_front');
function ufhec_pub_enqueue_front() {
    wp_enqueue_style('ufhec-pub-css', UFHEC_PUB_URL . 'assets/css/frontend.css', array(), UFHEC_PUB_VERSION);
    wp_enqueue_script('ufhec-pub-js', UFHEC_PUB_URL . 'assets/js/frontend.js', array('jquery'), UFHEC_PUB_VERSION, true);
    wp_localize_script('ufhec-pub-js', 'ufhecPub', array(
        'ajaxurl' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('ufhec_pub_nonce')
    ));
}

// Enqueue admin
add_action('admin_enqueue_scripts', 'ufhec_pub_enqueue_admin');
function ufhec_pub_enqueue_admin($hook) {
    global $post_type;
    if ('ufhec_pub' === $post_type) {
        wp_enqueue_style('ufhec-pub-admin-css', UFHEC_PUB_URL . 'assets/css/admin.css', array(), UFHEC_PUB_VERSION);
        wp_enqueue_script('ufhec-pub-admin-js', UFHEC_PUB_URL . 'assets/js/admin.js', array('jquery'), UFHEC_PUB_VERSION, true);
    }
}
