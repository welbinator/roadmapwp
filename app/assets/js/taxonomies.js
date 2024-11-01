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
            url: roadmapwpAjax.ajax_url,
            type: 'post',
            data: {
                action: 'delete_custom_taxonomy',
                taxonomy: taxonomy, // Ensure this parameter is correctly included
                nonce: roadmapwpAjax.delete_taxonomy_nonce
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
        var form = $(this);
        var taxonomy = form.data('taxonomy');
        var selectedTerms = form.find('input[type="checkbox"]:checked').map(function() {
            return this.value;
        }).get();
    
        $.ajax({
            url: roadmapwpAjax.ajax_url,
            type: 'post',
            data: {
                action: 'delete_selected_terms',
                taxonomy: taxonomy,
                terms: selectedTerms,
                nonce: roadmapwpAjax.delete_terms_nonce
            },
            success: function(response) {
                if (response.success) {
                    // Remove the deleted terms from the list
                    selectedTerms.forEach(function(termId) {
                        form.find('input[value="' + termId + '"]').closest('li').remove();
                    });
    
                    // Display the success message at the top of the form or page
                    $('.wrap.custom').prepend('<div class="updated"><p>Term deleted successfully.</p></div>');
                } else {
                    // Display error message
                    $('.wrap.custom').prepend('<div class="error"><p>' + response.data.message + '</p></div>');
                }
            }
        });
    });
    
    
    
});