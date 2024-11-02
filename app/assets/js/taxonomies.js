jQuery(document).ready(function($) {
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
                // Check if response.success is true
                if (response && response.success) {
                    // Remove the deleted terms from the list
                    selectedTerms.forEach(function(termId) {
                        form.find('input[value="' + termId + '"]').closest('li').remove();
                    });
    
                    // Display the success message at the top of the form or page
                    $('.wrap.custom').prepend('<div class="updated"><p>Terms deleted successfully.</p></div>');
                } else {
                    // Check if response.data and response.data.message are defined
                    var errorMessage = response && response.data && response.data.message 
                        ? response.data.message 
                        : 'An unexpected error occurred. Please try again.';
                    
                    // Display error message
                    $('.wrap.custom').prepend('<div class="error"><p>' + errorMessage + '</p></div>');
                }
            },
            error: function() {
                // Handle server or connection errors
                $('.wrap.custom').prepend('<div class="error"><p>An error occurred while processing the request. Please try again later.</p></div>');
            }
        });
    });
});
