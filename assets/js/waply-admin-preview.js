jQuery(document).ready(function($){
    var styles = {
        'default': 'waply-btn-style-default',
        'rounded': 'waply-btn-style-rounded',
        'square': 'waply-btn-style-square',
        'circle': 'waply-btn-style-circle',
        'outline': 'waply-btn-style-outline'
    };

    function updatePreviewButtons() {
        console.log('updatePreviewButtons fired');
        var style = $('input[name="waply_design[btn_style]"]:checked').val();
        var cls = styles[style] || styles['default'];
        // Map style to icon image
        var iconMap = {
            'default': '<img src="/wp-content/plugins/waply/assets/img/whats-app-buttons-1.png" class="waply-btn-icon" alt="Default" />',
            'rounded': '<img src="/wp-content/plugins/waply/assets/img/whatsapp-icon-rounded.svg" class="waply-btn-icon" alt="Rounded" />',
            'square': '<img src="/wp-content/plugins/waply/assets/img/whatsapp-icon-square.svg" class="waply-btn-icon" alt="Square" />',
            'circle': '<img src="/wp-content/plugins/waply/assets/img/whatsapp-icon-circle.svg" class="waply-btn-icon" alt="Circle" />',
            'outline': '<img src="/wp-content/plugins/waply/assets/img/whats-app-bubble-dashboard.png" class="waply-btn-icon" alt="Outline" />'
        };
        var iconHtml = iconMap[style] || iconMap['default'];
        $('#waply-preview-btn-top').removeClass().addClass('waply-btn ' + cls).html(iconHtml + ' WhatsApp');
        $('#waply-pos-preview-btn').removeClass().addClass('waply-btn ' + cls).html(iconHtml + ' WhatsApp');

        // Debug panel
        var debugHtml = '<div style="background:#f8f8f8;border:1px solid #ccc;margin:12px 0;padding:8px;font-size:13px;">'+
            '<b>Waply Debug Info</b><br>'+
            'Selected style: <code>'+style+'</code><br>'+
            'Class: <code>'+cls+'</code><br>'+
            'Image HTML:<br><textarea style="width:100%;height:80px;">'+iconHtml.replace(/</g,'&lt;')+'</textarea>'+
            '</div>';
        if ($('#waply-debug-panel').length) {
            $('#waply-debug-panel').replaceWith('<div id="waply-debug-panel">'+debugHtml+'</div>');
        } else {
            $('#waply-style-preview-top').after('<div id="waply-debug-panel">'+debugHtml+'</div>');
        }
    }

    function updatePreviewPosition() {
        var x = $('#waply-pos-x').val();
        var y = $('#waply-pos-y').val();
        var $btn = $('#waply-pos-preview-btn');
        $btn.css({
            left: x + '%',
            top: y + '%',
            right: '',
            bottom: '',
            transform: 'translate(-' + x + '%,-' + y + '%)'
        });
    }

    // Initial update
    updatePreviewButtons();
    updatePreviewPosition();
    // Event listeners
    $('input[name="waply_design[btn_style]"]').on('change', function() {
        console.log('Direct change event fired');
        updatePreviewButtons();
    });
    $(document).on('change', 'input[name="waply_design[btn_style]"]', updatePreviewButtons);
    $('#waply-pos-x, #waply-pos-y').on('input change', updatePreviewPosition);
});
