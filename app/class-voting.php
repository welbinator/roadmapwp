<?php

namespace RoadMapWP\Free\ClassVoting;

class VotingHandler {

/**
 * Checks if a user is allowed to vote.
 *
 * @param int $user_id The ID of the user attempting to vote.
 * @return bool True if the user can vote, false otherwise.
 */
public static function can_user_vote($user_id) {
    $options = get_option('wp_roadmap_settings');
    // Check if the restrict voting setting is enabled
    if (isset($options['restrict_voting']) && $options['restrict_voting']) {
        // Return true if user is logged in, false otherwise
        return is_user_logged_in();
    }

    // If the setting is not enabled or doesn't exist, apply any other filters or default logic
    return apply_filters('roadmapwp_can_user_vote', true, $user_id);
}


    /**
     * Handles the process of voting for an idea.
     *
     * @param int $post_id The ID of the post being voted on.
     * @param int $user_id The ID of the user who is voting, 0 for guests.
     * @return array An array containing 'new_count' and 'voted' status.
     */
    public static function handle_vote($post_id, $user_id) {
        $remote_addr = isset($_SERVER['REMOTE_ADDR']) ? sanitize_text_field($_SERVER['REMOTE_ADDR']) : '';
        $http_user_agent = isset($_SERVER['HTTP_USER_AGENT']) ? sanitize_text_field($_SERVER['HTTP_USER_AGENT']) : '';
        $user_key = $user_id ? 'user_' . $user_id : 'guest_' . md5($remote_addr . $http_user_agent);

        // Retrieve the current vote count
        $current_votes = get_post_meta($post_id, 'idea_votes', true) ?: 0;

        // Check if this user or guest has already voted
        $has_voted = get_post_meta($post_id, 'voted_' . $user_key, true);

        if ($has_voted) {
            // User or guest has voted, remove their vote
            $new_votes = max($current_votes - 1, 0);
            delete_post_meta($post_id, 'voted_' . $user_key);
        } else {
            // User or guest hasn't voted, add their vote
            $new_votes = $current_votes + 1;
            update_post_meta($post_id, 'voted_' . $user_key, true);
        }

        // Update the post meta with the new vote count
        update_post_meta($post_id, 'idea_votes', $new_votes);

        return [
            'new_count' => $new_votes,
            'voted'     => !$has_voted,
        ];
    }

    /**
     * Renders the voting button for an idea.
     *
     * @param int $idea_id The ID of the idea.
     * @param int $vote_count The current vote count for the idea.
     */
    public static function render_vote_button($idea_id, $vote_count) {
        $vote_count = esc_html($vote_count);
        echo <<<HTML
<div class="flex items-center idea-vote-box" data-idea-id="{$idea_id}">
    <button class="inline-flex items-center justify-center text-sm font-medium h-10 bg-blue-500 text-white px-4 py-2 rounded-lg idea-vote-button">
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-5 h-5 mr-1">
            <path d="M7 10v12"></path>
            <path d="M15 5.88 14 10h5.83a2 2 0 0 1 1.92 2.56l-2.33 8A2 2 0 0 1 17.5 22H4a2 2 0 0 1-2-2v-8a2 2 0 0 1 2-2h2.76a2 2 0 0 0 1.79-1.11L12 2h0a3.13 3.13 0 0 1 3 3.88Z"></path>
        </svg>
        <div class="idea-vote-count">{$vote_count}</div>
    </button>
</div>
HTML;
    }
}