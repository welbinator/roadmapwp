jQuery(document).ready(function($) {

    function sendAjaxRequest() {
        var filterData = {};

        // Collecting filter data
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

        // AJAX request with filters
        $.ajax({
            url: wpRoadMapFilter.ajax_url,
            type: 'POST',
            data: {
                action: 'filter_ideas',
                filter_data: filterData, // Pass the filter data
                nonce: wpRoadMapFilter.nonce // Security nonce
            },
            success: function(response) {
                $('.rmwp__ideas-list').html(response);
            },
            error: function(jqXHR, textStatus, errorThrown) {
                console.log('AJAX error:', textStatus, errorThrown);
            }
        });
    }

    // Bind the sendAjaxRequest function to filter changes
    $('.rmwp__ideas-filter-taxonomy input').change(sendAjaxRequest);
});
