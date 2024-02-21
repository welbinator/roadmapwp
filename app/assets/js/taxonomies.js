jQuery(document).ready(function($) {
    // Handling deletion of a taxonomy
    $('.delete-taxonomy').on('click', function(e) {
        e.preventDefault();
        var taxonomy = $(this).data('taxonomy');

        // Make sure the taxonomy value is being captured
        if (!taxonomy) {
            alert('Taxonomy not specified.');
            return;
        }
        
        $.ajax({
            url: wpRoadmapAjax.ajax_url,
            type: 'post',
            data: {
                action: 'delete_custom_taxonomy',
                taxonomy: taxonomy,
                nonce: wpRoadmapAjax.delete_taxonomy_nonce
            },
            success: function(response) {
                if (response.success) {
                    window.location.reload(); // Reload the page
                } else {
                    alert('Error: ' + response.data.message);
                }
            }
        });
    });

    // Handling deletion of selected terms
    $('.delete-terms-form').on('submit', function(e) {
        e.preventDefault();
        var taxonomy = $(this).data('taxonomy');
        var selectedTerms = $(this).find('input[type="checkbox"]:checked').map(function() {
            return this.value;
        }).get();

        $.ajax({
            url: wpRoadmapAjax.ajax_url,
            type: 'post',
            data: {
                action: 'delete_selected_terms',
                taxonomy: taxonomy,
                terms: selectedTerms,
                nonce: wpRoadmapAjax.delete_terms_nonce
            },
            success: function(response) {
                if (response.success) {
                    window.location.reload(); // Reload the page
                } else {
                    alert('Error: ' + response.data.message);
                }
            }
        });
    });
});
