jQuery(document).ready(function ($) {
    $(".main-phone").hover(
            function () {
                $('.phones').slideDown('medium');
            }
    );
    $(".phones").on('mouseleave', function () {
        $('.phones ').slideUp('medium');
    }
    );

});