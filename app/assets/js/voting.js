jQuery(document).ready(function($) {
    $(document).on('click', '.idea-vote-button', function() {
        var post_id = $(this).closest('.idea-vote-box').data('idea-id');

        $.ajax({
            url: wpRoadMapVoting.ajax_url,
            type: 'post',
            data: {
                action: 'wp_roadmap_handle_vote',
                post_id: post_id,
                nonce: wpRoadMapVoting.nonce
            },
            success: function(response) {
                if (response.success) {
                    $('.idea-vote-box[data-idea-id="' + post_id + '"] .idea-vote-count').text(response.data.new_count);
                    
                    if (response.data.voted) {
                        // Vote was added, set the cookie
                        document.cookie = "voted_idea_" + post_id + "=true; max-age=" + 60 * 60 * 24 * 365 + "; path=/";
                    } else {
                        // Vote was removed, clear the cookie
                        document.cookie = "voted_idea_" + post_id + "=; max-age=0; path=/";
                    }
                }
            }
            
        });
    });
});
