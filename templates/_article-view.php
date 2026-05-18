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
    $saved_date = ! empty( $article['saved_at'] ) ? $article['saved_at'] : ( $article['refetched_at'] ?? '' );
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
                </span>
            <?php endif; ?>
        </p>
    </div>
</div>

<article class="wiki-article">
    <?php echo wp_kses( $article_html, App::article_allowed_html() ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- article HTML is sanitized on output. ?>
</article>
