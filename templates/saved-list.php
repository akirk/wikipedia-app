<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

use Akirk\Wikipedia\App;

global $wp_app_route;

$params = isset( $wp_app_route['params'] ) && is_array( $wp_app_route['params'] ) ? $wp_app_route['params'] : [];
$list_slug = isset( $params['slug'] ) ? sanitize_title( $params['slug'] ) : '';
$current_list = $list_slug ? get_term_by( 'slug', $list_slug, App::TAX_LIST ) : null;
if ( $current_list && is_wp_error( $current_list ) ) {
    $current_list = null;
}

// phpcs:disable WordPress.Security.NonceVerification.Recommended -- saved article filtering is read-only.
$search = isset( $_GET['s'] ) ? sanitize_text_field( wp_unslash( $_GET['s'] ) ) : '';

$saved_articles = App::list_saved_articles( $search, 50, '', $list_slug );
$lists = get_terms( [
    'taxonomy'   => App::TAX_LIST,
    'hide_empty' => true,
] );
if ( is_wp_error( $lists ) ) {
    $lists = [];
}

$page_title = $current_list ? $current_list->name : __( 'Saved articles', 'wikipedia' );
$wiki_current_nav = 'saved';
include __DIR__ . '/_header.php';
?>
<?php if ( isset( $_GET['wikipedia_error'] ) ) : ?>
    <div class="wiki-notice error"><?php echo esc_html( sanitize_text_field( wp_unslash( $_GET['wikipedia_error'] ) ) ); ?></div>
<?php endif; ?>

<?php if ( $lists ) : ?>
    <nav class="wiki-list-tabs" aria-label="<?php esc_attr_e( 'Saved article lists', 'wikipedia' ); ?>">
        <a class="<?php echo esc_attr( '' === $list_slug ? 'is-active' : '' ); ?>" href="<?php echo esc_url( App::get_saved_articles_url() ); ?>"><?php esc_html_e( 'All', 'wikipedia' ); ?></a>
        <?php foreach ( $lists as $list ) : ?>
            <a class="<?php echo esc_attr( $list->slug === $list_slug ? 'is-active' : '' ); ?>" href="<?php echo esc_url( App::get_list_url( $list ) ); ?>"><?php echo esc_html( $list->name ); ?></a>
        <?php endforeach; ?>
    </nav>
<?php endif; ?>

<?php
$wiki_saved_search = $search;
$wiki_saved_search_action = $current_list ? App::get_list_url( $current_list ) : App::get_saved_articles_url();
include __DIR__ . '/_saved-search-form.php';
?>

<?php if ( $saved_articles ) : ?>
    <ul class="wiki-saved-list wiki-saved-list-full" id="wiki-saved-list">
        <?php foreach ( $saved_articles as $saved ) : ?>
            <li class="wiki-saved-item">
                <h2><a href="<?php echo esc_url( $saved['view_url'] ); ?>"><?php echo esc_html( $saved['title'] ); ?></a></h2>
                <div class="wiki-meta">
                    <span title="<?php echo esc_attr( $saved['language_label'] ); ?>" aria-label="<?php echo esc_attr( $saved['language_label'] . ' (' . $saved['language'] . ')' ); ?>"><?php echo esc_html( $saved['language'] ); ?></span>
                    <?php foreach ( $saved['lists'] as $list ) : ?>
                        <span><a href="<?php echo esc_url( $list['view_url'] ); ?>"><?php echo esc_html( $list['name'] ); ?></a></span>
                    <?php endforeach; ?>
                    <?php if ( ! empty( $saved['last_saved_at_display'] ) ) : ?>
                        <span><?php echo esc_html( __( 'Last saved', 'wikipedia' ) . ' ' . $saved['last_saved_at_display'] ); ?></span>
                    <?php endif; ?>
                </div>
                <?php if ( ! empty( $saved['search_snippet'] ) ) : ?>
                    <p class="wiki-saved-snippet"><?php echo wp_kses( $saved['search_snippet'], [ 'mark' => [] ] ); ?></p>
                <?php elseif ( $saved['summary'] ) : ?>
                    <p class="wiki-saved-snippet"><?php echo esc_html( $saved['summary'] ); ?></p>
                <?php endif; ?>
                <div class="wiki-article-tools">
                    <a class="wiki-btn secondary" href="<?php echo esc_url( $saved['view_url'] ); ?>"><?php esc_html_e( 'Read saved', 'wikipedia' ); ?></a>
                    <a class="wiki-btn secondary" href="<?php echo esc_url( $saved['live_app_url'] ); ?>"><?php esc_html_e( 'Open live', 'wikipedia' ); ?></a>
                    <?php if ( current_user_can( 'edit_posts' ) ) : ?>
                        <form class="wiki-inline-form" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
                            <?php wp_nonce_field( App::NONCE_REFETCH_ARTICLE . '_' . $saved['post_id'] ); ?>
                            <input type="hidden" name="action" value="wikipedia_refetch_article">
                            <input type="hidden" name="post_id" value="<?php echo esc_attr( $saved['post_id'] ); ?>">
                            <button class="wiki-btn secondary" type="submit"><?php esc_html_e( 'Refetch', 'wikipedia' ); ?></button>
                        </form>
                    <?php endif; ?>
                </div>
            </li>
        <?php endforeach; ?>
    </ul>
<?php else : ?>
    <div class="wiki-notice"><?php esc_html_e( 'No saved articles found.', 'wikipedia' ); ?></div>
<?php endif; ?>
<?php include __DIR__ . '/_footer.php'; ?>
