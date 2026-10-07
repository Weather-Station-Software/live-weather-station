jQuery(function($) {
    $(window).resize(function() {
        var containers = $(".item-boxes-container");
        if (containers.length === 0) {
            return;
        }
        var wrapped = true;
        var left = containers.first().position().left;

        containers.each(function() {
            if ($(this).position().left != left) {
                wrapped = false;
            }
        });

        $.each($(".lws-placeholder"), function() {
            $(this).toggle(!wrapped);
        });
    }).resize();
});