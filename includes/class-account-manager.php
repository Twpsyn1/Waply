<?php
class Waply_Account_Manager {
    public function __construct() {
        add_action('init', array($this, 'register_account_cpt'));
        add_action('add_meta_boxes', array($this, 'add_account_meta_boxes'));
        add_action('save_post', array($this, 'save_account_meta'), 10, 2);
        add_filter('manage_waply_account_posts_columns', array($this, 'add_custom_columns'));
        add_action('manage_waply_account_posts_custom_column', array($this, 'render_custom_column'), 10, 2);
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_assets'));
        // Quick Edit
        add_filter('post_row_actions', array($this, 'add_quick_edit_link'), 10, 2);
        add_action('quick_edit_custom_box', array($this, 'quick_edit_fields'), 10, 2);
        add_action('save_post', array($this, 'save_quick_edit_fields'), 20, 2);
    }

    public function register_account_cpt() {
        $labels = array(
            'name' => __('Your Accounts', 'waply'),
            'singular_name' => __('Your Account', 'waply'),
            'add_new' => __('Add New', 'waply'),
            'add_new_item' => __('Add New Account', 'waply'),
            'edit_item' => __('Edit Account', 'waply'),
            'new_item' => __('New Account', 'waply'),
            'view_item' => __('View Account', 'waply'),
            'search_items' => __('Search Accounts', 'waply'),
            'not_found' => __('No accounts found', 'waply'),
            'not_found_in_trash' => __('No accounts found in Trash', 'waply'),
            'all_items' => __('Your Accounts', 'waply'),
            'menu_name' => __('Accounts', 'waply'),
        );
        $args = array(
            'labels' => $labels,
            'public' => false,
            'show_ui' => true,
            'show_in_menu' => 'waply-settings', // Show under Waply menu
            'supports' => array('title', 'thumbnail'),
            'menu_position' => 25,
            'capability_type' => 'post',
            'hierarchical' => false,
            'has_archive' => false,
            'menu_icon' => null, // No custom icon
        );
        register_post_type('waply_account', $args);
    }

    public function add_account_meta_boxes() {
        add_meta_box(
            'waply_account_details',
            __('Account Details', 'waply'),
            array($this, 'render_account_meta_box'),
            'waply_account',
            'normal',
            'default'
        );
    }

    public function render_account_meta_box($post) {
        echo '<h2 style="margin-top:0">' . esc_html__('Account Holder Details', 'waply') . '</h2>';
        echo '<p class="description">' . esc_html__('Edit the WhatsApp account holder information below.', 'waply') . '</p>';

        wp_nonce_field('waply_account_meta', 'waply_account_meta_nonce');
        $title = get_post_meta($post->ID, '_waply_title', true);
        $phone = get_post_meta($post->ID, '_waply_phone', true);
        $btn_style = get_post_meta($post->ID, '_waply_btn_style', true);
        $popup_text = get_post_meta($post->ID, '_waply_popup_text', true);
        $avatar_id = get_post_thumbnail_id($post->ID);
        $avatar_url = $avatar_id ? wp_get_attachment_image_url($avatar_id, 'medium') : '';
        $always_online = get_post_meta($post->ID, '_waply_always_online', true);
        $styles = $this->get_button_styles();
        ?>
        <div class="waply-admin-card waply-account-edit-card" style="display:flex;gap:32px;align-items:flex-start;">
          <div style="flex:1 1 0;min-width:240px;">
            <div class="waply-avatar-upload" style="margin-bottom:18px;">
  <label><strong><?php _e('Avatar', 'waply'); ?></strong></label><br>
  <div style="display:flex;align-items:center;gap:16px;">
    <img id="waply-avatar-preview" src="<?php echo esc_url($avatar_url); ?>" class="waply-avatar" style="width:64px;height:64px;border-radius:50%;background:#f4f4f4;object-fit:cover;box-shadow:0 1px 3px rgba(0,0,0,0.08);">
  </div>
</div>

            <p>
              <label for="waply_phone"><strong><?php _e('WhatsApp Number / Group URL', 'waply'); ?></strong></label><br>
              <input type="text" id="waply_phone" name="waply_phone" value="<?php echo esc_attr($phone); ?>" class="regular-text" placeholder="e.g. 15551234567 or https://chat.whatsapp.com/..." />
            </p>
            <p>
              <label for="waply_title"><strong><?php _e('Title / Position', 'waply'); ?></strong></label><br>
              <input type="text" id="waply_title" name="waply_title" value="<?php echo esc_attr($title); ?>" class="regular-text" placeholder="e.g. Support, Sales, Manager" />
            </p>
            <p>
              <label for="waply_popup_text"><strong><?php _e('Predefined Text', 'waply'); ?></strong></label><br>
              <input type="text" id="waply_popup_text" name="waply_popup_text" value="<?php echo esc_attr($popup_text); ?>" class="regular-text" placeholder="e.g. Hi, I need help with..." />
            </p>
            <p style="margin-bottom:0;">
              <label for="waply_always_online" style="display:flex;align-items:center;gap:8px;cursor:pointer;">
                <input type="checkbox" id="waply_always_online" name="waply_always_online" value="1" <?php checked($always_online, '1'); ?> />
                <span><?php _e('Always available online', 'waply'); ?></span>
              </label>
            </p>
          </div>
          <div class="waply-account-sidebar" style="min-width:210px;max-width:230px;background:#f8f8f8;border-radius:10px;padding:18px 12px 12px 12px;box-shadow:0 1px 3px rgba(0,0,0,0.04);">
            <div style="margin-bottom:10px;"><strong><?php _e('Shortcode', 'waply'); ?></strong></div>
            <div class="waply-shortcode-box" style="font-size:15px;padding:7px 10px;word-break:break-all;">
              [dap_button id="<?php echo esc_attr($post->ID); ?>"]
            </div>
          </div>
        </div>
        
        <?php
    }

    public function save_account_meta($post_id, $post) {
        if (!isset($_POST['waply_account_meta_nonce']) || !wp_verify_nonce($_POST['waply_account_meta_nonce'], 'waply_account_meta')) {
            return;
        }
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
        if ($post->post_type !== 'waply_account') return;
        if (!current_user_can('edit_post', $post_id)) return;
        update_post_meta($post_id, '_waply_title', sanitize_text_field($_POST['waply_title'] ?? ''));
        update_post_meta($post_id, '_waply_phone', sanitize_text_field($_POST['waply_phone'] ?? ''));
        update_post_meta($post_id, '_waply_btn_style', sanitize_text_field($_POST['waply_btn_style'] ?? ''));
        update_post_meta($post_id, '_waply_popup_text', sanitize_text_field($_POST['waply_popup_text'] ?? ''));
    }

    // Quick Edit support
    public function add_quick_edit_link($actions, $post) {
        if ($post->post_type === 'waply_account') {
            $actions['inline hide-if-no-js'] = '<a href="#" class="editinline" aria-label="' . esc_attr__('Quick Edit', 'waply') . '">' . esc_html__('Quick Edit', 'waply') . '</a>';
        }
        return $actions;
    }
    public function quick_edit_fields($column_name, $post_type) {
        if ($post_type !== 'waply_account' || $column_name !== 'title') return;
        ?>
        <fieldset class="inline-edit-col-left">
            <div class="inline-edit-col">
                <label>
                    <span class="title"><?php _e('Title/Position', 'waply'); ?></span>
                    <input type="text" name="waply_title" value="" class="waply-qe-title" />
                </label><br>
                <label>
                    <span class="title"><?php _e('Phone', 'waply'); ?></span>
                    <input type="text" name="waply_phone" value="" class="waply-qe-phone" />
                </label><br>
                <label>
                    <span class="title"><?php _e('Popup Text', 'waply'); ?></span>
                    <input type="text" name="waply_popup_text" value="" class="waply-qe-popup-text" />
                </label>
            </div>
        </fieldset>
        <script>
        jQuery(document).on('click', '.editinline', function(){
            var tr = jQuery(this).closest('tr');
            var data = tr.data('inline-edit-post');
            if (data) {
                jQuery('.waply-qe-title').val(data.waply_title||'');
                jQuery('.waply-qe-phone').val(data.waply_phone||'');
                jQuery('.waply-qe-popup-text').val(data.waply_popup_text||'');
            }
        });
        </script>
        <?php
    }
    public function save_quick_edit_fields($post_id, $post) {
        if ($post->post_type !== 'waply_account' || !current_user_can('edit_post', $post_id)) return;
        if (isset($_POST['waply_title'])) update_post_meta($post_id, '_waply_title', sanitize_text_field($_POST['waply_title']));
        if (isset($_POST['waply_phone'])) update_post_meta($post_id, '_waply_phone', sanitize_text_field($_POST['waply_phone']));
        if (isset($_POST['waply_popup_text'])) update_post_meta($post_id, '_waply_popup_text', sanitize_text_field($_POST['waply_popup_text']));
    }

    private function get_button_styles() {
        // These can be expanded or themed later
        return array(
            'default' => __('Default Green', 'waply'),
            'rounded' => __('Rounded', 'waply'),
            'square' => __('Square', 'waply'),
            'circle' => __('Circle', 'waply'),
            'outline' => __('Outline', 'waply'),
        );
    }

    // Admin columns
    // Modern admin columns
    public function add_custom_columns($columns) {
        $new = [];
        $new['cb'] = $columns['cb'];
        $new['title'] = __('Account Name', 'waply');
        $new['waply_avatar'] = __('Avatar', 'waply');
        $new['waply_phone'] = __('Number', 'waply');
        $new['waply_title'] = __('Title', 'waply');
        $new['waply_days'] = __('Active Days', 'waply');
        $new['waply_shortcode'] = __('Shortcode', 'waply');
        return $new;
    }
    public function render_custom_column($column, $post_id) {
        if ($column === 'waply_avatar') {
            $thumb = get_the_post_thumbnail($post_id, [40,40], ['class'=>'waply-avatar']);
            echo $thumb ?: '-';
        } elseif ($column === 'waply_phone') {
            echo esc_html(get_post_meta($post_id, '_waply_phone', true));
        } elseif ($column === 'waply_title') {
            echo esc_html(get_post_meta($post_id, '_waply_title', true));
        } elseif ($column === 'waply_days') {
            echo __('Always online', 'waply'); // Placeholder for now
        } elseif ($column === 'waply_shortcode') {
            echo '<span class="waply-shortcode-box">[dap_button id="' . esc_attr($post_id) . '"]</span>';
        } elseif ($column === 'title') {
            $edit_link = get_edit_post_link($post_id);
            $title = esc_html(get_the_title($post_id));
            echo '<a href="' . esc_url($edit_link) . '" class="waply-name-link">' . $title . '</a>';
        }
    }
    public function enqueue_admin_assets($hook) {
        global $post_type;
        if ($hook === 'post-new.php' || $hook === 'post.php') {
            if ($post_type === 'waply_account') {
                wp_enqueue_style('waply-frontend', WAPLY_PLUGIN_URL . 'assets/css/waply-frontend.css', [], '0.1.0');
                wp_enqueue_script('waply-admin-preview', WAPLY_PLUGIN_URL . 'assets/js/waply-admin-preview.js', ['jquery'], '0.1.0', true);
            }
        }
    }
}
