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
        $('.button').removeClass('button-primary').addClass('button-disabled');
        $('.button').click(function() { return false; });
        $('#owm-span-sync').show();
    });

    $('#wug-connect').click( function() {
        $('.button').removeClass('button-primary').addClass('button-disabled');
        $('.button').click(function() { return false; });
        $('#wug-span-sync').show();
    });

    $('#wug-disconnect').click( function() {
        $('.button').removeClass('button-primary').addClass('button-disabled');
        $('.button').click(function() { return false; });
        $('#wug-span-sync').show();
    });

    $('#netatmo-connect').click( function() {
        $('.button').removeClass('button-primary').addClass('button-disabled');
        $('.button').click(function() { return false; });
        $('#netatmo-span-sync').show();
    });

    $('#netatmo-disconnect').click( function() {
        $('.button').removeClass('button-primary').addClass('button-disabled');
        $('.button').click(function() { return false; });
        $('#netatmo-span-sync').show();
    });

} );