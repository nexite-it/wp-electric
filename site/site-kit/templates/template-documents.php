<?php
/**
 * Template: documents archive.
 *
 * A filterable, table-like list of downloadable documents. Files are linked
 * through the `sk_document_file` meta field, so the media library stays the
 * single source of truth for uploads.
 */

defined('ABSPATH') || exit;

use SiteKit\Content;
use SiteKit\Util;

$intro = Content\page_intro('documents');

$query = new WP_Query([
    'post_type'      => 'document',
    'post_status'    => 'publish',
    'posts_per_page' => 50,
    'orderby'        => 'menu_order date',
    'order'          => 'ASC',
]);

$types = get_terms(['taxonomy' => 'document_type', 'hide_empty' => true]);
$types = is_array($types) ? $types : [];

\SiteKit\Template\partial('header', ['page_key' => 'documents']);
?>
<main id="content">
    <?php
    Util\page_hero([
        'eyebrow' => $intro['eyebrow'],
        'title'   => $intro['title'],
        'lead'    => $intro['lead'],
        'crumbs'  => [['label' => $intro['title']]],
    ]);
    ?>

    <section class="sk-section">
        <div class="sk-wrap">
            <?php if ($query->have_posts()) : ?>
                <?php if ($types) : ?>
                    <div class="sk-tabs reveal" data-filter data-filter-for="sk-doc-list" role="group" aria-label="<?php esc_attr_e('Filter documents', 'site-kit'); ?>">
                        <button class="sk-tab is-active" type="button" data-filter-val="*"><?php esc_html_e('All', 'site-kit'); ?></button>
                        <?php foreach ($types as $type) : ?>
                            <button class="sk-tab" type="button" data-filter-val="<?php echo esc_attr($type->slug); ?>"><?php echo esc_html($type->name); ?></button>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <ul class="sk-docs" id="sk-doc-list">
                    <?php
                    while ($query->have_posts()) :
                        $query->the_post();
                        $doc   = get_post();
                        $file  = Util\meta('sk_document_file', $doc);
                        $date  = Util\meta('sk_document_date', $doc);
                        $terms = get_the_terms($doc, 'document_type');
                        $terms = is_array($terms) ? $terms : [];
                        $slugs = wp_list_pluck($terms, 'slug');
                        ?>
                        <li class="sk-docs__item sk-filter-item" data-categories="<?php echo esc_attr(implode(',', $slugs)); ?>">
                            <div class="sk-docs__icon"><?php Util\icon('file-text', ''); ?></div>
                            <div class="sk-docs__main">
                                <h2>
                                    <?php if ($file !== '') : ?>
                                        <a href="<?php echo esc_url($file); ?>" target="_blank" rel="noopener"><?php echo esc_html(get_the_title()); ?></a>
                                    <?php else : ?>
                                        <?php echo esc_html(get_the_title()); ?>
                                    <?php endif; ?>
                                </h2>
                                <p><?php echo esc_html(Util\excerpt($doc, 24)); ?></p>
                                <div class="sk-tags">
                                    <?php foreach ($terms as $term) : ?>
                                        <span class="sk-tag"><?php echo esc_html($term->name); ?></span>
                                    <?php endforeach; ?>
                                    <?php if ($date !== '') : ?>
                                        <span class="sk-docs__date"><?php echo esc_html(Util\date_parts($date)['day'] . '. ' . date_i18n('F Y', strtotime($date))); ?></span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <?php if ($file !== '') : ?>
                                <a class="sk-btn sk-btn--outline sk-btn--sm sk-docs__action" href="<?php echo esc_url($file); ?>" target="_blank" rel="noopener">
                                    <?php Util\icon('download', ''); ?>
                                    <?php esc_html_e('Open', 'site-kit'); ?>
                                </a>
                            <?php endif; ?>
                        </li>
                    <?php endwhile; ?>
                </ul>
            <?php else : ?>
                <div class="sk-empty reveal">
                    <div class="sk-empty__icon"><?php Util\icon('doc', ''); ?></div>
                    <h2><?php esc_html_e('No documents yet', 'site-kit'); ?></h2>
                    <p><?php esc_html_e('Uploaded documents are listed here automatically.', 'site-kit'); ?></p>
                </div>
            <?php endif; ?>
            <?php wp_reset_postdata(); ?>
        </div>
    </section>
</main>
<?php
\SiteKit\Template\partial('footer');
