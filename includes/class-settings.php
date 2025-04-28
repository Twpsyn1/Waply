<?php

class Waply_Settings {
    public function __construct() {
        add_action('admin_enqueue_scripts', array($this, 'enqueue_button_settings_assets'));
    
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_init', array($this, 'register_settings'));
        add_action('admin_head', array($this, 'inject_admin_dark_mode_css'));
        add_action('admin_notices', array($this, 'dashboard_dark_mode_toggle_notice'));
        add_action('init', array($this, 'handle_dark_mode_toggle_post'));
        // Register settings, enqueue admin scripts, etc.
        add_action('wp_ajax_waply_toggle_dark_mode', array($this, 'ajax_toggle_dark_mode'));
    }


    public function ajax_toggle_dark_mode() {
        check_ajax_referer('waply_dark_mode_ajax', 'nonce');
        $mode = isset($_POST['mode']) && $_POST['mode'] === 'dark' ? 'dark' : 'light';
        $opts = get_option('waply_design', []);
        $opts['mode'] = $mode;
        update_option('waply_design', $opts);
        wp_send_json_success(['mode' => $mode]);
    }

    public function inject_admin_dark_mode_css() {
        $opts = get_option('waply_design', []);
        $mode = $opts['mode'] ?? 'light';
        if ($mode === 'dark') {
            echo '<style>body.wp-admin { background: #23272f !important; color: #f2f2f2 !important; }'
                . '#wpcontent, #wpbody, .wrap { background: #23272f !important; color: #f2f2f2 !important; }'
                . '</style>';
        }
    }

    public function add_admin_menu() {
        $dashicon = 'dashicons-admin-generic';
        add_menu_page(
            __('Waply Settings', 'waply'),
            __('Waply', 'waply'),
            'manage_options',
            'waply-settings',
            array($this, 'render_settings_page'),
            WAPLY_PLUGIN_URL . 'assets/img/whats-app-bubble-dashboard.png',
            25
        );
        // Submenu items with icons (icons via CSS)

        add_submenu_page('waply-settings', __('Button Settings', 'waply'), '<span class="waply-menu-icon waply-menu-buttons"></span>' . __('Button Settings', 'waply'), 'manage_options', 'waply-button-settings', array($this, 'render_button_settings'));

        add_submenu_page('waply-settings', __('Settings', 'waply'), '<span class="waply-menu-icon waply-menu-settings"></span>' . __('Settings', 'waply'), 'manage_options', 'waply-settings', array($this, 'render_settings_page'));
        add_submenu_page('waply-settings', __('Pro Version', 'waply'), '<span class="waply-menu-icon waply-menu-pro"></span>' . __('Pro Version', 'waply'), 'manage_options', 'waply-pro', array($this, 'render_pro_page'));
    }


    public function enqueue_button_settings_assets($hook) {
        if (isset($_GET['page']) && $_GET['page'] === 'waply-button-settings') {
            wp_enqueue_style('waply-admin-css', WAPLY_PLUGIN_URL . 'assets/css/waply-admin.css', [], '0.1.0');
            wp_enqueue_script('waply-admin-preview', WAPLY_PLUGIN_URL . 'assets/js/waply-admin-preview.js', ['jquery'], '0.1.0', true);
        }
    }

    public function render_button_settings() {
        $opts = get_option('waply_design', []);
        $btn_styles = array(
            'default' => __('Default Green', 'waply'),
            'rounded' => __('Rounded', 'waply'),
            'square' => __('Square', 'waply'),
            'circle' => __('Circle', 'waply'),
            'outline' => __('Outline', 'waply'),
        );
        $style = $opts['btn_style'] ?? 'default';
        $pos = get_option('waply_position', ['x'=>68,'y'=>68]);
        $x = isset($pos['x']) ? intval($pos['x']) : 68;
        $y = isset($pos['y']) ? intval($pos['y']) : 68;
        ?>
        <div class="wrap">
            <h1><?php esc_html_e('Button Settings', 'waply'); ?></h1>

            <form method="post" action="options.php">
                <?php settings_fields('waply_settings_group'); ?>
                <table class="form-table">
                    <tr>
                        <th scope="row"><?php _e('Button Style', 'waply'); ?></th>
                        <td>
                            <?php foreach ($btn_styles as $val => $label): ?>
                                <label style="margin-right:16px;">
                                    <input type="radio" name="waply_design[btn_style]" value="<?php echo esc_attr($val); ?>" <?php checked($style, $val); ?> />
                                    <?php echo esc_html($label); ?>
                                </label>
                            <?php endforeach; ?>
                            <!-- Top Preview Button -->
                            <div id="waply-style-preview-top" style="margin:12px 0 24px 0;text-align:center;">
    <button type="button" id="waply-preview-btn-top" class="waply-btn waply-btn-style-<?php echo esc_attr($style); ?>" style="font-size:1.2em;padding:12px 32px;">
        <span class="waply-btn-icon"></span> WhatsApp
    </button>
    <div style="color:#a00;font-size:14px;display:none;" id="waply-preview-btn-top-fallback">If you do not see a preview button above, there may be a CSS/JS issue.</div>
</div>
                            <div id="waply-style-preview" style="margin-top:12px;"></div>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php _e('Button Position', 'waply'); ?></th>
                        <td>
                            <label><?php _e('Horizontal', 'waply'); ?> (Left ⇒ Right):
                                <input type="range" min="0" max="100" name="waply_position[x]" value="<?php echo esc_attr($x); ?>" id="waply-pos-x" oninput="waplyPreviewMove()">
                            </label>
                            <br>
                            <label><?php _e('Vertical', 'waply'); ?> (Top ⇓ Bottom):
                                <input type="range" min="0" max="100" name="waply_position[y]" value="<?php echo esc_attr($y); ?>" id="waply-pos-y" oninput="waplyPreviewMove()">
                            </label>
                            <div id="waply-pos-preview-wrap" style="position:relative;width:220px;height:220px;background:#f6f6f6;border:1px solid #ddd;margin:16px 0;">
                                <!-- Bottom Preview Button -->
                                <div id="waply-pos-preview-btn" class="waply-btn waply-btn-style-<?php echo esc_attr($style); ?>" style="position:absolute;bottom:32px;right:32px;transition:all .2s;">
    <span class="waply-btn-icon"></span> WhatsApp
    <div style="color:#a00;font-size:14px;display:none;" id="waply-preview-btn-bottom-fallback">If you do not see a preview button here, there may be a CSS/JS issue.</div>
</div>
                            </div>
                        </td>
                    </tr>
                </table>
                <?php submit_button(); ?>
            </form>
            <script src="<?php echo WAPLY_PLUGIN_URL . 'assets/js/waply-admin-preview.js'; ?>"></script>
            <script>if(typeof waplyPreviewMove==='undefined'){function waplyPreviewMove(){var x=document.getElementById('waply-pos-x').value,y=document.getElementById('waply-pos-y').value,btn=document.getElementById('waply-pos-preview-btn');if(btn){btn.style.left=x+"%";btn.style.top=y+"%";btn.style.right='';btn.style.bottom='';btn.style.transform='translate(-'+x+'%,-'+y+'%)';}}document.getElementById('waply-pos-x').addEventListener('input',waplyPreviewMove);document.getElementById('waply-pos-y').addEventListener('input',waplyPreviewMove);waplyPreviewMove();}</script>
        </div>
        <?php
    }

    // Handle POST for dark mode toggle on any admin page
    public function handle_dark_mode_toggle_post() {
        if (isset($_POST['waply_toggle_mode_dashboard'])) {
            check_admin_referer('waply_dark_mode_action_dashboard', 'waply_dark_mode_nonce_dashboard');
            $opts = get_option('waply_design', []);
            $mode = $opts['mode'] ?? 'light';
            $mode = ($mode === 'dark') ? 'light' : 'dark';
            $opts['mode'] = $mode;
            update_option('waply_design', $opts);
            // Redirect to the same page to avoid resubmission
            if (!headers_sent()) {
                wp_redirect($_SERVER['REQUEST_URI']);
                exit;
            }
        }
    }

    public function render_dark_mode() {
        $opts = get_option('waply_design', []);
        $mode = $opts['mode'] ?? 'light';
        if (isset($_POST['waply_toggle_mode'])) {
            check_admin_referer('waply_dark_mode_action', 'waply_dark_mode_nonce');
            $mode = ($mode === 'dark') ? 'light' : 'dark';
            $opts['mode'] = $mode;
            update_option('waply_design', $opts);
            // Ensure no output before redirect
            if (!headers_sent()) {
                wp_redirect(admin_url('admin.php?page=waply-dark-mode'));
                exit;
            }
            return;
        }
        $opts = get_option('waply_design', []);
        $mode = $opts['mode'] ?? 'light';
        ?>
        <div class="wrap">
            <h1><?php esc_html_e('Dark Mode', 'waply'); ?></h1>
            <form method="post">
                <?php wp_nonce_field('waply_dark_mode_action', 'waply_dark_mode_nonce'); ?>

                <button type="submit" name="waply_toggle_mode" class="button button-primary">
                    <?php echo ($mode === 'dark') ? esc_html__('Switch to Light Mode', 'waply') : esc_html__('Switch to Dark Mode', 'waply'); ?>
                </button>
            </form>
        </div>
        <?php
    }

    // Add dark/light mode toggle to the Dashboard (and all admin pages via admin_notices)
    public function dashboard_dark_mode_toggle_notice() {
        // Only show to users who can manage options
        if (!current_user_can('manage_options')) return;

        $opts = get_option('waply_design', []);
        $mode = $opts['mode'] ?? 'light';

        echo '<div style="margin:16px 0;">'
            . '<form method="post" style="display:inline;">'
            . wp_nonce_field('waply_dark_mode_action_dashboard', 'waply_dark_mode_nonce_dashboard', true, false)
            . '<button type="submit" name="waply_toggle_mode_dashboard" class="button" style="margin-right:8px;">'
            . (($mode === 'dark') ? esc_html__('Switch to Light Mode', 'waply') : esc_html__('Switch to Dark Mode', 'waply'))
            . '</button>'
    
            . '</form>'
            . '</div>';
    }

    public function render_settings_page() {
        ?>
        <div class="wrap">
            <h1><?php esc_html_e('Waply Settings', 'waply'); ?></h1>
            <form method="post" action="options.php">
                <?php
                settings_fields('waply_settings_group');
                do_settings_sections('waply-settings');
                submit_button();
                ?>
            </form>
        </div>
        <?php
    }

    public function register_settings() {
        register_setting('waply_settings_group', 'waply_design', array(
            'type' => 'array',
            'sanitize_callback' => array($this, 'sanitize_design_options'),
            'default' => array(
                'mode' => 'light',
                'color' => '#25d366',
                'font' => 'inherit',
            ),
        ));

        add_settings_section('waply_text_section', __('Popup Text', 'waply'), function() {
            echo '<p>' . esc_html__('Set the default text shown in the WhatsApp chat popup. You can override this per account.', 'waply') . '</p>';
        }, 'waply-settings');

        add_settings_field('waply_popup_text', __('Default Popup Text', 'waply'), function() {
            $txt = get_option('waply_popup_text', 'Hi! How can we help you?');
            echo '<input type="text" name="waply_popup_text" value="' . esc_attr($txt) . '" class="regular-text" style="width:340px;" maxlength="120">';
        }, 'waply-settings', 'waply_text_section');

        register_setting('waply_settings_group', 'waply_popup_text', array('type'=>'string','sanitize_callback'=>'sanitize_text_field','default'=>'Hi! How can we help you?'));

        add_settings_section('waply_design_section', __('Design Options', 'waply'), function() {
            echo '<p>' . esc_html__('Customize the appearance of WhatsApp buttons and popups.', 'waply') . '</p>';
        }, 'waply-settings');

        // Position Control Section
        add_settings_section('waply_position_section', __('Button Position', 'waply'), function() {
            echo '<p>' . esc_html__('Drag the sliders to position the WhatsApp button on the screen. Preview updates live.', 'waply') . '</p>';
        }, 'waply-settings');

        add_settings_field('waply_position', __('Button Placement', 'waply'), function() {
            $opts = get_option('waply_position', ['x'=>68,'y'=>68]);
            $x = isset($opts['x']) ? intval($opts['x']) : 68;
            $y = isset($opts['y']) ? intval($opts['y']) : 68;
            ?>
            <div style="max-width:400px;">
                <label><?php _e('Horizontal', 'waply'); ?> (Left 1D2 Right):
                    <input type="range" min="0" max="100" name="waply_position[x]" value="<?php echo esc_attr($x); ?>" id="waply-pos-x" oninput="waplyPreviewMove()">
                </label>
                <br>
                <label><?php _e('Vertical', 'waply'); ?> (Top 1D3 Bottom):
                    <input type="range" min="0" max="100" name="waply_position[y]" value="<?php echo esc_attr($y); ?>" id="waply-pos-y" oninput="waplyPreviewMove()">
                </label>
                <div id="waply-pos-preview-wrap" style="position:relative;width:220px;height:220px;background:#f6f6f6;border:1px solid #ddd;margin:16px 0;">
                    <div id="waply-pos-preview-btn" class="waply-btn waply-btn-style-default" style="position:absolute;bottom:32px;right:32px;transition:all .2s;">
                        <span class="waply-btn-icon"></span>
                    </div>
                </div>
            </div>
            <script>if(typeof waplyPreviewMove==='undefined'){function waplyPreviewMove(){var x=document.getElementById('waply-pos-x').value,y=document.getElementById('waply-pos-y').value,btn=document.getElementById('waply-pos-preview-btn');if(btn){btn.style.left=x+"%";btn.style.top=y+"%";btn.style.right='';btn.style.bottom='';btn.style.transform='translate(-'+x+'%,-'+y+'%)';}}document.getElementById('waply-pos-x').addEventListener('input',waplyPreviewMove);document.getElementById('waply-pos-y').addEventListener('input',waplyPreviewMove);waplyPreviewMove();}</script>
            <?php
        }, 'waply-settings', 'waply_position_section');

        add_settings_field('waply_mode', __('Dark/Light Mode', 'waply'), function() {
            $opts = get_option('waply_design', []);
            $mode = $opts['mode'] ?? 'light';
            ?>
            <select name="waply_design[mode]">
                <option value="light" <?php selected($mode, 'light'); ?>><?php _e('Light', 'waply'); ?></option>
                <option value="dark" <?php selected($mode, 'dark'); ?>><?php _e('Dark', 'waply'); ?></option>
            </select>
            <?php
        }, 'waply-settings', 'waply_design_section');

        add_settings_field('waply_color', __('Theme Color', 'waply'), function() {
            $opts = get_option('waply_design', []);
            $color = $opts['color'] ?? '#25d366';
            ?>
            <input type="color" name="waply_design[color]" value="<?php echo esc_attr($color); ?>" />
            <?php
        }, 'waply-settings', 'waply_design_section');

        add_settings_field('waply_font', __('Popup Font', 'waply'), function() {
            $opts = get_option('waply_design', []);
            $font = $opts['font'] ?? 'inherit';
            $fonts = array(
                'inherit' => __('Theme Default', 'waply'),
                'Arial, sans-serif' => 'Arial',
                'Helvetica, sans-serif' => 'Helvetica',
                'Georgia, serif' => 'Georgia',
                'Tahoma, sans-serif' => 'Tahoma',
                'Verdana, sans-serif' => 'Verdana',
                'Courier New, monospace' => 'Courier New',
            );
            ?>
            <select name="waply_design[font]">
                <?php foreach ($fonts as $val => $label): ?>
                    <option value="<?php echo esc_attr($val); ?>" <?php selected($font, $val); ?>><?php echo esc_html($label); ?></option>
                <?php endforeach; ?>
            </select>
            <?php
        }, 'waply-settings', 'waply_design_section');
    }

    public function sanitize_design_options($input) {
        $out = [];
        $out['mode'] = in_array($input['mode'], ['light','dark']) ? $input['mode'] : 'light';
        $out['color'] = (isset($input['color']) && preg_match('/^#[0-9a-fA-F]{6}$/', $input['color'])) ? $input['color'] : '#25d366';
        $allowed_fonts = ['inherit','Arial, sans-serif','Helvetica, sans-serif','Georgia, serif','Tahoma, sans-serif','Verdana, sans-serif','Courier New, monospace'];
        $out['font'] = (isset($input['font']) && in_array($input['font'], $allowed_fonts)) ? $input['font'] : 'inherit';
        return $out;
    }
}

