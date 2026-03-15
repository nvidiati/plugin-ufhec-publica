<?php
if (!defined('ABSPATH')) exit;

add_action('admin_post_ufhec_do_export', 'ufhec_do_export');
function ufhec_do_export() {
    if (!isset($_POST['ufhec_export_nonce']) || !wp_verify_nonce($_POST['ufhec_export_nonce'], 'ufhec_export')) {
        wp_die('Error de seguridad');
    }

    if (!current_user_can('edit_posts')) {
        wp_die('Sin permisos');
    }

    $include_pending = isset($_POST['include_pending']);
    $include_pdf = isset($_POST['include_pdf']);

    $status = $include_pending ? array('publish', 'pendiente') : array('publish');
    
    $query = new WP_Query(array(
        'post_type' => 'ufhec_pub',
        'post_status' => $status,
        'posts_per_page' => -1,
        'orderby' => 'meta_value_num',
        'meta_key' => '_ano',
        'order' => 'DESC'
    ));

    $filename = 'publicaciones-ufhec-' . date('Y-m-d-His') . '.csv';
    
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=' . $filename);
    header('Pragma: no-cache');
    header('Expires: 0');

    $output = fopen('php://output', 'w');
    fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

    $headers = array('Título', 'Año', 'DOI/URL', 'Autores', 'Categoría', 'Revista', 'Cuartil', 'Estado', 'Rol del Autor', 'Facultad', 'Estado Aprobación', 'Fecha');
    if ($include_pdf) $headers[] = 'PDF';

    fputcsv($output, $headers);

    if ($query->have_posts()) {
        while ($query->have_posts()) {
            $query->the_post();
            $id = get_the_ID();
            
            $row = array(
                get_the_title(),
                get_post_meta($id, '_ano', true),
                get_post_meta($id, '_doi', true),
                get_post_meta($id, '_autores', true),
                get_post_meta($id, '_categoria', true),
                get_post_meta($id, '_revista', true),
                get_post_meta($id, '_cuartil', true),
                get_post_meta($id, '_estado', true),
				get_post_meta($id, '_rol_autor', true),
                get_post_meta($id, '_facultad', true),
                get_post_status() == 'publish' ? 'Aprobado' : 'Pendiente',
                get_the_date('Y-m-d H:i:s')
            );
            
            if ($include_pdf) {
                $row[] = get_post_meta($id, '_pdf', true);
            }

            fputcsv($output, $row);
        }
    }

    fclose($output);
    wp_reset_postdata();
    exit;
}
