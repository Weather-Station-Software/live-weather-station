jQuery(document).ready( function($) {
    var welcomePanel = $('#welcome-panel'),
        welcomePanelHide = $('#lws_welcome_panel-hide'),
        updateWelcomePanel;


    updateWelcomePanel = function(visible) {
        $.post( ajaxurl, {
            action: 'update_lws_welcome_panel',
            visible: visible,
            lwswelcomepanelnonce: $('#lwswelcomepanelnonce').val()
        });
    };

    if ( welcomePanel.hasClass('hidden') && welcomePanelHide.prop('checked') ) {
        welcomePanel.removeClass('hidden');
    }

    $('.welcome-panel-close, .welcome-panel-dismiss a', welcomePanel).click( function(e) {
        e.preventDefault();
        welcomePanel.addClass('hidden');
        updateWelcomePanel( 0 );
        $('#lws_welcome_panel-hide').prop('checked', false);
    });

    welcomePanelHide.click( function() {
        welcomePanel.toggleClass('hidden', ! this.checked );
        updateWelcomePanel( this.checked ? 1 : 0 );
    });

    $('#whatsnew').on('click', '.notice-dismiss', function(event){
        $.post( ajaxurl, {
            action: 'hide_lws_whatsnew',
            lwswhatsnewnonce: $('#lwswhatsnewnonce').val()
        });
    });

    $('#link-sync').click( function() {
        $('.button-primary').removeClass('button-primary').addClass('button-disabled');
        $('.button').click(function() { return false; });
        $('#span-sync').show();
    });

    $('#add-netatmo .button').click( function() {
        $('.button').removeClass('button-primary').addClass('button-disabled');
        $('.button').click(function() { return false; });
        $('#span-sync').show();
    });

    $('#add-netatmohc .button').click( function() {
        $('.button').removeClass('button-primary').addClass('button-disabled');
        $('.button').click(function() { return false; });
        $('#span-sync').show();
    });

    $('#delete-station').click( function() {
        $('.button').removeClass('button-primary').addClass('button-disabled');
        $('.button').click(function() { return false; });
        $('#span-sync').show();
    });

    $('#manage-station').click( function() {
        $('.button').removeClass('button-primary').addClass('button-disabled');
        $('.button').click(function() { return false; });
        $('#span-sync').show();
    });

    $('#add-edit-loc').click( function() {
        var form_data=$('#add-edit-loc-form').serializeArray();
        var error_free=true;
        for (var input in form_data){
            if (form_data[input]['name'] == 'station_name') {
                if (form_data[input]['value'] == '') {
                    error_free=false;
                }
            }
            if (form_data[input]['name'] == 'loc_city') {
                if (form_data[input]['value'] == '') {
                    error_free=false;
                }
            }
            if (form_data[input]['name'] == 'loc_altitude') {
                if (form_data[input]['value'] == '') {
                    error_free=false;
                }
            }
        }
        if (error_free) {
            $('.button').removeClass('button-primary').addClass('button-disabled');
            $('.button').click(function() { return false; });
            $('#span-sync').show();
        }
    });

    $('#add-edit-wug').click( function() {
        var form_data=$('#add-edit-wug-form').serializeArray();
        var error_free=true;
        for (var input in form_data){
            if (form_data[input]['name'] == 'service_id') {
                if (form_data[input]['value'] == '') {
                    error_free=false;
                }
            }
        }
        if (error_free) {
            $('.button').removeClass('button-primary').addClass('button-disabled');
            $('.button').click(function() { return false; });
            $('#span-sync').show();
        }
    });

    $('#add-edit-raw').click( function() {
        var form_data=$('#add-edit-raw-form').serializeArray();
        var error_free=true;
        for (var input in form_data){
            if (form_data[input]['name'] == 'station_name') {
                if (form_data[input]['value'] == '') {
                    error_free=false;
                }
            }
            if (form_data[input]['name'] == 'loc_city') {
                if (form_data[input]['value'] == '') {
                    error_free=false;
                }
            }
            if (form_data[input]['name'] == 'loc_altitude') {
                if (form_data[input]['value'] == '') {
                    error_free=false;
                }
            }
            if (form_data[input]['name'] == 'service_id') {
                if (form_data[input]['value'] == '') {
                    error_free=false;
                }
            }
        }
        if (error_free) {
            $('.button').removeClass('button-primary').addClass('button-disabled');
            $('.button').click(function() { return false; });
            $('#span-sync').show();
        }
    });

    $('#add-edit-wflw').click( function() {
        var form_data=$('#add-edit-wflw-form').serializeArray();
        var error_free=true;
        for (var input in form_data){
            if (form_data[input]['name'] == 'loc_city') {
                if (form_data[input]['value'] == '') {
                    error_free=false;
                }
            }
            if (form_data[input]['name'] == 'service_id') {
                if (form_data[input]['value'] == '') {
                    error_free=false;
                }
            }
        }
        if (error_free) {
            $('.button').removeClass('button-primary').addClass('button-disabled');
            $('.button').click(function() { return false; });
            $('#span-sync').show();
        }
    });

    $('#add-edit-piou').click( function() {
        var form_data=$('#add-edit-piou-form').serializeArray();
        var error_free=true;
        for (var input in form_data){
            if (form_data[input]['name'] == 'loc_city') {
                if (form_data[input]['value'] == '') {
                    error_free=false;
                }
            }
            if (form_data[input]['name'] == 'service_id') {
                if (form_data[input]['value'] == '') {
                    error_free=false;
                }
            }
            if (form_data[input]['name'] == 'loc_altitude') {
                if (form_data[input]['value'] == '') {
                    error_free=false;
                }
            }
        }
        if (error_free) {
            $('.button').removeClass('button-primary').addClass('button-disabled');
            $('.button').click(function() { return false; });
            $('#span-sync').show();
        }
    });

    $('#add-edit-real').click( function() {
        var form_data=$('#add-edit-real-form').serializeArray();
        var error_free=true;
        for (var input in form_data){
            if (form_data[input]['name'] == 'station_name') {
                if (form_data[input]['value'] == '') {
                    error_free=false;
                }
            }
            if (form_data[input]['name'] == 'loc_city') {
                if (form_data[input]['value'] == '') {
                    error_free=false;
                }
            }
            if (form_data[input]['name'] == 'loc_altitude') {
                if (form_data[input]['value'] == '') {
                    error_free=false;
                }
            }
            if (form_data[input]['name'] == 'service_id') {
                if (form_data[input]['value'] == '') {
                    error_free=false;
                }
            }
            if (form_data[input]['name'] == 'loc_latitude') {
                if (form_data[input]['value'] == '') {
                    error_free=false;
                }
            }
            if (form_data[input]['name'] == 'loc_longitude') {
                if (form_data[input]['value'] == '') {
                    error_free=false;
                }
            }
        }
        if (error_free) {
            $('.button').removeClass('button-primary').addClass('button-disabled');
            $('.button').click(function() { return false; });
            $('#span-sync').show();
        }
    });

    $('#add-edit-txt').click( function() {
        var form_data=$('#add-edit-txt-form').serializeArray();
        var error_free=true;
        for (var input in form_data){
            if (form_data[input]['name'] == 'station_name') {
                if (form_data[input]['value'] == '') {
                    error_free=false;
                }
            }
            if (form_data[input]['name'] == 'loc_city') {
                if (form_data[input]['value'] == '') {
                    error_free=false;
                }
            }
            if (form_data[input]['name'] == 'loc_altitude') {
                if (form_data[input]['value'] == '') {
                    error_free=false;
                }
            }
            if (form_data[input]['name'] == 'service_id') {
                if (form_data[input]['value'] == '') {
                    error_free=false;
                }
            }
            if (form_data[input]['name'] == 'loc_latitude') {
                if (form_data[input]['value'] == '') {
                    error_free=false;
                }
            }
            if (form_data[input]['name'] == 'loc_longitude') {
                if (form_data[input]['value'] == '') {
                    error_free=false;
                }
            }
        }
        if (error_free) {
            $('.button').removeClass('button-primary').addClass('button-disabled');
            $('.button').click(function() { return false; });
            $('#span-sync').show();
        }
    });

    $('#partial-translation').click( function() {
        $('.button').removeClass('button-primary').addClass('button-disabled');
        $('.button').click(function() { return false; });
        $('#span-sync').show();
    });

    $('#owm-connect').click( function() {
        $('.button').removeClass('button-primary').addClass('button-disabled');
        $('.button').click(function() { return false; });
        $('#owm-span-sync').show();
    });

    $('#owm-disconnect').click( function() {
        if (lws_owm_confirmation) {
            $('.button').removeClass('button-primary').addClass('button-disabled');
            $('.button').click(function() { return false; });
            $('#owm-span-sync').show();
        }
    });

    $('#wug-connect').click( function() {
        $('.button').removeClass('button-primary').addClass('button-disabled');
        $('.button').click(function() { return false; });
        $('#wug-span-sync').show();
    });

    $('#wug-disconnect').click( function() {
        if (lws_wug_confirmation) {
            $('.button').removeClass('button-primary').addClass('button-disabled');
            $('.button').click(function() { return false; });
            $('#wug-span-sync').show();
        }
    });

    $('#netatmo-connect').click( function() {
        $('.button').removeClass('button-primary').addClass('button-disabled');
        $('.button').click(function() { return false; });
        $('#netatmo-span-sync').show();
    });

    $('#netatmo-disconnect').click( function() {
        if (lws_netatmo_confirmation) {
            $('.button').removeClass('button-primary').addClass('button-disabled');
            $('.button').click(function() { return false; });
            $('#netatmo-span-sync').show();
        }
    });

    $('#netatmohc-connect').click( function() {
        $('.button').removeClass('button-primary').addClass('button-disabled');
        $('.button').click(function() { return false; });
        $('#netatmohc-span-sync').show();
    });

    $('#netatmohc-disconnect').click( function() {
        if (lws_netatmohc_confirmation) {
            $('.button').removeClass('button-primary').addClass('button-disabled');
            $('.button').click(function() { return false; });
            $('#netatmohc-span-sync').show();
        }
    });

    $('#wow-unshare').click( function() {
        if (lws_wow_confirmation) {
            $('.button').removeClass('button-primary').addClass('button-disabled');
            $('.button').click(function() { return false; });
            $('#wow-span-sync').show();
        }
    });

    $('#pws-unshare').click( function() {
        if (lws_pws_confirmation) {
            $('.button').removeClass('button-primary').addClass('button-disabled');
            $('.button').click(function() { return false; });
            $('#pws-span-sync').show();
        }
    });

    $('#owm-unshare').click( function() {
        if (lws_owm_confirmation) {
            $('.button').removeClass('button-primary').addClass('button-disabled');
            $('.button').click(function() { return false; });
            $('#owm-span-sync').show();
        }
    });

    $('#wug-unshare').click( function() {
        if (lws_wow_confirmation) {
            $('.button').removeClass('button-primary').addClass('button-disabled');
            $('.button').click(function() { return false; });
            $('#wug-span-sync').show();
        }
    });

    $('#wow-share').click( function() {
        var form_data=$('#wow-share-form').serializeArray();
        var error_free=true;
        for (var input in form_data){
            if (form_data[input]['name'] == 'user') {
                if (form_data[input]['value'] == '') {
                    error_free=false;
                }
            }
            if (form_data[input]['name'] == 'password') {
                if (form_data[input]['value'] == '') {
                    error_free=false;
                }
            }
        }
        if (error_free) {
            $('.button').removeClass('button-primary').addClass('button-disabled');
            $('.button').click(function() { return false; });
            $('#wow-span-sync').show();
        }
    });

    $('#pws-share').click( function() {
        var form_data=$('#pws-share-form').serializeArray();
        var error_free=true;
        for (var input in form_data){
            if (form_data[input]['name'] == 'user') {
                if (form_data[input]['value'] == '') {
                    error_free=false;
                }
            }
            if (form_data[input]['name'] == 'password') {
                if (form_data[input]['value'] == '') {
                    error_free=false;
                }
            }
        }
        if (error_free) {
            $('.button').removeClass('button-primary').addClass('button-disabled');
            $('.button').click(function() { return false; });
            $('#pws-span-sync').show();
        }
    });

    $('#owm-share').click( function() {
        var form_data=$('#owm-share-form').serializeArray();
        var error_free=true;
        for (var input in form_data){
            if (form_data[input]['name'] == 'user') {
                if (form_data[input]['value'] == '') {
                    error_free=false;
                }
            }
            if (form_data[input]['name'] == 'password') {
                if (form_data[input]['value'] == '') {
                    error_free=false;
                }
            }
        }
        if (error_free) {
            $('.button').removeClass('button-primary').addClass('button-disabled');
            $('.button').click(function() { return false; });
            $('#owm-span-sync').show();
        }
    });

    $('#wug-share').click( function() {
        var form_data=$('#wug-share-form').serializeArray();
        var error_free=true;
        for (var input in form_data){
            if (form_data[input]['name'] == 'user') {
                if (form_data[input]['value'] == '') {
                    error_free=false;
                }
            }
            if (form_data[input]['name'] == 'password') {
                if (form_data[input]['value'] == '') {
                    error_free=false;
                }
            }
        }
        if (error_free) {
            $('.button').removeClass('button-primary').addClass('button-disabled');
            $('.button').click(function() { return false; });
            $('#wug-span-sync').show();
        }
    });

} );