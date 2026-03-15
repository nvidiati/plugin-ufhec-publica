<?php
if (!defined('ABSPATH')) exit;

// Handle submission
add_action('wp_ajax_ufhec_submit', 'ufhec_handle_submit');
add_action('wp_ajax_nopriv_ufhec_submit', 'ufhec_handle_submit');

function ufhec_handle_submit() {
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'ufhec_pub_nonce')) {
        wp_send_json_error(array('message' => 'Error de seguridad'));
    }

    $required = array('titulo', 'ano', 'doi', 'autores', 'revista', 'rol_autor', 'facultad');
    foreach ($required as $field) {
        if (empty($_POST[$field])) {
            wp_send_json_error(array('message' => 'Completa todos los campos obligatorios'));
        }
    }

    // Handle PDF
    $pdf_url = '';
    if (!empty($_FILES['pdf_file']['name'])) {
        require_once(ABSPATH . 'wp-admin/includes/file.php');
        require_once(ABSPATH . 'wp-admin/includes/media.php');
        require_once(ABSPATH . 'wp-admin/includes/image.php');

        if ($_FILES['pdf_file']['type'] != 'application/pdf') {
            wp_send_json_error(array('message' => 'Solo archivos PDF'));
        }

        if ($_FILES['pdf_file']['size'] > 10485760) {
            wp_send_json_error(array('message' => 'PDF máximo 10MB'));
        }

        $upload = wp_handle_upload($_FILES['pdf_file'], array('test_form' => false));
        if (isset($upload['error'])) {
            wp_send_json_error(array('message' => $upload['error']));
        }
        $pdf_url = $upload['url'];
    }

    // Create post
    $post_id = wp_insert_post(array(
        'post_title' => sanitize_text_field($_POST['titulo']),
        'post_type' => 'ufhec_pub',
        'post_status' => 'pendiente',
        'meta_input' => array(
            '_ano' => sanitize_text_field($_POST['ano']),
            '_doi' => esc_url_raw($_POST['doi']),
            '_autores' => sanitize_textarea_field($_POST['autores']),
            '_categoria' => sanitize_text_field($_POST['categoria']),
            '_revista' => sanitize_text_field($_POST['revista']),
            '_cuartil' => sanitize_text_field($_POST['cuartil']),
            '_estado' => sanitize_text_field($_POST['estado']),
			'_rol_autor' => sanitize_text_field($_POST['rol_autor']),
            '_facultad' => sanitize_text_field($_POST['facultad']),
            '_pdf' => $pdf_url
        )
    ));

    if (is_wp_error($post_id)) {
        wp_send_json_error(array('message' => 'Error al guardar'));
    }

    // Send email
    ufhec_send_email($post_id, $pdf_url);

    wp_send_json_success(array('message' => 'Publicación enviada correctamente. Pendiente de aprobación.'));
}

function ufhec_send_email($post_id, $pdf_url) {
    $post = get_post($post_id);
    $to = 'vcti@ufhec.edu.do';
    $subject = '📚 Nueva Publicación Científica - ' . $post->post_title;
    
    // Get metadata
    $ano = get_post_meta($post_id, '_ano', true);
    $autores = get_post_meta($post_id, '_autores', true);
    $revista = get_post_meta($post_id, '_revista', true);
    $estado = get_post_meta($post_id, '_estado', true);
    $doi = get_post_meta($post_id, '_doi', true);
    $categoria = get_post_meta($post_id, '_categoria', true);
    $cuartil = get_post_meta($post_id, '_cuartil', true);
    $rol_autor = get_post_meta($post_id, '_rol_autor', true);
    $facultad = get_post_meta($post_id, '_facultad', true);
    
    // Build HTML email
    $message = '
    <!DOCTYPE html>
    <html lang="es">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
    </head>
    <body style="margin: 0; padding: 0; font-family: Arial, sans-serif; background-color: #f4f4f4;">
        <table width="100%" cellpadding="0" cellspacing="0" style="background-color: #f4f4f4; padding: 20px;">
            <tr>
                <td align="center">
                    <table width="600" cellpadding="0" cellspacing="0" style="background-color: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
                        
                        <!-- Header -->
                        <tr>
                            <td style="background-color: #7C0001; padding: 30px; text-align: center;">
                                <h1 style="margin: 0; color: #ffffff; font-size: 24px; font-weight: 600;">
                                    Nueva Publicación Científica
                                </h1>
                                <p style="margin: 10px 0 0 0; color: #ffffff; font-size: 14px; opacity: 0.9;">
                                    UFHEC - Vicerrectoría de Ciencia, Tecnología e Innovación
                                </p>
                            </td>
                        </tr>
                        
                        <!-- Content -->
                        <tr>
                            <td style="padding: 30px;">
                                
                                <p style="margin: 0 0 20px 0; font-size: 16px; color: #333; line-height: 1.5;">
                                    Se ha recibido una nueva publicación científica que requiere revisión y aprobación.
                                </p>
                                
                                <!-- Publication Title -->
                                <div style="background-color: #f8f8f8; border-left: 4px solid #7C0001; padding: 15px; margin-bottom: 20px;">
                                    <h2 style="margin: 0; font-size: 18px; color: #7C0001;">
                                        ' . esc_html($post->post_title) . '
                                    </h2>
                                </div>
                                
                                <!-- Details Table -->
                                <table width="100%" cellpadding="8" cellspacing="0" style="border-collapse: collapse; margin-bottom: 20px;">
                                    <tr>
                                        <td width="35%" style="padding: 12px; border-bottom: 1px solid #e0e0e0; font-weight: 600; color: #7C0001; vertical-align: top;">
                                            📅 Año
                                        </td>
                                        <td style="padding: 12px; border-bottom: 1px solid #e0e0e0; color: #333;">
                                            ' . esc_html($ano) . '
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="padding: 12px; border-bottom: 1px solid #e0e0e0; font-weight: 600; color: #7C0001; vertical-align: top;">
                                            👥 Autores
                                        </td>
                                        <td style="padding: 12px; border-bottom: 1px solid #e0e0e0; color: #333;">
                                            ' . esc_html($autores) . '
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="padding: 12px; border-bottom: 1px solid #e0e0e0; font-weight: 600; color: #7C0001; vertical-align: top;">
                                            👤 Rol del Autor Principal
                                        </td>
                                        <td style="padding: 12px; border-bottom: 1px solid #e0e0e0; color: #333;">
                                            ' . esc_html($rol_autor) . '
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="padding: 12px; border-bottom: 1px solid #e0e0e0; font-weight: 600; color: #7C0001; vertical-align: top;">
                                            🏛️ Facultad
                                        </td>
                                        <td style="padding: 12px; border-bottom: 1px solid #e0e0e0; color: #333;">
                                            ' . esc_html($facultad) . '
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="padding: 12px; border-bottom: 1px solid #e0e0e0; font-weight: 600; color: #7C0001; vertical-align: top;">
                                            📖 Revista
                                        </td>
                                        <td style="padding: 12px; border-bottom: 1px solid #e0e0e0; color: #333;">
                                            ' . esc_html($revista) . '
                                        </td>
                                    </tr>';
    
    if ($categoria) {
        $message .= '
                                    <tr>
                                        <td style="padding: 12px; border-bottom: 1px solid #e0e0e0; font-weight: 600; color: #7C0001; vertical-align: top;">
                                            🔬 Categoría
                                        </td>
                                        <td style="padding: 12px; border-bottom: 1px solid #e0e0e0; color: #333;">
                                            ' . esc_html($categoria) . '
                                        </td>
                                    </tr>';
    }
    
    if ($cuartil) {
        $cuartil_color = array('Q1' => '#28a745', 'Q2' => '#17a2b8', 'Q3' => '#ffc107', 'Q4' => '#dc3545');
        $color = isset($cuartil_color[$cuartil]) ? $cuartil_color[$cuartil] : '#666';
        
        $message .= '
                                    <tr>
                                        <td style="padding: 12px; border-bottom: 1px solid #e0e0e0; font-weight: 600; color: #7C0001; vertical-align: top;">
                                            🏆 Cuartil
                                        </td>
                                        <td style="padding: 12px; border-bottom: 1px solid #e0e0e0; color: #333;">
                                            <span style="background-color: ' . $color . '; color: white; padding: 4px 10px; border-radius: 12px; font-size: 12px; font-weight: 600;">
                                                ' . esc_html($cuartil) . '
                                            </span>
                                        </td>
                                    </tr>';
    }
    
    $message .= '
                                    <tr>
                                        <td style="padding: 12px; border-bottom: 1px solid #e0e0e0; font-weight: 600; color: #7C0001; vertical-align: top;">
                                            📊 Estado
                                        </td>
                                        <td style="padding: 12px; border-bottom: 1px solid #e0e0e0; color: #333;">
                                            ' . esc_html($estado) . '
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="padding: 12px; font-weight: 600; color: #7C0001; vertical-align: top;">
                                            🔗 DOI/URL
                                        </td>
                                        <td style="padding: 12px; color: #333; word-break: break-all;">
                                            <a href="' . esc_url($doi) . '" style="color: #7C0001; text-decoration: none;">
                                                ' . esc_html($doi) . '
                                            </a>
                                        </td>
                                    </tr>
                                </table>';
    
    // PDF attachment notice
    if ($pdf_url) {
        $message .= '
                                <div style="background-color: #fff3cd; border: 1px solid #ffc107; border-radius: 4px; padding: 15px; margin-bottom: 20px;">
                                    <p style="margin: 0; color: #856404; font-size: 14px;">
                                        📎 <strong>PDF Adjunto:</strong> El artículo completo se encuentra adjunto a este correo.
                                    </p>
                                </div>';
    }
    
    $message .= '
                                <!-- Action Buttons -->
                                <table width="100%" cellpadding="0" cellspacing="0" style="margin-top: 30px;">
                                    <tr>
                                        <td align="center">
                                            <a href="' . admin_url('post.php?post=' . $post_id . '&action=edit') . '" 
                                               style="display: inline-block; background-color: #7C0001; color: #ffffff; text-decoration: none; padding: 14px 30px; border-radius: 4px; font-weight: 600; font-size: 16px;">
                                                Revisar y Aprobar Publicación
                                            </a>
                                        </td>
                                    </tr>
                                </table>
                                
                            </td>
                        </tr>
                        
                        <!-- Footer -->
                        <tr>
                            <td style="background-color: #f8f8f8; padding: 20px; text-align: center; border-top: 1px solid #e0e0e0;">
                                <p style="margin: 0; font-size: 12px; color: #666; line-height: 1.6;">
                                    <strong>UFHEC</strong><br>
                                    Universidad Federico Henríquez y Carvajal
                                </p>
                                <p style="margin: 10px 0 0 0; font-size: 11px; color: #999;">
                                    Este es un correo automático, por favor no responder.
                                </p>
                            </td>
                        </tr>
                        
                    </table>
                </td>
            </tr>
        </table>
    </body>
    </html>';
    
    // Headers for HTML email
    $headers = array(
        'Content-Type: text/html; charset=UTF-8',
        'From: UFHEC Publicaciones <noreply@ufhec.edu.do>'
    );
    
    // Attachments
    $attachments = array();
    if ($pdf_url) {
        $upload_dir = wp_upload_dir();
        $pdf_path = str_replace($upload_dir['baseurl'], $upload_dir['basedir'], $pdf_url);
        if (file_exists($pdf_path)) {
            $attachments[] = $pdf_path;
        }
    }
    
    wp_mail($to, $subject, $message, $headers, $attachments);
}

// Render form
function ufhec_render_form() {
    ob_start();
    ?>
    <div id="ufhec-form-wrap">
        <button type="button" id="ufhec-open-btn" class="ufhec-btn-open">
            <span class="dashicons dashicons-plus-alt"></span> Enviar Publicación
        </button>

        <div id="ufhec-modal" class="ufhec-modal">
            <div class="ufhec-modal-content">
                <span class="ufhec-close">&times;</span>
                <h2>Enviar Nueva Publicación Científica</h2>
                
                <form id="ufhec-form" enctype="multipart/form-data">
                    <p>
                        <label>Título de la Publicación *</label>
                        <input type="text" name="titulo" required>
                    </p>

                    <div class="ufhec-row">
                        <p>
                            <label>Año *</label>
                            <input type="number" name="ano" min="1900" max="2100" required>
                        </p>
                        <p>
                            <label>Cuartil</label>
                            <select name="cuartil">
                                <option value="">Seleccionar...</option>
                                <option value="Q1">Q1</option>
                                <option value="Q2">Q2</option>
                                <option value="Q3">Q3</option>
                                <option value="Q4">Q4</option>
                            </select>
                        </p>
                    </div>

                    <p>
                        <label>DOI o Enlace (URL) *</label>
                        <input type="url" name="doi" placeholder="https://doi.org/..." required>
                    </p>

                    <p>
                        <label>Autores (separados por coma) *</label>
                        <textarea name="autores" rows="3" placeholder="Ej.: Ricardo Almonte, Jose Perez" required></textarea>
                    </p>
					
					<p>
                        <label>Rol del Autor/a Principal *</label>
                        <select name="rol_autor" required>
                            <option value="">Seleccionar...</option>
                            <option value="Docente">Docente</option>
                            <option value="Docente–Investigador/a">Docente–Investigador/a</option>
                            <option value="Investigador/a">Investigador/a</option>
                            <option value="Estudiante de grado">Estudiante de grado</option>
                            <option value="Estudiante de posgrado">Estudiante de posgrado</option>
                            <option value="Investigador/a externo/a">Investigador/a externo/a</option>
                            <option value="Personal administrativo">Personal administrativo</option>
                            <option value="Egresado/a">Egresado/a</option>
                        </select>
                    </p>

                    <p>
                        <label>Facultad de Adscripción del Autor/a *</label>
                        <select name="facultad" required>
                            <option value="">Seleccionar...</option>
                            <option value="Facultad de Ciencias Económicas y Empresariales">Facultad de Ciencias Económicas y Empresariales</option>
                            <option value="Facultad de Ciencias de la Salud">Facultad de Ciencias de la Salud</option>
                            <option value="Facultad de Ingeniería y Tecnología">Facultad de Ingeniería y Tecnología</option>
                            <option value="Facultad de Humanidades y Ciencias Jurídicas">Facultad de Humanidades y Ciencias Jurídicas</option>
                            <option value="Facultad de Ciencias y Tecnología">Facultad de Ciencias y Tecnología</option>
                            <option value="Facultad de Educación">Facultad de Educación</option>
                            <option value="Otra / Interdisciplinaria">Otra / Interdisciplinaria</option>
                        </select>
                    </p>

                    <p>
                        <label>Categoría / Clasificación ASJC (Opcional)</label>
                        <input type="text" name="categoria">
                    </p>

                    <p>
                        <label>Revista o Fuente *</label>
                        <input type="text" name="revista" required>
                    </p>

                    <p>
                        <label>Estado </label>
                        <select name="estado" >
                            <option value="">Seleccionar...</option> 
                            <option value="En revisión">En revisión</option>
                            <option value="Aceptado">Aceptado</option>
                            <option value="Publicado">Publicado</option>
                        </select>
                    </p>

                    <p>
                        <label>Adjuntar PDF (Opcional)</label>
                        <input type="file" name="pdf_file" accept=".pdf">
                        <small>Máximo 10MB.</small>
                    </p>

                    <div class="ufhec-actions">
                        <button type="submit" class="ufhec-btn-submit">Enviar</button>
                        <button type="button" class="ufhec-btn-cancel">Cancelar</button>
                    </div>

                    <div id="ufhec-msg"></div>
                </form>
            </div>
        </div>
    </div>
    <?php
    return ob_get_clean();
}
