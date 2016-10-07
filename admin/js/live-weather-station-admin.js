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
        $('.button').removeClass('button-primary').addClass('button-disabled');
        $('.button').click(function() { return false; });
        $('#span-sync').show();
    });

    $('#partial-translation').click( function() {
        $('.button').removeClass('button-primary').addClass('button-disabled');
        $('.button').click(function() { return false; });
        $('#span-sync').show();
    });

} );