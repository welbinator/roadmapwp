jQuery(document).ready(function($) {
    // Listen for changes on checkboxes and radio buttons in the filter
    $('.wp-roadmap-ideas-filter-taxonomy input[type=checkbox], .wp-roadmap-ideas-filter-taxonomy input[type=radio]').change(function() {
        var filterData = {};
        $('.wp-roadmap-ideas-filter-taxonomy').each(function() {
            var taxonomy = $(this).data('taxonomy');
            var matchType = $('input[name="match_type_' + taxonomy + '"]:checked').val();
            filterData[taxonomy] = {
                'terms': [],
                'matchType': matchType
            };
            $(this).find('input[type=checkbox]:checked').each(function() {
                filterData[taxonomy]['terms'].push($(this).val());
            });
        });

        $.ajax({
            url: wpRoadMapAjax.ajax_url,
            type: 'POST',
            data: {
                'action': 'filter_ideas',
                'filter_data': filterData,
                'nonce': wpRoadMapAjax.nonce // Include the nonce for security
            },
            success: function(response) {
                $('.wp-roadmap-ideas-list').html(response);
            },
            error: function(jqXHR, textStatus, errorThrown) {
                console.log('AJAX error:', textStatus, errorThrown); // Log errors for debugging
            }
        });
    });
});
