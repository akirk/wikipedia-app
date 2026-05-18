<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

use Akirk\Wikipedia\App;

$current_language = isset( $language ) ? (string) $language : App::get_default_language();
$search_query = isset( $language_tabs_query ) ? (string) $language_tabs_query : '';
$tabs_hidden = ! empty( $language_tabs_hidden );
$tabs_article = isset( $language_tabs_article ) && is_array( $language_tabs_article ) ? $language_tabs_article : [];
$preferred_languages = App::get_user_languages();
if ( ! in_array( $current_language, $preferred_languages, true ) ) {
    array_unshift( $preferred_languages, $current_language );
    $preferred_languages = App::normalize_language_list( $preferred_languages );
}
?>
<nav class="wiki-language-tabs" id="wiki-search-language-tabs" data-wiki-language-tabs aria-label="<?php esc_attr_e( 'Search languages', 'wikipedia' ); ?>" <?php echo $tabs_hidden ? 'hidden' : ''; ?>>
    <?php foreach ( $preferred_languages as $code ) : ?>
        <?php
        $url = add_query_arg(
            [
                'q'        => $search_query,
                'language' => $code,
            ],
            App::get_app_url()
        );
        ?>
        <a class="<?php echo esc_attr( $code === $current_language ? 'is-active' : '' ); ?>" href="<?php echo esc_url( $url ); ?>" data-wiki-language="<?php echo esc_attr( $code ); ?>">
            <?php echo esc_html( App::get_language_label( $code ) ); ?>
        </a>
    <?php endforeach; ?>
    <a href="<?php echo esc_url( App::get_settings_url() ); ?>"><?php esc_html_e( 'Edit', 'wikipedia' ); ?></a>
    <?php if ( $tabs_article && ( ! empty( $tabs_article['available_languages'] ) || ( current_user_can( 'edit_posts' ) && ! empty( $tabs_article['page_id'] ) ) ) ) : ?>
        <span class="wiki-language-tab-actions">
            <?php if ( ! empty( $tabs_article['available_languages'] ) ) : ?>
                <select class="wiki-language-tab-select" aria-label="<?php esc_attr_e( 'Other article languages', 'wikipedia' ); ?>" onchange="if (this.value) window.location.href = this.value;">
                    <option value="" selected><?php esc_html_e( 'Other languages', 'wikipedia' ); ?></option>
                    <?php foreach ( $tabs_article['available_languages'] as $translation ) : ?>
                        <option value="<?php echo esc_url( $translation['app_url'] ); ?>">
                            <?php echo esc_html( $translation['language_label'] . ' (' . $translation['language'] . ')' ); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            <?php endif; ?>
            <?php if ( current_user_can( 'edit_posts' ) && ! empty( $tabs_article['page_id'] ) ) : ?>
                <form class="wiki-inline-form" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
                    <?php wp_nonce_field( App::NONCE_SAVE_ARTICLE ); ?>
                    <input type="hidden" name="action" value="wikipedia_save_article">
                    <input type="hidden" name="page_id" value="<?php echo esc_attr( $tabs_article['page_id'] ); ?>">
                    <input type="hidden" name="title" value="<?php echo esc_attr( $tabs_article['title'] ); ?>">
                    <input type="hidden" name="language" value="<?php echo esc_attr( $tabs_article['language'] ); ?>">
                    <button class="wiki-btn" type="submit"><?php esc_html_e( 'Save article', 'wikipedia' ); ?></button>
                </form>
            <?php endif; ?>
        </span>
    <?php endif; ?>
</nav>
