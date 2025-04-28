// Waply Admin UI Interactions
jQuery(document).ready(function($){
    // Toggle switch for "Always available online"
    $('.waply-toggle input[type="checkbox"]').on('change', function(){
        var slider = $(this).siblings('.waply-toggle-slider');
        if($(this).is(':checked')) {
            slider.css('left','20px');
        } else {
            slider.css('left','2px');
        }
    });

    // Make account name row clickable
    $('.waply-accounts-table .waply-name-link').on('click', function(e){
        e.preventDefault();
        window.location.href = $(this).attr('href');
    });

    // Avatar preview on upload
    $('#waply_avatar').on('change', function(e){
        if(this.files && this.files[0]){
            var reader = new FileReader();
            reader.onload = function(ev){
                $('.waply-admin-avatar').attr('src', ev.target.result);
            };
            reader.readAsDataURL(this.files[0]);
        }
    });

    // Set initial dark mode class on page load
    var isDarkInit = $('#waply-darkmode-toggle').is(':checked');
    if(isDarkInit) {
        $('body').addClass('waply-dark-active');
    } else {
        $('body').removeClass('waply-dark-active');
    }

    // Ensure ajaxurl is defined
    if (typeof ajaxurl === 'undefined') {
        window.ajaxurl = '/wp-admin/admin-ajax.php';
    }

    // AJAX dark mode toggle
    var $toggle = $('#waply-darkmode-toggle');
    if($toggle.length === 0) {
        alert('Dark mode toggle not found!');
    }
    $toggle.on('change', function(){
        console.log('Dark mode toggle changed!');
        var isDark = $(this).is(':checked');
        var mode = isDark ? 'dark' : 'light';
        var nonce = $('#waply_dark_mode_ajax_nonce').val();
        console.log('Sending AJAX: mode=', mode, 'nonce=', nonce);
        $.post(ajaxurl, {
            action: 'waply_toggle_dark_mode',
            mode: mode,
            nonce: nonce
        }, function(response) {
            console.log('AJAX response:', response);
            if(response.success) {
                if(mode === 'dark') {
                    $('body').addClass('waply-dark-active');
                } else {
                    $('body').removeClass('waply-dark-active');
                }
            } else {
                alert('Failed to update dark mode.');
            }
        });
    });
});