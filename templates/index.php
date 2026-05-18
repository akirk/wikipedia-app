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

$saved_posts = get_posts( [
    'post_type'      => App::POST_TYPE,
    'post_status'    => [ 'publish', 'draft', 'private' ],
    'posts_per_page' => -1,
    'orderby'        => 'title',
    'order'          => 'ASC',
] );

$saved_articles = [];
foreach ( $saved_posts as $saved_post ) {
    $saved_articles[] = App::format_saved_article( $saved_post );
}

$saved_articles_by_letter = [];
foreach ( $saved_articles as $saved ) {
    $title = trim( $saved['title'] );
    $letter = function_exists( 'mb_substr' ) ? mb_substr( $title, 0, 1 ) : substr( $title, 0, 1 );
    $letter = function_exists( 'mb_strtoupper' ) ? mb_strtoupper( $letter ) : strtoupper( $letter );
    if ( '' === $letter || ! preg_match( '/[[:alpha:]]/u', $letter ) ) {
        $letter = '#';
    }
    if ( ! isset( $saved_articles_by_letter[ $letter ] ) ) {
        $saved_articles_by_letter[ $letter ] = [];
    }
    $saved_articles_by_letter[ $letter ][] = $saved;
}
uksort( $saved_articles_by_letter, function( $a, $b ) {
    if ( '#' === $a ) {
        return 1;
    }
    if ( '#' === $b ) {
        return -1;
    }
    return strcasecmp( $a, $b );
} );

$page_title = __( 'Wikipedia', 'wikipedia' );
$wiki_current_nav = 'search';
include __DIR__ . '/_header.php';
?>
<?php if ( isset( $_GET['wikipedia_error'] ) ) : ?>
    <div class="wiki-notice error"><?php echo esc_html( sanitize_text_field( wp_unslash( $_GET['wikipedia_error'] ) ) ); ?></div>
<?php endif; ?>

<?php
$wiki_search_query = $query;
$wiki_search_language = $language;
include __DIR__ . '/_search-form.php';
?>
<?php
$language_tabs_query = $query;
$language_tabs_hidden = '' === trim( $query );
include __DIR__ . '/_search-language-tabs.php';
?>

<section class="wiki-search-results" id="wiki-search-results" data-wiki-quicksearch-results aria-live="polite">
    <?php if ( $search_error ) : ?>
        <div class="wiki-notice error"><?php echo esc_html( $search_error->get_error_message() ); ?></div>
    <?php elseif ( is_array( $results ) ) : ?>
        <?php if ( $results ) : ?>
            <ul class="wiki-results">
                <?php foreach ( $results as $result ) : ?>
                    <li class="wiki-result">
                        <h2><a href="<?php echo esc_url( $result['app_url'] ); ?>"><?php echo esc_html( $result['title'] ); ?></a></h2>
                        <div class="wiki-meta">
                            <span title="<?php echo esc_attr( $result['language_label'] ); ?>" aria-label="<?php echo esc_attr( $result['language_label'] . ' (' . $result['language'] . ')' ); ?>"><?php echo esc_html( $result['language'] ); ?></span>
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
    <?php endif; ?>
</section>

<section class="wiki-home-saved">
    <div class="wiki-section-head">
        <div>
            <h2><a href="<?php echo esc_url( App::get_saved_articles_url() ); ?>"><?php esc_html_e( 'Saved articles', 'wikipedia' ); ?></a></h2>
            <p class="wiki-subtitle">
                <?php
                echo esc_html(
                    sprintf(
                        /* translators: %d: number of saved articles */
                        _n( '%d article saved in WordPress.', '%d articles saved in WordPress.', count( $saved_articles ), 'wikipedia' ),
                        count( $saved_articles )
                    )
                );
                ?>
            </p>
        </div>
    </div>

    <?php if ( $saved_articles_by_letter ) : ?>
        <nav class="wiki-alpha-index" aria-label="<?php esc_attr_e( 'Saved article index', 'wikipedia' ); ?>">
            <?php foreach ( array_keys( $saved_articles_by_letter ) as $letter ) : ?>
                <?php $letter_id = '#' === $letter ? 'other' : sanitize_title( $letter ); ?>
                <a class="wiki-chip" href="#saved-<?php echo esc_attr( $letter_id ); ?>"><?php echo esc_html( $letter ); ?></a>
            <?php endforeach; ?>
        </nav>

        <?php foreach ( $saved_articles_by_letter as $letter => $group ) : ?>
            <?php $letter_id = '#' === $letter ? 'other' : sanitize_title( $letter ); ?>
            <section class="wiki-alpha-section" id="saved-<?php echo esc_attr( $letter_id ); ?>">
                <h3 class="wiki-alpha-heading"><?php echo esc_html( $letter ); ?></h3>
                <ul class="wiki-alpha-list">
                    <?php foreach ( $group as $saved ) : ?>
                        <li>
                            <a href="<?php echo esc_url( $saved['view_url'] ); ?>">
                                <span class="wiki-alpha-title"><?php echo esc_html( $saved['title'] ); ?></span>
                                <span class="wiki-meta">
                                    <span title="<?php echo esc_attr( $saved['language_label'] ); ?>" aria-label="<?php echo esc_attr( $saved['language_label'] . ' (' . $saved['language'] . ')' ); ?>"><?php echo esc_html( $saved['language'] ); ?></span>
                                    <?php foreach ( $saved['lists'] as $list ) : ?>
                                        <span><?php echo esc_html( $list['name'] ); ?></span>
                                    <?php endforeach; ?>
                                    <?php if ( ! empty( $saved['last_saved_at_display'] ) ) : ?>
                                        <span title="<?php echo esc_attr( __( 'Last saved', 'wikipedia' ) . ' ' . $saved['last_saved_at_display'] ); ?>" aria-label="<?php echo esc_attr( __( 'Last saved', 'wikipedia' ) . ' ' . $saved['last_saved_at_display'] ); ?>"><?php echo esc_html( $saved['last_saved_at_display'] ); ?></span>
                                    <?php endif; ?>
                                </span>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </section>
        <?php endforeach; ?>
    <?php else : ?>
        <div class="wiki-notice"><?php esc_html_e( 'No saved articles yet.', 'wikipedia' ); ?></div>
    <?php endif; ?>
</section>
<?php include __DIR__ . '/_footer.php'; ?>
