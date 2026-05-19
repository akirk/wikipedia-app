<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

use Akirk\Wordopedia\App;

$article = isset( $article ) && is_array( $article ) ? $article : [];
$article_html = isset( $article['html'] ) ? $article['html'] : ( $article['content'] ?? '' );
$is_saved_view = ! empty( $is_saved_view );
$snippets = $is_saved_view && ! empty( $article['snippets'] ) && is_array( $article['snippets'] ) ? $article['snippets'] : [];
$can_save_snippets = current_user_can( 'edit_posts' ) && ( ! empty( $article['post_id'] ) || ! empty( $article['page_id'] ) );
$snippet_count = count( $snippets );
$saved_label = '';
if ( $is_saved_view ) {
    $saved_date = ! empty( $article['last_saved_at_display'] ) ? $article['last_saved_at_display'] : App::format_datetime( $article['last_saved_at'] ?? '' );
    $saved_label = $saved_date
        ? sprintf(
            /* translators: %s: saved date */
            __( 'Saved %s', 'wordopedia' ),
            $saved_date
        )
        : __( 'Saved', 'wordopedia' );
}
?>
<div class="wiki-page-head wiki-article-head">
    <div>
        <h1><?php echo esc_html( $article['title'] ?? '' ); ?></h1>
        <p class="wiki-subtitle">
            <?php echo esc_html( ( $article['language_label'] ?? '' ) . ' (' . ( $article['language'] ?? '' ) . ')' ); ?>
            <?php if ( $saved_label ) : ?>
                <span class="wiki-saved-status">
                    <span aria-hidden="true">✓</span>
                    <?php echo esc_html( $saved_label ); ?>
                    <?php if ( current_user_can( 'edit_posts' ) && ! empty( $article['post_id'] ) ) : ?>
                        <form class="wiki-inline-form wiki-saved-refresh" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
                            <?php wp_nonce_field( App::NONCE_REFETCH_ARTICLE . '_' . $article['post_id'] ); ?>
                            <input type="hidden" name="action" value="wordopedia_refetch_article">
                            <input type="hidden" name="post_id" value="<?php echo esc_attr( $article['post_id'] ); ?>">
                            <button class="wiki-btn secondary wiki-mini-btn" type="submit"><?php esc_html_e( 'Refresh', 'wordopedia' ); ?></button>
                        </form>
                    <?php endif; ?>
                </span>
            <?php endif; ?>
        </p>
    </div>
</div>

<?php if ( $is_saved_view ) : ?>
    <section class="wiki-snippets" aria-labelledby="wiki-snippets-title" data-wiki-snippets data-snippet-count="<?php echo esc_attr( $snippet_count ); ?>" data-count-singular="<?php esc_attr_e( 'snippet', 'wordopedia' ); ?>" data-count-plural="<?php esc_attr_e( 'snippets', 'wordopedia' ); ?>" <?php echo $snippet_count ? '' : 'hidden'; ?>>
        <div class="wiki-section-head wiki-snippets-head">
            <h2 id="wiki-snippets-title"><?php esc_html_e( 'Snippets', 'wordopedia' ); ?></h2>
            <div class="wiki-meta">
                <span data-wiki-snippet-count>
                    <?php
                    printf(
                        /* translators: %d: number of saved snippets */
                        esc_html( _n( '%d snippet', '%d snippets', $snippet_count, 'wordopedia' ) ),
                        $snippet_count
                    );
                    ?>
                </span>
            </div>
        </div>
        <ol class="wiki-snippet-list" data-wiki-snippet-list>
            <?php foreach ( $snippets as $snippet ) : ?>
                <?php
                $snippet_id = isset( $snippet['post_id'] ) ? absint( $snippet['post_id'] ) : 0;
                $snippet_text = isset( $snippet['text'] ) ? (string) $snippet['text'] : (string) ( $snippet['content'] ?? '' );
                $snippet_html = isset( $snippet['html'] ) ? (string) $snippet['html'] : ( isset( $snippet['content'] ) ? (string) $snippet['content'] : $snippet_text );
                $snippet_html = ! isset( $snippet['html'] ) && function_exists( 'do_blocks' ) ? do_blocks( $snippet_html ) : $snippet_html;
                $snippet_can_edit = $snippet_id && current_user_can( 'edit_post', $snippet_id );
                $snippet_can_delete = $snippet_id && current_user_can( 'delete_post', $snippet_id );
                ?>
                <li class="wiki-snippet" id="<?php echo esc_attr( 'wiki-snippet-' . $snippet_id ); ?>">
                    <div class="wiki-snippet-content" data-wiki-snippet-content><?php echo wp_kses_post( $snippet_html ); ?></div>
                    <div class="wiki-snippet-tools">
                        <span class="wiki-meta" data-wiki-snippet-status>
                            <?php
                            $snippet_updated = ! empty( $snippet['updated_at_display'] ) ? $snippet['updated_at_display'] : '';
                            echo esc_html( $snippet_updated ? sprintf(
                                /* translators: %s: snippet updated date */
                                __( 'Updated %s', 'wordopedia' ),
                                $snippet_updated
                            ) : __( 'Saved snippet', 'wordopedia' ) );
                            ?>
                        </span>
                        <div class="wiki-snippet-buttons">
                            <?php if ( $snippet_can_edit ) : ?>
                                <?php if ( ! empty( $snippet['edit_url'] ) ) : ?>
                                    <a class="wiki-btn secondary" href="<?php echo esc_url( $snippet['edit_url'] ); ?>"><?php esc_html_e( 'Edit snippet', 'wordopedia' ); ?></a>
                                <?php endif; ?>
                            <?php endif; ?>
                            <?php if ( $snippet_can_delete ) : ?>
                                <form class="wiki-inline-form wiki-snippet-delete" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" data-wiki-snippet-delete data-wiki-ajax-url="<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>">
                                    <?php wp_nonce_field( App::NONCE_DELETE_SNIPPET . '_' . $snippet_id ); ?>
                                    <input type="hidden" name="action" value="wordopedia_delete_snippet">
                                    <input type="hidden" name="post_id" value="<?php echo esc_attr( $snippet_id ); ?>">
                                    <button class="wiki-btn secondary" type="submit"><?php esc_html_e( 'Delete', 'wordopedia' ); ?></button>
                                </form>
                            <?php endif; ?>
                        </div>
                    </div>
                </li>
            <?php endforeach; ?>
        </ol>
    </section>
<?php endif; ?>

<?php if ( $can_save_snippets ) : ?>
    <form class="wiki-selection-snippet" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" data-wiki-snippet-form data-wiki-ajax-url="<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>" data-saving-text="<?php esc_attr_e( 'Saving...', 'wordopedia' ); ?>" data-saved-text="<?php esc_attr_e( 'Snippet saved.', 'wordopedia' ); ?>" data-error-text="<?php esc_attr_e( 'Could not save snippet.', 'wordopedia' ); ?>" hidden>
        <?php wp_nonce_field( App::NONCE_SAVE_SNIPPET ); ?>
        <input type="hidden" name="action" value="wordopedia_save_snippet">
        <input type="hidden" name="text" value="" data-wiki-snippet-text>
        <input type="hidden" name="html" value="" data-wiki-snippet-html>
        <?php if ( $is_saved_view && ! empty( $article['post_id'] ) ) : ?>
            <input type="hidden" name="parent_post_id" value="<?php echo esc_attr( $article['post_id'] ); ?>">
        <?php else : ?>
            <input type="hidden" name="page_id" value="<?php echo esc_attr( $article['page_id'] ?? 0 ); ?>">
            <input type="hidden" name="title" value="<?php echo esc_attr( $article['title'] ?? '' ); ?>">
            <input type="hidden" name="language" value="<?php echo esc_attr( $article['language'] ?? '' ); ?>">
        <?php endif; ?>
        <div class="wiki-selection-snippet-actions">
            <button class="wiki-btn" type="submit"><?php esc_html_e( 'Save snippet', 'wordopedia' ); ?></button>
            <button class="wiki-btn secondary wiki-icon-btn" type="button" data-wiki-snippet-cancel aria-label="<?php esc_attr_e( 'Cancel', 'wordopedia' ); ?>">&times;</button>
        </div>
    </form>
<?php endif; ?>

<article class="wiki-article" data-wiki-article-snippets>
    <?php echo wp_kses( $article_html, App::article_allowed_html() ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- article HTML is sanitized on output. ?>
</article>
