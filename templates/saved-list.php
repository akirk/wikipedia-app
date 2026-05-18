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
$language_input = isset( $_GET['language'] ) ? sanitize_text_field( wp_unslash( $_GET['language'] ) ) : '';
$language = '' !== $language_input ? App::normalize_language( $language_input ) : '';
if ( is_wp_error( $language ) ) {
    $language = '';
}

$saved_articles = App::list_saved_articles( $search, 50, $language, $list_slug );
$languages = App::get_supported_languages();
if ( '' !== $language && ! isset( $languages[ $language ] ) ) {
    $languages[ $language ] = App::get_language_label( $language );
}
$lists = get_terms( [
    'taxonomy'   => App::TAX_LIST,
    'hide_empty' => true,
] );
if ( is_wp_error( $lists ) ) {
    $lists = [];
}

$page_title = $current_list ? $current_list->name : __( 'Saved articles', 'wikipedia' );
include __DIR__ . '/_header.php';
?>
<div class="wiki-page-head">
    <div>
        <h1><?php echo esc_html( $current_list ? $current_list->name : __( 'Saved articles', 'wikipedia' ) ); ?></h1>
        <p class="wiki-subtitle"><?php esc_html_e( 'Articles saved in WordPress.', 'wikipedia' ); ?></p>
    </div>
</div>

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

<form class="wiki-search wiki-compact-search" method="get" action="<?php echo esc_url( $current_list ? App::get_list_url( $current_list ) : App::get_saved_articles_url() ); ?>">
    <label class="wiki-search-field">
        <span><?php esc_html_e( 'Search saved articles', 'wikipedia' ); ?></span>
        <input type="search" name="s" value="<?php echo esc_attr( $search ); ?>" autocomplete="off" placeholder="<?php esc_attr_e( 'Search saved articles', 'wikipedia' ); ?>">
    </label>
    <label class="wiki-search-language">
        <span><?php esc_html_e( 'Language', 'wikipedia' ); ?></span>
        <select name="language">
            <option value=""><?php esc_html_e( 'All languages', 'wikipedia' ); ?></option>
            <?php foreach ( $languages as $code => $label ) : ?>
                <option value="<?php echo esc_attr( $code ); ?>" <?php selected( $language, $code ); ?>><?php echo esc_html( $label . ' (' . $code . ')' ); ?></option>
            <?php endforeach; ?>
        </select>
    </label>
    <button class="wiki-btn wiki-search-submit" type="submit"><?php esc_html_e( 'Filter', 'wikipedia' ); ?></button>
</form>

<?php if ( $saved_articles ) : ?>
    <ul class="wiki-saved-list wiki-saved-list-full">
        <?php foreach ( $saved_articles as $saved ) : ?>
            <li class="wiki-saved-item">
                <h2><a href="<?php echo esc_url( $saved['view_url'] ); ?>"><?php echo esc_html( $saved['title'] ); ?></a></h2>
                <div class="wiki-meta">
                    <span><?php echo esc_html( $saved['language_label'] . ' (' . $saved['language'] . ')' ); ?></span>
                    <?php foreach ( $saved['lists'] as $list ) : ?>
                        <span><a href="<?php echo esc_url( $list['view_url'] ); ?>"><?php echo esc_html( $list['name'] ); ?></a></span>
                    <?php endforeach; ?>
                    <?php if ( $saved['saved_at'] ) : ?>
                        <span><?php echo esc_html( __( 'Saved', 'wikipedia' ) . ': ' . $saved['saved_at'] ); ?></span>
                    <?php endif; ?>
                    <?php if ( $saved['refetched_at'] ) : ?>
                        <span><?php echo esc_html( __( 'Refetched', 'wikipedia' ) . ': ' . $saved['refetched_at'] ); ?></span>
                    <?php endif; ?>
                </div>
                <?php if ( $saved['summary'] ) : ?>
                    <p><?php echo esc_html( $saved['summary'] ); ?></p>
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
