<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

use Akirk\Wikipedia\App;

// phpcs:disable WordPress.Security.NonceVerification.Recommended -- search and filters are read-only.
$query = isset( $_GET['q'] ) ? sanitize_text_field( wp_unslash( $_GET['q'] ) ) : '';
$language_input = isset( $_GET['language'] ) ? sanitize_text_field( wp_unslash( $_GET['language'] ) ) : App::get_default_language();
$language = App::normalize_language( $language_input );
if ( is_wp_error( $language ) ) {
    $language = App::get_default_language();
}

$results = null;
$search_error = null;
if ( '' !== $query ) {
    $results = App::search_wikipedia_articles( $query, $language, 12 );
    if ( is_wp_error( $results ) ) {
        $search_error = $results;
        $results = [];
    }
}

$languages = App::get_supported_languages();
if ( ! isset( $languages[ $language ] ) ) {
    $languages[ $language ] = App::get_language_label( $language );
}

$page_title = __( 'Wikipedia', 'wikipedia' );
include __DIR__ . '/_header.php';
?>
<div class="wiki-page-head">
    <div>
        <h1><?php esc_html_e( 'Wikipedia', 'wikipedia' ); ?></h1>
        <p class="wiki-subtitle"><?php esc_html_e( 'Search, read, follow links, switch article languages, and save articles.', 'wikipedia' ); ?></p>
    </div>
    <div class="wiki-actions">
        <a class="wiki-btn secondary" href="<?php echo esc_url( App::get_saved_articles_url() ); ?>"><?php esc_html_e( 'Saved articles', 'wikipedia' ); ?></a>
    </div>
</div>

<?php if ( isset( $_GET['wikipedia_error'] ) ) : ?>
    <div class="wiki-notice error"><?php echo esc_html( sanitize_text_field( wp_unslash( $_GET['wikipedia_error'] ) ) ); ?></div>
<?php endif; ?>

<form class="wiki-search" method="get" action="<?php echo esc_url( App::get_app_url() ); ?>">
    <label>
        <span><?php esc_html_e( 'Search', 'wikipedia' ); ?></span>
        <input type="search" name="q" value="<?php echo esc_attr( $query ); ?>" autocomplete="off">
    </label>
    <label>
        <span><?php esc_html_e( 'Language', 'wikipedia' ); ?></span>
        <select name="language">
            <?php foreach ( $languages as $code => $label ) : ?>
                <option value="<?php echo esc_attr( $code ); ?>" <?php selected( $language, $code ); ?>><?php echo esc_html( $label . ' (' . $code . ')' ); ?></option>
            <?php endforeach; ?>
        </select>
    </label>
    <button class="wiki-btn" type="submit"><?php esc_html_e( 'Search', 'wikipedia' ); ?></button>
</form>

<section>
    <?php if ( $search_error ) : ?>
        <div class="wiki-notice error"><?php echo esc_html( $search_error->get_error_message() ); ?></div>
    <?php elseif ( is_array( $results ) ) : ?>
        <h2>
            <?php
            echo esc_html(
                sprintf(
                    /* translators: 1: search query, 2: language label */
                    __( 'Results for "%1$s" in %2$s', 'wikipedia' ),
                    $query,
                    App::get_language_label( $language )
                )
            );
            ?>
        </h2>
        <?php if ( $results ) : ?>
            <ul class="wiki-results">
                <?php foreach ( $results as $result ) : ?>
                    <li class="wiki-result">
                        <h2><a href="<?php echo esc_url( $result['app_url'] ); ?>"><?php echo esc_html( $result['title'] ); ?></a></h2>
                        <div class="wiki-meta">
                            <span><?php echo esc_html( $result['language_label'] . ' (' . $result['language'] . ')' ); ?></span>
                            <?php if ( ! empty( $result['word_count'] ) ) : ?>
                                <span><?php echo esc_html( number_format_i18n( $result['word_count'] ) . ' ' . __( 'words', 'wikipedia' ) ); ?></span>
                            <?php endif; ?>
                        </div>
                        <?php if ( ! empty( $result['snippet'] ) ) : ?>
                            <p><?php echo esc_html( $result['snippet'] ); ?></p>
                        <?php endif; ?>
                        <div class="wiki-article-tools">
                            <a class="wiki-btn secondary" href="<?php echo esc_url( $result['app_url'] ); ?>"><?php esc_html_e( 'Read', 'wikipedia' ); ?></a>
                            <?php if ( current_user_can( 'edit_posts' ) ) : ?>
                                <form class="wiki-inline-form" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
                                    <?php wp_nonce_field( App::NONCE_SAVE_ARTICLE ); ?>
                                    <input type="hidden" name="action" value="wikipedia_save_article">
                                    <input type="hidden" name="page_id" value="<?php echo esc_attr( $result['page_id'] ); ?>">
                                    <input type="hidden" name="title" value="<?php echo esc_attr( $result['title'] ); ?>">
                                    <input type="hidden" name="language" value="<?php echo esc_attr( $result['language'] ); ?>">
                                    <button class="wiki-btn secondary" type="submit"><?php esc_html_e( 'Save article', 'wikipedia' ); ?></button>
                                </form>
                            <?php endif; ?>
                        </div>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php else : ?>
            <div class="wiki-notice"><?php esc_html_e( 'No Wikipedia results found.', 'wikipedia' ); ?></div>
        <?php endif; ?>
    <?php else : ?>
        <div class="wiki-card">
            <h2><?php esc_html_e( 'Start with a search', 'wikipedia' ); ?></h2>
            <p><?php esc_html_e( 'The search language defaults to your WordPress profile language.', 'wikipedia' ); ?></p>
        </div>
    <?php endif; ?>
</section>
<?php include __DIR__ . '/_footer.php'; ?>
