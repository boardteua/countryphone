jQuery(function ($) {

    $('.ch-save-hone').hide();
    $('.ch-add-hone').show();

    $.ajax({
        type: 'POST',
        url: ajaxurl,
        data: {
            action: 'get_rows',
            req: ''
        },
        success: function (res) {

            var html = '';
            if (res.success != false) {

                $.each(res.data.option, function (index, value) {
                    html += '<li><strong class="phone_number" >' + value.phone_number + '</strong> - <strong class="country_code" >' + value.country_code + '</strong> <a href="#" class="edit" data-id="' + index + '">Edit</a>  <a href="#" class="delete" data-id="' + index + '">Delete</a></li>'
                });
                $('ul.phone-list').html(html);
            } else {
                $('ul.phone-list').append(
                        'Empty list'
                        );
            }
        },
        error: function (errorThrown) {
            alert(errorThrown);
        }
    });

    $('.ch-add-hone').click(function (e) {
        e.preventDefault();


            $.ajax({
                type: 'POST',
                url: ajaxurl,
                data: {
                    action: 'add_row',
                    req: $('#add_number').serialize()
                },
                success: function (res) {

                    var html = '';
                    if (res.success != false) {

                        $.each(res.data.option, function (index, value) {

                            html += '<li><strong class="phone_number" >' + value.phone_number + '</strong> - <strong class="country_code" >' + value.country_code + '</strong> <a href="#" class="edit" data-id="' + index + '">Edit</a>  <a href="#" class="delete" data-id="' + index + '">Delete</a></li>'

                        });
                        $('ul.phone-list').html(html);

                        document.getElementById("add_number").reset();

                    } else {
                   
                        $('ul.phone-list').append(
                                'error'
                                );
                    }
                },
                error: function (errorThrown) {
                    alert(errorThrown);
                }
            });
        
    });

    $('.phone-list').on('click', '.delete', function (e) {
        e.preventDefault();
        $.ajax({
            type: 'POST',
            url: ajaxurl,
            data: {
                action: 'delete_row',
                req: $(this).data('id')
            },
            success: function (res) {

                var html = '';
                if (res.success != false) {

                    $.each(res.data.option, function (index, value) {

                        html += '<li><strong class="phone_number" >' + value.phone_number + '</strong> - <strong class="country_code" >' + value.country_code + '</strong> <a href="#" class="edit" data-id="' + index + '">Edit</a>  <a href="#" class="delete" data-id="' + index + '">Delete</a></li>'

                    });
                    $('ul.phone-list').html(html);
                } else {
                    $('ul.phone-list').append(
                            'error'
                            );
                }
            },
            error: function (errorThrown) {
                alert(errorThrown);
            }
        });
    });

    $('.phone-list').on('click', '.edit', function (e) {
        e.preventDefault();
        var id = $(this).data('id');

        $('.ch-save-hone').show();
        $('.ch-add-hone').hide();
        $('.ch-phone-number').val($('.phone-list').find('li').eq(id).find('.phone_number').text());
        $('.ch-country-code ').val($('.phone-list').find('li').eq(id).find('.country_code').text());



    });
    $('#add_number').on('click', '.ch-save-hone', function (e) {
        e.preventDefault();

        var id = $(this).data('id');

        row = {
            phone_number: $('.ch-phone-number').val(),
            country_code: $('.ch-country-code ').val(),
            id: id
        };


        $.ajax({
            type: 'POST',
            url: ajaxurl,
            data: {
                action: 'edit_row',
                req: row
            },
            success: function (res) {

                var html = '';
                if (res.success != false) {

                    $.each(res.data.option, function (index, value) {

                        html += '<li><strong class="phone_number" >' + value.phone_number + '</strong> - <strong class="country_code" >' + value.country_code + '</strong> <a href="#" class="edit" data-id="' + index + '">Edit</a>  <a href="#" class="delete" data-id="' + index + '">Delete</a></li>'

                    });
                    $('ul.phone-list').html(html);

                    document.getElementById("add_number").reset();

                    $('.ch-save-hone').hide();
                    $('.ch-add-hone').show();

                } else {
                    $('ul.phone-list').append(
                            'error'
                            );
                }
            },
            error: function (errorThrown) {
                alert(errorThrown);
            }
        });
    });


});