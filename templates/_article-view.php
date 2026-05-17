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
<div class="wiki-page-head">
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
    <div class="wiki-actions">
        <?php if ( ! $is_saved_view && ! empty( $article['available_languages'] ) ) : ?>
            <label class="wiki-language-switcher">
                <span><?php esc_html_e( 'Language', 'wikipedia' ); ?></span>
                <select onchange="if (this.value) window.location.href = this.value;">
                    <option value="<?php echo esc_url( $article['app_url'] ); ?>" selected><?php echo esc_html( $article['language_label'] . ' (' . $article['language'] . ')' ); ?></option>
                    <?php foreach ( $article['available_languages'] as $translation ) : ?>
                        <option value="<?php echo esc_url( $translation['app_url'] ); ?>">
                            <?php echo esc_html( $translation['language_label'] . ' (' . $translation['language'] . ')' ); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </label>
        <?php endif; ?>
        <?php if ( $is_saved_view && ! empty( $article['live_app_url'] ) ) : ?>
            <a class="wiki-btn secondary" href="<?php echo esc_url( $article['live_app_url'] ); ?>"><?php esc_html_e( 'Open live', 'wikipedia' ); ?></a>
        <?php endif; ?>
        <?php if ( ! empty( $article['source_url'] ) ) : ?>
            <a class="wiki-btn secondary" href="<?php echo esc_url( $article['source_url'] ); ?>" target="_blank" rel="noreferrer"><?php esc_html_e( 'Wikipedia', 'wikipedia' ); ?></a>
        <?php endif; ?>
        <?php if ( current_user_can( 'edit_posts' ) ) : ?>
            <?php if ( $is_saved_view && ! empty( $article['post_id'] ) ) : ?>
                <form class="wiki-inline-form" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
                    <?php wp_nonce_field( App::NONCE_REFETCH_ARTICLE . '_' . $article['post_id'] ); ?>
                    <input type="hidden" name="action" value="wikipedia_refetch_article">
                    <input type="hidden" name="post_id" value="<?php echo esc_attr( $article['post_id'] ); ?>">
                    <button class="wiki-btn" type="submit"><?php esc_html_e( 'Refetch', 'wikipedia' ); ?></button>
                </form>
            <?php elseif ( ! empty( $article['page_id'] ) ) : ?>
                <form class="wiki-inline-form" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
                    <?php wp_nonce_field( App::NONCE_SAVE_ARTICLE ); ?>
                    <input type="hidden" name="action" value="wikipedia_save_article">
                    <input type="hidden" name="page_id" value="<?php echo esc_attr( $article['page_id'] ); ?>">
                    <input type="hidden" name="title" value="<?php echo esc_attr( $article['title'] ); ?>">
                    <input type="hidden" name="language" value="<?php echo esc_attr( $article['language'] ); ?>">
                    <button class="wiki-btn" type="submit"><?php esc_html_e( 'Save article', 'wikipedia' ); ?></button>
                </form>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>

<article class="wiki-article">
    <?php echo wp_kses( $article_html, App::article_allowed_html() ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- article HTML is sanitized on output. ?>
</article>
