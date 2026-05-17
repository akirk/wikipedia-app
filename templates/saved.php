<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

use Akirk\Wikipedia\App;

global $wp_app_route;

$params = isset( $wp_app_route['params'] ) && is_array( $wp_app_route['params'] ) ? $wp_app_route['params'] : [];
$post = App::get_saved_article_from_route( $params['id'] ?? 0, $params['slug'] ?? '' );

if ( ! $post ) {
    status_header( 404 );
}

$article = $post ? App::format_saved_article( $post, true ) : null;
$page_title = $article ? $article['title'] : __( 'Saved Wikipedia article', 'wikipedia' );
include __DIR__ . '/_header.php';
?>
<?php if ( isset( $_GET['wikipedia_error'] ) ) : ?>
    <div class="wiki-notice error"><?php echo esc_html( sanitize_text_field( wp_unslash( $_GET['wikipedia_error'] ) ) ); ?></div>
<?php endif; ?>
<?php if ( isset( $_GET['saved'] ) ) : ?>
    <div class="wiki-notice success"><?php esc_html_e( 'Saved as a local source.', 'wikipedia' ); ?></div>
<?php endif; ?>
<?php if ( isset( $_GET['refetched'] ) ) : ?>
    <div class="wiki-notice success"><?php esc_html_e( 'Refetched from Wikipedia.', 'wikipedia' ); ?></div>
<?php endif; ?>

<?php if ( ! $article ) : ?>
    <div class="wiki-notice error"><?php esc_html_e( 'Saved article not found.', 'wikipedia' ); ?></div>
<?php else : ?>
    <div class="wiki-page-head">
        <div>
            <h1><?php echo esc_html( $article['title'] ); ?></h1>
            <p class="wiki-subtitle">
                <?php
                echo esc_html(
                    sprintf(
                        /* translators: 1: language label, 2: page ID */
                        __( 'Local source from %1$s Wikipedia, page %2$d', 'wikipedia' ),
                        $article['language_label'],
                        $article['page_id']
                    )
                );
                ?>
            </p>
        </div>
        <div class="wiki-actions">
            <a class="wiki-btn secondary" href="<?php echo esc_url( $article['live_app_url'] ); ?>"><?php esc_html_e( 'Open live', 'wikipedia' ); ?></a>
            <a class="wiki-btn secondary" href="<?php echo esc_url( $article['source_url'] ); ?>" target="_blank" rel="noreferrer"><?php esc_html_e( 'Wikipedia', 'wikipedia' ); ?></a>
            <?php if ( current_user_can( 'edit_posts' ) ) : ?>
                <form class="wiki-inline-form" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
                    <?php wp_nonce_field( App::NONCE_REFETCH_ARTICLE . '_' . $article['post_id'] ); ?>
                    <input type="hidden" name="action" value="wikipedia_refetch_article">
                    <input type="hidden" name="post_id" value="<?php echo esc_attr( $article['post_id'] ); ?>">
                    <button class="wiki-btn" type="submit"><?php esc_html_e( 'Refetch', 'wikipedia' ); ?></button>
                </form>
            <?php endif; ?>
        </div>
    </div>

    <div class="wiki-card">
        <div class="wiki-meta">
            <span><?php echo esc_html( $article['language_label'] . ' (' . $article['language'] . ')' ); ?></span>
            <?php if ( $article['last_revision_id'] ) : ?>
                <span><?php echo esc_html( __( 'Revision', 'wikipedia' ) . ' ' . $article['last_revision_id'] ); ?></span>
            <?php endif; ?>
            <?php if ( $article['remote_touched'] ) : ?>
                <span><?php echo esc_html( $article['remote_touched'] ); ?></span>
            <?php endif; ?>
            <?php if ( $article['refetched_at'] ) : ?>
                <span><?php echo esc_html( __( 'Refetched', 'wikipedia' ) . ': ' . $article['refetched_at'] ); ?></span>
            <?php endif; ?>
        </div>
    </div>

    <article class="wiki-article">
        <?php echo wp_kses( $article['content'], App::article_allowed_html() ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- saved content is re-sanitized before output. ?>
    </article>
<?php endif; ?>
<?php include __DIR__ . '/_footer.php'; ?>
