jQuery(document).ready(function($) {
    // Listen for changes on checkboxes and radio buttons in the filter
    $('.rmwp__ideas-filter-taxonomy input[type=checkbox], .rmwp__ideas-filter-taxonomy input[type=radio]').change(function() {
        var filterData = {};
        $('.rmwp__ideas-filter-taxonomy').each(function() {
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
            url: wpRoadMapFilter.ajax_url,
            type: 'POST',
            data: {
                action: 'filter_ideas',
                filter_data: filterData,
                nonce: wpRoadMapFilter.nonce
            },
            success: function(response) {
                $('.rmwp__ideas-list').html(response);
            },
            error: function(jqXHR, textStatus, errorThrown) {
                console.log('AJAX error:', textStatus, errorThrown);
            }
        });
    });
});
