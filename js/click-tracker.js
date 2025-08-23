jQuery(document).ready(function($) {
    $('a').on('click', function(e) {
        var link = $(this).attr('href');
        
        // A more robust check for internal vs. external links
        try {
            var url = new URL(link, window.location.href);
            var is_internal = url.hostname === window.location.hostname;
        } catch (error) {
            // Fallback for non-valid URLs
            var is_internal = link.indexOf(window.location.host) !== -1 || link.indexOf('/') === 0;
        }

        // Check if the link is external and not part of the WordPress admin bar
        if (!is_internal && $(this).closest('#wpadminbar').length === 0) {
            e.preventDefault();
            
            $.ajax({
                type: 'POST',
                url: MyAjax.ajaxurl,
                data: {
                    action: 'track_external_click',
                    url: link,
                },
                success: function(response) {
                    window.location.href = link;
                },
                error: function() {
                    window.location.href = link;
                }
            });
        }
    });
});