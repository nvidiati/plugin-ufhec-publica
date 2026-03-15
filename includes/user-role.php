<?php
if (!defined('ABSPATH')) exit;

// Create role on activation
register_activation_hook(UFHEC_PUB_DIR . 'ufhec-publicaciones-cientificas.php', 'ufhec_add_pub_role');
function ufhec_add_pub_role() {
    // Remove if exists
    remove_role('ufhec_pub_manager');
    
    // Get capabilities from editor
    $editor = get_role('editor');
    
    // Create new role with editor capabilities
    add_role(
        'ufhec_pub_manager',
        'Gestor de Publicaciones',
        $editor->capabilities
    );
}

// Remove role on deactivation
register_deactivation_hook(UFHEC_PUB_DIR . 'ufhec-publicaciones-cientificas.php', 'ufhec_remove_pub_role');
function ufhec_remove_pub_role() {
    remove_role('ufhec_pub_manager');
}

// Restrict what pub managers can see
add_action('admin_menu', 'ufhec_restrict_manager_menu', 999);
function ufhec_restrict_manager_menu() {
    $user = wp_get_current_user();
    
    if (!in_array('ufhec_pub_manager', (array) $user->roles)) {
        return;
    }

    global $menu, $submenu;

    // Keep only these menus
    $keep = array(
        'edit.php?post_type=ufhec_pub',
        'profile.php'
    );

    // Remove everything else
    foreach ($menu as $key => $item) {
        if (isset($item[2]) && !in_array($item[2], $keep)) {
            unset($menu[$key]);
        }
    }
    
    // Remove all submenus except ufhec_pub
    foreach ($submenu as $parent => $items) {
        if (!in_array($parent, $keep)) {
            unset($submenu[$parent]);
        }
    }
}

// Restrict to only ufhec_pub post type
add_action('pre_get_posts', 'ufhec_restrict_to_own_posts');
function ufhec_restrict_to_own_posts($query) {
    $user = wp_get_current_user();
    
    if (!in_array('ufhec_pub_manager', (array) $user->roles)) {
        return;
    }

    if (is_admin() && $query->is_main_query()) {
        global $pagenow;
        
        if ($pagenow === 'edit.php') {
            // Force only ufhec_pub
            $query->set('post_type', 'ufhec_pub');
        }
    }
}

// Block access to other post types completely
add_action('admin_init', 'ufhec_block_wrong_post_types');
function ufhec_block_wrong_post_types() {
    $user = wp_get_current_user();
    
    if (!in_array('ufhec_pub_manager', (array) $user->roles)) {
        return;
    }

    global $pagenow;

    // Block access to other post type screens
    if ($pagenow === 'edit.php') {
        $post_type = isset($_GET['post_type']) ? $_GET['post_type'] : 'post';
        
        if ($post_type !== 'ufhec_pub') {
            wp_redirect(admin_url('edit.php?post_type=ufhec_pub'));
            exit;
        }
    }

    // Block editing posts from other post types
    if ($pagenow === 'post.php' || $pagenow === 'post-new.php') {
        if (isset($_GET['post'])) {
            $post_type = get_post_type($_GET['post']);
            if ($post_type !== 'ufhec_pub') {
                wp_redirect(admin_url('edit.php?post_type=ufhec_pub'));
                exit;
            }
        }
        
        if (isset($_GET['post_type']) && $_GET['post_type'] !== 'ufhec_pub') {
            wp_redirect(admin_url('post-new.php?post_type=ufhec_pub'));
            exit;
        }
    }
}

// Clean admin bar
add_action('wp_before_admin_bar_render', 'ufhec_clean_admin_bar');
function ufhec_clean_admin_bar() {
    $user = wp_get_current_user();
    
    if (!in_array('ufhec_pub_manager', (array) $user->roles)) {
        return;
    }

    global $wp_admin_bar;
    
    $wp_admin_bar->remove_menu('wp-logo');
    $wp_admin_bar->remove_menu('new-content');
    $wp_admin_bar->remove_menu('comments');
    $wp_admin_bar->remove_menu('updates');
    $wp_admin_bar->remove_menu('customize');
}

// Login redirect
add_filter('login_redirect', 'ufhec_manager_login_redirect', 10, 3);
function ufhec_manager_login_redirect($redirect_to, $request, $user) {
    if (isset($user->roles) && is_array($user->roles) && in_array('ufhec_pub_manager', $user->roles)) {
        return admin_url('edit.php?post_type=ufhec_pub');
    }
    return $redirect_to;
}