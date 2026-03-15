<?php
if (!defined('ABSPATH')) exit;

// Quick approve action
add_filter('post_row_actions', 'ufhec_row_actions', 10, 2);
function ufhec_row_actions($actions, $post) {
    if ($post->post_type === 'ufhec_pub' && $post->post_status === 'pendiente') {
        $url = wp_nonce_url(
            admin_url('admin-post.php?action=ufhec_approve&post_id=' . $post->ID),
            'ufhec_approve_' . $post->ID
        );
        $actions['approve'] = '<a href="' . $url . '" style="color:#00a32a;">Aprobar</a>';
    }
    return $actions;
}

// Handle approval
add_action('admin_post_ufhec_approve', 'ufhec_do_approve');
function ufhec_do_approve() {
    if (!isset($_GET['post_id']) || !isset($_GET['_wpnonce'])) wp_die('Error');
    
    $post_id = intval($_GET['post_id']);
    if (!wp_verify_nonce($_GET['_wpnonce'], 'ufhec_approve_' . $post_id)) wp_die('Error');
    if (!current_user_can('edit_post', $post_id)) wp_die('Sin permisos');

    wp_update_post(array('ID' => $post_id, 'post_status' => 'publish'));
    
    wp_redirect(admin_url('edit.php?post_type=ufhec_pub&approved=1'));
    exit;
}

// Approval notice
add_action('admin_notices', 'ufhec_approval_notice');
function ufhec_approval_notice() {
    if (isset($_GET['approved'])) {
        echo '<div class="notice notice-success is-dismissible"><p>Publicación aprobada correctamente.</p></div>';
    }
}

// Pending notice
add_action('admin_notices', 'ufhec_pending_notice');
function ufhec_pending_notice() {
    $screen = get_current_screen();
    if ($screen && $screen->post_type === 'ufhec_pub') {
        $pending = wp_count_posts('ufhec_pub')->pendiente ?? 0;
        if ($pending > 0) {
            echo '<div class="notice notice-info">';
            echo '<p><strong>Hay ' . $pending . ' publicación(es) pendiente(s) de aprobación.</strong> ';
            echo '<a href="' . admin_url('edit.php?post_type=ufhec_pub&post_status=pendiente') . '">Ver pendientes</a></p>';
            echo '</div>';
        }
    }
}

// Export menu
add_action('admin_menu', 'ufhec_export_menu');
function ufhec_export_menu() {
    add_submenu_page(
        'edit.php?post_type=ufhec_pub',
        'Exportar',
        'Exportar Datos',
        'edit_posts',
        'ufhec-export',
        'ufhec_export_page'
    );
}

function ufhec_export_page() {
    $total = wp_count_posts('ufhec_pub');
    $pending = $total->pendiente ?? 0;
    $published = $total->publish ?? 0;
    ?>
    <div class="wrap">
        <h1>Exportar Publicaciones</h1>
        
        <div class="card" style="max-width:600px;">
            <h2>Descargar Excel</h2>
            <p>Exporta todas las publicaciones en formato CSV (compatible con Excel).</p>
            
            <form method="post" action="<?php echo admin_url('admin-post.php'); ?>">
                <input type="hidden" name="action" value="ufhec_do_export">
                <?php wp_nonce_field('ufhec_export', 'ufhec_export_nonce'); ?>
                
                <p>
                    <label>
                        <input type="checkbox" name="include_pending" value="1">
                        Incluir pendientes de aprobación
                    </label>
                </p>
                
                <p>
                    <label>
                        <input type="checkbox" name="include_pdf" value="1" checked>
                        Incluir enlaces a PDFs
                    </label>
                </p>
                
                <?php submit_button('Descargar Excel'); ?>
            </form>
        </div>

        <div class="card" style="max-width:600px; margin-top:20px;">
            <h2>Estadísticas</h2>
            <table class="widefat">
                <tr><td><strong>Publicadas:</strong></td><td><?php echo $published; ?></td></tr>
                <tr><td><strong>Pendientes:</strong></td><td><?php echo $pending; ?></td></tr>
                <tr><td><strong>Total:</strong></td><td><?php echo ($published + $pending); ?></td></tr>
            </table>
        </div>
    </div>
    <?php
}

// Clear cache when approving publication
add_action('transition_post_status', 'ufhec_clear_cache_on_approve', 10, 3);
function ufhec_clear_cache_on_approve($new_status, $old_status, $post) {
    if ($post->post_type !== 'ufhec_pub') return;
    
    // If publishing or updating a published post
    if ($new_status === 'publish') {
        // Clear common cache plugins
        if (function_exists('wp_cache_flush')) {
            wp_cache_flush();
        }
        if (function_exists('w3tc_flush_all')) {
            w3tc_flush_all();
        }
        if (function_exists('wp_cache_clear_cache')) {
            wp_cache_clear_cache();
        }
        if (function_exists('rocket_clean_domain')) {
            rocket_clean_domain();
        }
        if (function_exists('litespeed_purge_all')) {
            litespeed_purge_all();
        }
    }
}
