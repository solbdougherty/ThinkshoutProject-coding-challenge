jQuery(document).ready( function($) {
        $.ajax({
                type: 'post',
                url: tst_ajax.ajaxUrl,
                data: {
                        action: 'tst_banner_request',
                        nonce: tst_ajax.nonce,
                },
                success: function(result) {
                        var parsed = JSON.parse(result);
                        console.log(parsed);
                        if (parsed != null) {
                                if (parsed.start_time != null) {
                                        $('.site-header').before('<div class="tst-banner">' +
                                            '<div class="tst-container">' +
                                            '<div class="tst-content">' +
                                            '<p class="tst-special-hours">Special hours today: ' + parsed.start_time + ' - ' + parsed.end_time + '</p>' +
                                            '<p class="tst-message">' + parsed.text_string + '</p>' +
                                            '</div>' +
                                            '<div class="tst-close"><p>&#10005</p></div>' +
                                            '</div>' +
                                            '</div>');
                                }
                                else {
                                        $('.site-header').after('<div class="tst-banner">' +
                                            '<div class="tst-container">' +
                                            '<div class="tst-content"' +
                                            '<p class="tst-special-hours">Special hours today: ' + parsed.start_time + ' - ' + parsed.end_time + '</p>' +
                                            '<p class="tst-message">' + parsed.text_string + '</p>' +
                                            '<div class="tst-close"><p>&#10005</p></div>' +
                                            '</div>' +
                                            '</div>' +
                                            '</div>')
                                }
                        }

                },
        });
        $('.tst-close').on('click', function() {
                $('.tst-banner').hide();
        });
});