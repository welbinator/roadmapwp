# RoadMapWP Free - AI Coding Instructions

## Project Overview
WordPress plugin for user-submitted ideas with voting, statuses, and roadmap display. Free version with Pro upgrade paths.

## Architecture

### Namespace Structure
All code uses PHP namespaces:
- `RoadMapWP\Free\` - Root namespace
- `RoadMapWP\Free\CPT\` - Custom post type registration (`app/cpt-ideas.php`)
- `RoadMapWP\Free\Ajax\` - AJAX handlers (`app/ajax-handlers.php`)
- `RoadMapWP\Free\Admin\Functions\` - Admin utilities (`app/admin-functions.php`)
- `RoadMapWP\Free\Admin\Pages\` - Admin pages (`app/admin-pages.php`)
- `RoadMapWP\Free\Shortcodes\*\` - Each shortcode gets its own namespace
- `RoadMapWP\Free\ClassVoting\` - Voting system class (`app/class-voting.php`)

### Core Custom Post Type: `idea`
- Post type slug: `idea`
- Built-in taxonomies: `idea-status`, `idea-tag`
- Custom taxonomies stored in option: `wp_roadmap_custom_taxonomies`
- Default status terms: New Idea, Maybe, Up Next, On Roadmap, Not Now, Closed
- Vote count stored in post meta: `idea_votes`
- Individual votes tracked: `voted_user_{id}` or `voted_guest_{hash}`

### Plugin Entry Point
`wp-roadmap.php` - Requires all app files, registers activation hooks, handles Pro version conflicts.

### Settings
Stored in option: `wp_roadmap_settings`
- `default_status_term` - Default status for new ideas
- `default_idea_status` - Published/pending/draft (pending, publish, draft)
- `allow_comments` - Enable/disable comments on ideas
- `restrict_voting` - Restrict voting to logged-in users
- `single_idea_template` - Template choice (plugin/theme)

## Key Patterns

### AJAX Security
All AJAX handlers require nonce verification:
```php
check_ajax_referer('wp-roadmap-vote-nonce', 'nonce');
```
Nonces are localized in `enqueue_frontend_styles()` via `wp_localize_script()`.

### Pro Version Hooks
Free version uses filters to show "Available in Pro" buttons:
```php
apply_filters('wp_roadmap_default_idea_status_setting', $default_html);
```
Pro version hooks these filters to replace UI. Never remove these filter applications.

### Asset Enqueueing
- **Frontend**: Only enqueues if shortcode present or on single `idea` (see `check_for_shortcode_or_block_presence()`)
- **Tailwind CSS**: Built to `dist/styles.css`, no preflight mode enabled
- **Admin**: Conditionally loads based on `$hook` parameter

### Voting System
- Class-based: `VotingHandler::can_user_vote()`, `::handle_vote()`, `::render_vote_button()`
- Guest voting uses MD5 hash of IP + user agent
- Vote removal if already voted (toggle behavior)

### Shortcodes
Each shortcode in `app/shortcodes/`:
- `[new_idea_form]` - Submission form + handles POST via `template_redirect` action
- `[display_ideas]` - Grid display with filters
- `[roadmap status="..."]` - Static roadmap display
- `[roadmap_tabs status="..."]` - Tab-based roadmap with AJAX loading

## Development Workflow

### Build Process
```bash
npm run build  # Compiles Tailwind: app/assets/css/styles.css → dist/styles.css
```

### Code Quality
```bash
composer phpcs:wp   # Run WordPress coding standards check
composer phpcbf:wp  # Auto-fix coding standards issues
```

### Deployment to WordPress.org SVN
```bash
composer sync-to-trunk  # Syncs to svn/trunk/, excludes dev files
```

## Critical Conventions

### Text Domain
Always use `'roadmapwp-free'` for i18n functions, never hardcode strings.

### Escaping
- `esc_html()` for text output
- `esc_attr()` for HTML attributes
- `esc_url()` for URLs
- `wp_kses_post()` for filtered HTML (admin areas)

### Sanitization
- `sanitize_text_field()` for single-line input
- `sanitize_textarea_field()` for multi-line
- `intval()` for integers
- `array_map()` for arrays of values

### WordPress Hooks
- Use `__NAMESPACE__ . '\\function_name'` for hook callbacks
- Check `! defined('ABSPATH')` in all files

### Tailwind Usage
- Preflight disabled - WordPress handles base styles
- Content paths in `tailwind.config.js` include shortcodes, templates, AJAX handlers
- Safelist dynamic grid classes (`grid-cols-1` through `grid-cols-5` with breakpoints)

## File Structure Patterns

- **`app/`** - Core functionality (no subdirectories except assets, includes, settings, shortcodes, templates)
- **`app/assets/css/`** - Source CSS (admin, frontend, customizer)
- **`app/assets/js/`** - Frontend scripts (voting, filters, tabs)
- **`dist/`** - Built CSS output (generated, not in repo)
- **`svn/`** - WordPress.org deployment target

## Testing Key Scenarios

1. **Voting**: Test logged-in, logged-out, and vote toggle behavior
2. **Taxonomies**: Custom taxonomies from options must register before CPT
3. **AJAX**: Always verify nonce and sanitize all inputs
4. **Pro Detection**: Check plugin doesn't break if Pro installed

## Version Management
- Update version in: `wp-roadmap.php` header, `RMWP_PLUGIN_VERSION` constant, `package.json`, `composer.json` (name field mirrors version)
