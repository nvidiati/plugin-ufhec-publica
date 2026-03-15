<?php
if (!defined('ABSPATH')) exit;

// Form shortcode
add_shortcode('ufhec_formulario_publicacion', 'ufhec_form_shortcode');
function ufhec_form_shortcode() {
    return ufhec_render_form();
}

// Table shortcode
add_shortcode('ufhec_tabla_publicaciones', 'ufhec_table_shortcode');
function ufhec_table_shortcode() {
    // Get current page
    $paged = (get_query_var('paged')) ? get_query_var('paged') : 1;
    
    $query = new WP_Query(array(
        'post_type' => 'ufhec_pub',
        'post_status' => 'publish',
        'posts_per_page' => 20,
        'paged' => $paged,
        'orderby' => 'meta_value_num',
        'meta_key' => '_ano',
        'order' => 'DESC'
    ));

    if (!$query->have_posts()) {
        return '<p>No hay publicaciones disponibles.</p>';
    }

    // Get all years for filter (separate query)
    $all_query = new WP_Query(array(
        'post_type' => 'ufhec_pub',
        'post_status' => 'publish',
        'posts_per_page' => -1,
        'fields' => 'ids'
    ));
    
    $anos = array();
    if ($all_query->have_posts()) {
        while ($all_query->have_posts()) {
            $all_query->the_post();
            $ano = get_post_meta(get_the_ID(), '_ano', true);
            if ($ano && !in_array($ano, $anos)) $anos[] = $ano;
        }
        rsort($anos);
        wp_reset_postdata();
    }

    ob_start();
    ?>
    <div class="ufhec-table-wrap">
        <div class="ufhec-controls">
            <input type="text" id="ufhec-search" placeholder="Buscar...">
            <select id="ufhec-filter-ano">
                <option value="">Filtrar por Año</option>
                <?php foreach ($anos as $ano) {
                    echo '<option value="' . $ano . '">' . $ano . '</option>';
                } ?>
            </select>
            <select id="ufhec-filter-cuartil">
                <option value="">Filtrar por Cuartil</option>
                <option value="Q1">Q1</option>
                <option value="Q2">Q2</option>
                <option value="Q3">Q3</option>
                <option value="Q4">Q4</option>
            </select>
            <button type="button" id="ufhec-reset">Limpiar</button>
        </div>

        <div class="ufhec-table-container">
            <table class="ufhec-table" id="ufhec-table">
                <thead>
                    <tr>
                        <th class="sortable" data-sort="titulo">Título <span class="arrow"></span></th>
                        <th class="sortable" data-sort="ano">Año <span class="arrow"></span></th>
                        <th>Enlace</th>
                        <th>Autores</th>
                        <th>Categoría</th>
                        <th class="sortable" data-sort="revista">Revista <span class="arrow"></span></th>
                        <th class="sortable" data-sort="cuartil">Cuartil <span class="arrow"></span></th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    while ($query->have_posts()) : $query->the_post();
                        $ano = get_post_meta(get_the_ID(), '_ano', true);
                        $doi = get_post_meta(get_the_ID(), '_doi', true);
                        $autores = get_post_meta(get_the_ID(), '_autores', true);
                        $categoria = get_post_meta(get_the_ID(), '_categoria', true);
                        $revista = get_post_meta(get_the_ID(), '_revista', true);
                        $cuartil = get_post_meta(get_the_ID(), '_cuartil', true);
                    ?>
                    <tr data-ano="<?php echo esc_attr($ano); ?>" data-cuartil="<?php echo esc_attr($cuartil); ?>">
                        <td data-label="Título"><?php the_title(); ?></td>
                        <td data-label="Año"><?php echo esc_html($ano); ?></td>
                        <td data-label="Enlace">
                            <?php if ($doi): ?>
                                <a href="<?php echo esc_url($doi); ?>" target="_blank" rel="noopener">Ver Publicación</a>
                            <?php endif; ?>
                        </td>
                        <td data-label="Autores"><?php echo esc_html($autores); ?></td>
                        <td data-label="Categoría"><?php echo esc_html($categoria); ?></td>
                        <td data-label="Revista"><?php echo esc_html($revista); ?></td>
                        <td data-label="Cuartil">
                            <?php if ($cuartil): ?>
                                <span class="ufhec-badge badge-<?php echo strtolower($cuartil); ?>">
                                    <?php echo esc_html($cuartil); ?>
                                </span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
        
        <?php
        // Pagination
        $total_pages = $query->max_num_pages;
        if ($total_pages > 1) {
            echo '<div class="ufhec-pagination">';
            
            $current_page = max(1, $paged);
            
            // Previous button
            if ($current_page > 1) {
                echo '<a href="' . get_pagenum_link($current_page - 1) . '#Publicaciones" class="ufhec-page-btn ufhec-prev">« Anterior</a>';
            }
            
            // Page numbers
            echo '<div class="ufhec-page-numbers">';
            
            for ($i = 1; $i <= $total_pages; $i++) {
                // Show first, last, current, and 2 pages around current
                if ($i == 1 || $i == $total_pages || ($i >= $current_page - 2 && $i <= $current_page + 2)) {
                    if ($i == $current_page) {
                        echo '<span class="ufhec-page-num active">' . $i . '</span>';
                    } else {
                        echo '<a href="' . get_pagenum_link($i) . '#Publicaciones" class="ufhec-page-num">' . $i . '</a>';
                    }
                } elseif ($i == $current_page - 3 || $i == $current_page + 3) {
                    echo '<span class="ufhec-dots">...</span>';
                }
            }
            
            echo '</div>';
            
            // Next button
            if ($current_page < $total_pages) {
                echo '<a href="' . get_pagenum_link($current_page + 1) . '#Publicaciones" class="ufhec-page-btn ufhec-next">Siguiente »</a>';
            }
            
            echo '</div>';
        }
        wp_reset_postdata();
        ?>
        
        <div class="ufhec-footer">
            <p>Mostrando <?php echo (($paged - 1) * 20 + 1); ?>-<?php echo min($paged * 20, $query->found_posts); ?> de <?php echo $query->found_posts; ?> publicaciones</p>
        </div>
    </div>
    <?php
    return ob_get_clean();
}