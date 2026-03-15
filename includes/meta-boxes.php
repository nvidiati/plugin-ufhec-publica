<?php
if (!defined('ABSPATH')) exit;

// Add meta box
add_action('add_meta_boxes', 'ufhec_add_meta_box');
function ufhec_add_meta_box() {
    add_meta_box(
        'ufhec_pub_details',
        'Detalles de la Publicación',
        'ufhec_meta_box_callback',
        'ufhec_pub',
        'normal',
        'high'
    );
}

// Meta box callback
function ufhec_meta_box_callback($post) {
    wp_nonce_field('ufhec_save_meta', 'ufhec_meta_nonce');
    
    $ano = get_post_meta($post->ID, '_ano', true);
    $doi = get_post_meta($post->ID, '_doi', true);
    $autores = get_post_meta($post->ID, '_autores', true);
    $categoria = get_post_meta($post->ID, '_categoria', true);
    $revista = get_post_meta($post->ID, '_revista', true);
    $cuartil = get_post_meta($post->ID, '_cuartil', true);
    $estado = get_post_meta($post->ID, '_estado', true);
    $pdf = get_post_meta($post->ID, '_pdf', true);
    ?>
    <table class="form-table">
        <tr>
            <th><label for="ano">Año *</label></th>
            <td><input type="number" id="ano" name="ano" value="<?php echo esc_attr($ano); ?>" required style="width:200px;"></td>
        </tr>
        <tr>
            <th><label for="doi">DOI o URL *</label></th>
            <td><input type="url" id="doi" name="doi" value="<?php echo esc_url($doi); ?>" required style="width:100%;"></td>
        </tr>
        <tr>
            <th><label for="autores">Autores *</label></th>
            <td><textarea id="autores" name="autores" rows="3" required style="width:100%;"><?php echo esc_textarea($autores); ?></textarea></td>
        </tr>
		<tr>
            <th><label for="rol_autor">Rol del Autor/a Principal *</label></th>
            <td>
                <?php 
                $rol_autor = get_post_meta($post->ID, '_rol_autor', true);
                $roles = array(
                    'Docente',
                    'Docente–Investigador/a',
                    'Investigador/a',
                    'Estudiante de grado',
                    'Estudiante de posgrado',
                    'Investigador/a externo/a',
                    'Personal administrativo',
                    'Egresado/a'
                );
                ?>
                <select id="rol_autor" name="rol_autor" required style="width:100%;">
                    <option value="">Seleccionar...</option>
                    <?php foreach ($roles as $rol): ?>
                        <option value="<?php echo esc_attr($rol); ?>" <?php selected($rol_autor, $rol); ?>><?php echo esc_html($rol); ?></option>
                    <?php endforeach; ?>
                </select>
            </td>
        </tr>
        
        <tr>
            <th><label for="facultad">Facultad de Adscripción *</label></th>
            <td>
                <?php 
                $facultad = get_post_meta($post->ID, '_facultad', true);
                $facultades = array(
                    'Facultad de Ciencias Económicas y Empresariales',
                    'Facultad de Ciencias de la Salud',
                    'Facultad de Ingeniería y Tecnología',
                    'Facultad de Humanidades y Ciencias Jurídicas',
                    'Facultad de Ciencias y Tecnología',
                    'Facultad de Educación',
                    'Otra / Interdisciplinaria'
                );
                ?>
                <select id="facultad" name="facultad" required style="width:100%;">
                    <option value="">Seleccionar...</option>
                    <?php foreach ($facultades as $fac): ?>
                        <option value="<?php echo esc_attr($fac); ?>" <?php selected($facultad, $fac); ?>><?php echo esc_html($fac); ?></option>
                    <?php endforeach; ?>
                </select>
            </td>
        </tr>
        <tr>
            <th><label for="categoria">Categoría ASJC</label></th>
            <td><input type="text" id="categoria" name="categoria" value="<?php echo esc_attr($categoria); ?>" style="width:100%;"></td>
        </tr>
        <tr>
            <th><label for="revista">Revista *</label></th>
            <td><input type="text" id="revista" name="revista" value="<?php echo esc_attr($revista); ?>" required style="width:100%;"></td>
        </tr>
        <tr>
            <th><label for="cuartil">Cuartil</label></th>
            <td>
                <select id="cuartil" name="cuartil" style="width:200px;">
                    <option value="">Seleccionar...</option>
                    <option value="Q1" <?php selected($cuartil, 'Q1'); ?>>Q1</option>
                    <option value="Q2" <?php selected($cuartil, 'Q2'); ?>>Q2</option>
                    <option value="Q3" <?php selected($cuartil, 'Q3'); ?>>Q3</option>
                    <option value="Q4" <?php selected($cuartil, 'Q4'); ?>>Q4</option>
                </select>
            </td>
        </tr>
        <tr>
            <th><label for="estado">Estado *</label></th>
            <td>
                <select id="estado" name="estado" required style="width:200px;">
                    <option value="">Seleccionar...</option>
                    <option value="En revisión" <?php selected($estado, 'En revisión'); ?>>En revisión</option>
                    <option value="Aceptado" <?php selected($estado, 'Aceptado'); ?>>Aceptado</option>
                    <option value="Publicado" <?php selected($estado, 'Publicado'); ?>>Publicado</option>
                </select>
            </td>
        </tr>
        <?php if ($pdf): ?>
        <tr>
            <th>PDF Adjunto</th>
            <td><a href="<?php echo esc_url($pdf); ?>" target="_blank">Ver PDF</a></td>
        </tr>
        <?php endif; ?>
    </table>
    <?php
}

// Save meta
add_action('save_post_ufhec_pub', 'ufhec_save_meta');
function ufhec_save_meta($post_id) {
    if (!isset($_POST['ufhec_meta_nonce']) || !wp_verify_nonce($_POST['ufhec_meta_nonce'], 'ufhec_save_meta')) return;
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
    if (!current_user_can('edit_post', $post_id)) return;

    $fields = array('ano', 'doi', 'autores', 'categoria', 'revista', 'cuartil', 'estado', 'pdf', 'rol_autor', 'facultad');
    foreach ($fields as $field) {
        if (isset($_POST[$field])) {
            update_post_meta($post_id, '_' . $field, sanitize_text_field($_POST[$field]));
        }
    }
}

// Custom columns
add_filter('manage_ufhec_pub_posts_columns', 'ufhec_columns');
function ufhec_columns($columns) {
    return array(
        'cb' => $columns['cb'],
        'title' => 'Título',
        'autores' => 'Autores',
        'ano' => 'Año',
        'revista' => 'Revista',
        'cuartil' => 'Cuartil',
        'estado' => 'Estado',
        'date' => 'Fecha'
    );
}

add_action('manage_ufhec_pub_posts_custom_column', 'ufhec_column_content', 10, 2);
function ufhec_column_content($column, $post_id) {
    switch ($column) {
        case 'autores':
            echo esc_html(wp_trim_words(get_post_meta($post_id, '_autores', true), 10));
            break;
        case 'ano':
            echo esc_html(get_post_meta($post_id, '_ano', true));
            break;
        case 'revista':
            echo esc_html(get_post_meta($post_id, '_revista', true));
            break;
        case 'cuartil':
            $q = get_post_meta($post_id, '_cuartil', true);
            if ($q) echo '<span class="ufhec-q">' . esc_html($q) . '</span>';
            break;
        case 'estado':
            echo esc_html(get_post_meta($post_id, '_estado', true));
            break;
    }
}
