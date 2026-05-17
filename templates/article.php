<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

use Akirk\Wikipedia\App;

global $wp_app_route;

$route_language = isset( $wp_app_route['params']['language'] ) ? sanitize_text_field( $wp_app_route['params']['language'] ) : App::get_default_language();
$language = App::normalize_language( $route_language );
if ( is_wp_error( $language ) ) {
    $language = App::get_default_language();
}

// phpcs:disable WordPress.Security.NonceVerification.Recommended -- article lookup is read-only.
$title = isset( $_GET['title'] ) ? sanitize_text_field( wp_unslash( $_GET['title'] ) ) : '';
$page_id = isset( $_GET['page_id'] ) ? absint( wp_unslash( $_GET['page_id'] ) ) : 0;
$article = null;
$error = null;

if ( $page_id || '' !== $title ) {
    $article = App::fetch_wikipedia_article( [
        'page_id'  => $page_id,
        'title'    => $title,
        'language' => $language,
    ] );

    if ( is_wp_error( $article ) ) {
        $error = $article;
        $article = null;
    }
}

$page_title = $article ? $article['title'] : __( 'Wikipedia article', 'wikipedia' );
include __DIR__ . '/_header.php';
?>
<form class="wiki-search" method="get" action="<?php echo esc_url( App::get_app_url() ); ?>">
    <label>
        <span><?php esc_html_e( 'Search', 'wikipedia' ); ?></span>
        <input type="search" name="q" value="<?php echo esc_attr( $title ); ?>" autocomplete="off">
    </label>
    <label>
        <span><?php esc_html_e( 'Language', 'wikipedia' ); ?></span>
        <select name="language">
            <?php foreach ( App::get_supported_languages() as $code => $label ) : ?>
                <option value="<?php echo esc_attr( $code ); ?>" <?php selected( $language, $code ); ?>><?php echo esc_html( $label . ' (' . $code . ')' ); ?></option>
            <?php endforeach; ?>
        </select>
    </label>
    <button class="wiki-btn" type="submit"><?php esc_html_e( 'Search', 'wikipedia' ); ?></button>
</form>

<?php if ( isset( $_GET['wikipedia_error'] ) ) : ?>
    <div class="wiki-notice error"><?php echo esc_html( sanitize_text_field( wp_unslash( $_GET['wikipedia_error'] ) ) ); ?></div>
<?php endif; ?>

<?php if ( $error ) : ?>
    <div class="wiki-notice error"><?php echo esc_html( $error->get_error_message() ); ?></div>
<?php elseif ( ! $article ) : ?>
    <div class="wiki-notice"><?php esc_html_e( 'Choose an article from search results.', 'wikipedia' ); ?></div>
<?php else : ?>
    <?php
    $is_saved_view = false;
    include __DIR__ . '/_article-view.php';
    ?>
<?php endif; ?>
<?php include __DIR__ . '/_footer.php'; ?>
