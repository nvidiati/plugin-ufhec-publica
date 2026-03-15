<?php
if (!defined('ABSPATH')) exit;

// Register post type
add_action('init', 'ufhec_register_pub_post_type', 0);
function ufhec_register_pub_post_type() {
    
    $labels = array(
        'name' => 'Publicaciones UFHEC',
        'singular_name' => 'Publicación',
        'menu_name' => 'Publicaciones UFHEC',
        'add_new' => 'Añadir Nueva',
        'add_new_item' => 'Añadir Publicación',
        'edit_item' => 'Editar Publicación',
        'new_item' => 'Nueva Publicación',
        'view_item' => 'Ver Publicación',
        'search_items' => 'Buscar Publicaciones',
        'not_found' => 'No se encontraron publicaciones',
        'not_found_in_trash' => 'No hay publicaciones en la papelera',
        'all_items' => 'Todas las Publicaciones'
    );

    $args = array(
        'labels' => $labels,
        'public' => true,
        'show_ui' => true,
        'show_in_menu' => true,
        'menu_position' => 5,
        'menu_icon' => 'dashicons-book',
        'supports' => array('title'),
        'has_archive' => false,
        'rewrite' => array('slug' => 'publicacion'),
        'capability_type' => 'post', // ← IMPORTANTE: usa 'post' estándar
        'map_meta_cap' => true, // ← IMPORTANTE: mapea capabilities
        'show_in_rest' => false
    );

    register_post_type('ufhec_pub', $args);
}

// Register custom status
add_action('init', 'ufhec_register_pub_status', 0);
function ufhec_register_pub_status() {
    register_post_status('pendiente', array(
        'label' => 'Pendiente de Aprobación',
        'public' => false,
        'exclude_from_search' => true,
        'show_in_admin_all_list' => true,
        'show_in_admin_status_list' => true,
        'label_count' => _n_noop('Pendiente <span class="count">(%s)</span>', 'Pendientes <span class="count">(%s)</span>')
    ));
}

// Add status to dropdown
add_action('admin_footer-post.php', 'ufhec_add_status_dropdown');
add_action('admin_footer-post-new.php', 'ufhec_add_status_dropdown');
function ufhec_add_status_dropdown() {
    global $post;
    if (!$post || $post->post_type != 'ufhec_pub') return;
    ?>
    <script>
    jQuery(document).ready(function($) {
        $('select#post_status').append('<option value="pendiente" <?php echo selected($post->post_status, 'pendiente', false); ?>>Pendiente de Aprobación</option>');
        if ($('#post-status-display').length && '<?php echo $post->post_status; ?>' == 'pendiente') {
            $('#post-status-display').text('Pendiente de Aprobación');
        }
    });
    </script>
    <?php
}
