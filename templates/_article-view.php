<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

use Akirk\Wikipedia\App;

$article = isset( $article ) && is_array( $article ) ? $article : [];
$article_html = isset( $article['html'] ) ? $article['html'] : ( $article['content'] ?? '' );
$is_saved_view = ! empty( $is_saved_view );
$snippets = $is_saved_view && ! empty( $article['snippets'] ) && is_array( $article['snippets'] ) ? $article['snippets'] : [];
$can_save_snippets = current_user_can( 'edit_posts' ) && ( ! empty( $article['post_id'] ) || ! empty( $article['page_id'] ) );
$saved_label = '';
if ( $is_saved_view ) {
    $saved_date = ! empty( $article['last_saved_at_display'] ) ? $article['last_saved_at_display'] : App::format_datetime( $article['last_saved_at'] ?? '' );
    $saved_label = $saved_date
        ? sprintf(
            /* translators: %s: saved date */
            __( 'Saved %s', 'wikipedia' ),
            $saved_date
        )
        : __( 'Saved', 'wikipedia' );
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
                            <input type="hidden" name="action" value="wikipedia_refetch_article">
                            <input type="hidden" name="post_id" value="<?php echo esc_attr( $article['post_id'] ); ?>">
                            <button class="wiki-btn secondary wiki-mini-btn" type="submit"><?php esc_html_e( 'Refresh', 'wikipedia' ); ?></button>
                        </form>
                    <?php endif; ?>
                </span>
            <?php endif; ?>
        </p>
    </div>
</div>

<?php if ( $is_saved_view && $snippets ) : ?>
    <section class="wiki-snippets" aria-labelledby="wiki-snippets-title">
        <div class="wiki-section-head wiki-snippets-head">
            <h2 id="wiki-snippets-title"><?php esc_html_e( 'Snippets', 'wikipedia' ); ?></h2>
            <div class="wiki-meta">
                <span>
                    <?php
                    printf(
                        /* translators: %d: number of saved snippets */
                        esc_html( _n( '%d snippet', '%d snippets', count( $snippets ), 'wikipedia' ) ),
                        count( $snippets )
                    );
                    ?>
                </span>
            </div>
        </div>
        <ol class="wiki-snippet-list">
            <?php foreach ( $snippets as $snippet ) : ?>
                <?php
                $snippet_id = isset( $snippet['post_id'] ) ? absint( $snippet['post_id'] ) : 0;
                $snippet_text = isset( $snippet['text'] ) ? (string) $snippet['text'] : (string) ( $snippet['content'] ?? '' );
                $snippet_can_edit = $snippet_id && current_user_can( 'edit_post', $snippet_id );
                ?>
                <li class="wiki-snippet" id="<?php echo esc_attr( 'wiki-snippet-' . $snippet_id ); ?>">
                    <?php if ( $snippet_can_edit ) : ?>
                        <form class="wiki-snippet-edit" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
                            <?php wp_nonce_field( App::NONCE_UPDATE_SNIPPET . '_' . $snippet_id ); ?>
                            <input type="hidden" name="action" value="wikipedia_update_snippet">
                            <input type="hidden" name="post_id" value="<?php echo esc_attr( $snippet_id ); ?>">
                            <textarea name="text" rows="4" aria-label="<?php esc_attr_e( 'Snippet text', 'wikipedia' ); ?>"><?php echo esc_textarea( $snippet_text ); ?></textarea>
                            <div class="wiki-snippet-tools">
                                <span class="wiki-meta">
                                    <?php
                                    $snippet_updated = ! empty( $snippet['updated_at_display'] ) ? $snippet['updated_at_display'] : '';
                                    echo esc_html( $snippet_updated ? sprintf(
                                        /* translators: %s: snippet updated date */
                                        __( 'Updated %s', 'wikipedia' ),
                                        $snippet_updated
                                    ) : __( 'Saved snippet', 'wikipedia' ) );
                                    ?>
                                </span>
                                <button class="wiki-btn secondary" type="submit"><?php esc_html_e( 'Save snippet', 'wikipedia' ); ?></button>
                                <?php if ( ! empty( $snippet['edit_url'] ) ) : ?>
                                    <a class="wiki-btn secondary" href="<?php echo esc_url( $snippet['edit_url'] ); ?>"><?php esc_html_e( 'Edit post', 'wikipedia' ); ?></a>
                                <?php endif; ?>
                            </div>
                        </form>
                    <?php else : ?>
                        <blockquote><?php echo esc_html( $snippet_text ); ?></blockquote>
                    <?php endif; ?>
                </li>
            <?php endforeach; ?>
        </ol>
    </section>
<?php endif; ?>

<?php if ( $can_save_snippets ) : ?>
    <form class="wiki-selection-snippet" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" data-wiki-snippet-form hidden>
        <?php wp_nonce_field( App::NONCE_SAVE_SNIPPET ); ?>
        <input type="hidden" name="action" value="wikipedia_save_snippet">
        <input type="hidden" name="text" value="" data-wiki-snippet-text>
        <?php if ( $is_saved_view && ! empty( $article['post_id'] ) ) : ?>
            <input type="hidden" name="parent_post_id" value="<?php echo esc_attr( $article['post_id'] ); ?>">
        <?php else : ?>
            <input type="hidden" name="page_id" value="<?php echo esc_attr( $article['page_id'] ?? 0 ); ?>">
            <input type="hidden" name="title" value="<?php echo esc_attr( $article['title'] ?? '' ); ?>">
            <input type="hidden" name="language" value="<?php echo esc_attr( $article['language'] ?? '' ); ?>">
        <?php endif; ?>
        <div class="wiki-selection-snippet-preview" data-wiki-snippet-preview></div>
        <div class="wiki-selection-snippet-actions">
            <button class="wiki-btn" type="submit"><?php esc_html_e( 'Save snippet', 'wikipedia' ); ?></button>
            <button class="wiki-btn secondary wiki-icon-btn" type="button" data-wiki-snippet-cancel aria-label="<?php esc_attr_e( 'Cancel', 'wikipedia' ); ?>">&times;</button>
        </div>
    </form>
<?php endif; ?>

<article class="wiki-article" data-wiki-article-snippets>
    <?php echo wp_kses( $article_html, App::article_allowed_html() ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- article HTML is sanitized on output. ?>
</article>
