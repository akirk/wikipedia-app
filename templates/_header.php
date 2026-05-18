<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$wiki_current_nav = isset( $wiki_current_nav ) ? sanitize_key( $wiki_current_nav ) : '';
?>
<!DOCTYPE html>
<html <?php wp_app_language_attributes(); ?>>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo wp_app_title( isset( $page_title ) ? $page_title : __( 'Wikipedia', 'wikipedia' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- wp_app_title escapes. ?></title>
    <?php wp_app_head(); ?>
</head>
<body>
<?php wp_app_body_open(); ?>
<main class="wiki-shell">
    <nav class="wiki-topbar" aria-label="<?php esc_attr_e( 'Wikipedia app', 'wikipedia' ); ?>">
        <a class="wiki-brand" href="<?php echo esc_url( \Akirk\Wikipedia\App::get_app_url() ); ?>">
            <span class="wiki-brand-mark" aria-hidden="true">W</span>
            <span class="wiki-brand-text">
                <span class="wiki-wordmark"><?php esc_html_e( 'Wikipedia', 'wikipedia' ); ?></span>
                <span class="wiki-tagline"><?php esc_html_e( 'Inside WordPress', 'wikipedia' ); ?></span>
            </span>
        </a>
        <div class="wiki-nav">
            <a class="<?php echo esc_attr( 'search' === $wiki_current_nav ? 'is-active' : '' ); ?>" <?php echo 'search' === $wiki_current_nav ? 'aria-current="page"' : ''; ?> href="<?php echo esc_url( \Akirk\Wikipedia\App::get_app_url() ); ?>"><?php esc_html_e( 'Search', 'wikipedia' ); ?></a>
            <a class="<?php echo esc_attr( 'saved' === $wiki_current_nav ? 'is-active' : '' ); ?>" <?php echo 'saved' === $wiki_current_nav ? 'aria-current="page"' : ''; ?> href="<?php echo esc_url( \Akirk\Wikipedia\App::get_saved_articles_url() ); ?>"><?php esc_html_e( 'Saved articles', 'wikipedia' ); ?></a>
            <a class="<?php echo esc_attr( 'settings' === $wiki_current_nav ? 'is-active' : '' ); ?>" <?php echo 'settings' === $wiki_current_nav ? 'aria-current="page"' : ''; ?> href="<?php echo esc_url( \Akirk\Wikipedia\App::get_settings_url() ); ?>"><?php esc_html_e( 'Settings', 'wikipedia' ); ?></a>
            <?php if ( ! empty( $wiki_article_actions ) && is_array( $wiki_article_actions ) ) : ?>
                <details class="wiki-nav-menu">
                    <summary aria-label="<?php esc_attr_e( 'Article actions', 'wikipedia' ); ?>"><span aria-hidden="true">☰</span></summary>
                    <div class="wiki-nav-menu-panel">
                        <?php
                        $article = isset( $wiki_article_actions['article'] ) && is_array( $wiki_article_actions['article'] ) ? $wiki_article_actions['article'] : [];
                        $is_saved_view = ! empty( $wiki_article_actions['is_saved_view'] );
                        include __DIR__ . '/_article-actions.php';
                        ?>
                    </div>
                </details>
            <?php endif; ?>
        </div>
    </nav>
    <div class="wiki-content">
