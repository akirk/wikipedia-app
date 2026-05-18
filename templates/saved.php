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
$wiki_current_nav = 'saved';
if ( $article ) {
    $wiki_article_actions = [
        'article'       => $article,
        'is_saved_view' => true,
    ];
}
include __DIR__ . '/_header.php';
?>
<?php if ( isset( $_GET['wikipedia_error'] ) ) : ?>
    <div class="wiki-notice error"><?php echo esc_html( sanitize_text_field( wp_unslash( $_GET['wikipedia_error'] ) ) ); ?></div>
<?php endif; ?>
<?php if ( isset( $_GET['saved'] ) ) : ?>
    <div class="wiki-notice success"><?php esc_html_e( 'Article saved.', 'wikipedia' ); ?></div>
<?php endif; ?>
<?php if ( isset( $_GET['refetched'] ) ) : ?>
    <div class="wiki-notice success"><?php esc_html_e( 'Refetched from Wikipedia.', 'wikipedia' ); ?></div>
<?php endif; ?>

<?php
$wiki_saved_search = '';
$wiki_saved_search_action = App::get_saved_articles_url();
include __DIR__ . '/_saved-search-form.php';
?>

<?php if ( ! $article ) : ?>
    <div class="wiki-notice error"><?php esc_html_e( 'Saved article not found.', 'wikipedia' ); ?></div>
<?php else : ?>
    <?php
    $is_saved_view = true;
    include __DIR__ . '/_article-view.php';
    ?>
<?php endif; ?>
<?php include __DIR__ . '/_footer.php'; ?>
