<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

use Akirk\Wikipedia\App;

$article = isset( $article ) && is_array( $article ) ? $article : [];
$article_html = isset( $article['html'] ) ? $article['html'] : ( $article['content'] ?? '' );
$is_saved_view = ! empty( $is_saved_view );
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

<article class="wiki-article">
    <?php echo wp_kses( $article_html, App::article_allowed_html() ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- article HTML is sanitized on output. ?>
</article>
