<?php
/**
 * Partial: search form.
 */

defined('ABSPATH') || exit;

use SiteKit\Util;

$id = 'sk-search-' . wp_rand(1000, 9999);
?>
<form role="search" method="get" class="sk-search" action="<?php echo esc_url(home_url('/')); ?>">
    <label class="sk-sr" for="<?php echo esc_attr($id); ?>"><?php esc_html_e('Search for:', 'site-kit'); ?></label>
    <input id="<?php echo esc_attr($id); ?>" type="search" name="s"
           value="<?php echo esc_attr(get_search_query()); ?>"
           placeholder="<?php esc_attr_e('Search this site…', 'site-kit'); ?>">
    <button class="sk-btn sk-btn--primary sk-btn--sm" type="submit">
        <?php Util\icon('search', ''); ?>
        <span><?php esc_html_e('Search', 'site-kit'); ?></span>
    </button>
</form>
