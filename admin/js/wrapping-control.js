$(window).resize(function() {
    var wrapped = true;
    var left = $(".item-boxes-container").position().left;

    $(".item-boxes-container").each(function() {
        if ($(this).position().left != left) {
            wrapped = false;
        }
    });

    $.each($(".lws-placeholder"), function() {
        $(this).toggle(!wrapped);
    });
}).resize();