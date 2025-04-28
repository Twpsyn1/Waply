<?php
class Waply_Frontend {
    public function __construct() {


        add_action('wp_enqueue_scripts', array($this, 'enqueue_assets'));
        add_action('wp_footer', array($this, 'render_floating_buttons'));
        add_action('wp_head', array($this, 'output_design_css'));
        add_action('wp_footer', array($this, 'output_design_css'));
    }

    public function enqueue_assets() {
        wp_enqueue_style('waply-frontend', WAPLY_PLUGIN_URL . 'assets/css/waply-frontend.css', [], '0.1.0');
        wp_enqueue_script('waply-frontend', WAPLY_PLUGIN_URL . 'assets/js/waply-frontend.js', ['jquery'], '0.1.0', true);
    }

    public function output_design_css() {

        $opts = get_option('waply_design', []);
        $mode = $opts['mode'] ?? 'light';
        if ($mode === 'dark') {
            echo '<style>body { background: #222 !important; }</style>';
        }
        $color = $opts['color'] ?? '#25d366';
        $mode = $opts['mode'] ?? 'light';
        $font = $opts['font'] ?? 'inherit';

        ?>
        <style id="waply-design-vars">
        :root {
            --waply-theme-color: <?php echo esc_attr($color); ?>;
            --waply-popup-font: <?php echo esc_attr($font); ?>;
        }
        body .waply-floating-btns, .waply-popup {
            font-family: var(--waply-popup-font) !important;
        }
        <?php if ($mode === 'dark'): ?>
        body { background: #222 !important; }
        .waply-popup {
            background: #23272f !important;
            color: #f2f2f2 !important;
        }
        .waply-btn, .waply-btn-style-outline {
            background: #23272f !important;
            color: #25d366 !important;
            border-color: #25d366 !important;
        }
        <?php else: ?>
        .waply-btn, .waply-btn-style-default {
            background: var(--waply-theme-color) !important;
            color: #fff !important;
        }
        .waply-btn-style-outline {
            background: #fff !important;
            color: var(--waply-theme-color) !important;
            border: 2px solid var(--waply-theme-color) !important;
        }
        <?php endif; ?>
        </style>
        <?php
    }

    public function render_floating_buttons() {
        $args = array(
            'post_type' => 'waply_account',
            'post_status' => 'publish',
            'numberposts' => -1,
        );
        $accounts = get_posts($args);
        if (!$accounts) return;
        // Get position from options
        $pos = get_option('waply_position', ['x'=>68,'y'=>68]);
        $x = isset($pos['x']) ? intval($pos['x']) : 68;
        $y = isset($pos['y']) ? intval($pos['y']) : 68;
        $btn_style = get_option('waply_design', []);
        $btn_style_class = isset($btn_style['btn_style']) ? 'waply-btn-style-' . esc_attr($btn_style['btn_style']) : 'waply-btn-style-default';
        $style = sprintf('left:%dvw;top:%dvh;transform:translate(-%d%%,-%d%%);', $x, $y, $x, $y);
        echo '<div class="waply-floating-btns" style="position:fixed;'.$style.'">';
        // Floating Action Button (FAB) to open popup
        echo '<div class="waply-fab" tabindex="0" aria-label="Open WhatsApp Chat"><span class="waply-btn-icon"></span></div>';
        // Popup container
        echo '<div class="waply-popup" style="display:none;">';
        // Popup header
        echo '<div class="waply-popup-header">'
            .'<span class="waply-popup-icon"></span>'
            .'<span class="waply-popup-header-title">'.esc_html__('Chat with us on WhatsApp', 'waply').'</span>'
            .'<button class="waply-popup-close" aria-label="Close">&times;</button>'
        .'</div>';
        // Accounts list
        echo '<div class="waply-popup-accounts">';
        foreach ($accounts as $account) {
            $title = get_post_meta($account->ID, '_waply_title', true);
            $phone = get_post_meta($account->ID, '_waply_phone', true);
            $btn_style = get_post_meta($account->ID, '_waply_btn_style', true) ?: 'default';
            $avatar_id = get_post_thumbnail_id($account->ID);
            $avatar = $avatar_id ? wp_get_attachment_image_url($avatar_id, 'thumbnail') : '';
            $display_name = esc_html(get_the_title($account->ID));
            $popup_text = get_post_meta($account->ID, '_waply_popup_text', true);
            if (!$popup_text) {
                $popup_text = get_option('waply_popup_text', esc_html__('Hi! How can we help you?', 'waply'));
            }
            $popup_text = esc_html($popup_text);
            ?>
            <div class="waply-popup-account-card">
                <?php if ($avatar): ?><img src="<?php echo esc_url($avatar); ?>" class="waply-avatar" alt="Avatar"><?php endif; ?>
                <div class="waply-popup-account-details">
                    <div class="waply-popup-account-name"><?php echo $display_name; ?></div>
                    <div class="waply-popup-account-title"><?php echo esc_html($title); ?></div>
                    <div class="waply-popup-text"><?php echo $popup_text; ?></div>
                    <div class="waply-popup-account-btn">
                        <a href="https://wa.me/<?php echo esc_attr($phone); ?>" target="_blank" rel="noopener" class="waply-btn waply-btn-style-default" style="min-width:unset;padding:6px 16px;font-size:1em;">
                            <span class="waply-btn-icon" style="margin-right:6px;"></span><?php esc_html_e('Chat', 'waply'); ?>
                        </a>
                    </div>
                </div>
            </div>
            <?php
        }
        echo '</div>'; // .waply-popup-accounts
        echo '</div>'; // .waply-popup
        echo '</div>'; // .waply-floating-btns
    }
}
