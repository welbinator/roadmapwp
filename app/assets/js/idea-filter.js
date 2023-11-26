jQuery(document).ready(function($) {
    $('.wp-road-map-ideas-filter-select').change(function() {
        var filterData = {};
        $('.wp-road-map-ideas-filter-select').each(function() {
            var taxonomy = $(this).attr('id');
            var term = $(this).val();
            if (term) {
                filterData[taxonomy] = term;
            }
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
                $('.wp-road-map-ideas-list').html(response);
            },
            error: function(jqXHR, textStatus, errorThrown) {
                console.log('AJAX error:', textStatus, errorThrown); // Log errors for debugging
            }
        });
    });
});
