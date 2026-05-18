<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

use Akirk\Wikipedia\App;

$current_language = isset( $language ) ? (string) $language : App::get_default_language();
$search_query = isset( $language_tabs_query ) ? (string) $language_tabs_query : '';
$preferred_languages = App::get_user_languages();
if ( ! in_array( $current_language, $preferred_languages, true ) ) {
    array_unshift( $preferred_languages, $current_language );
    $preferred_languages = App::normalize_language_list( $preferred_languages );
}
?>
<nav class="wiki-language-tabs" aria-label="<?php esc_attr_e( 'Search languages', 'wikipedia' ); ?>">
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
        <a class="<?php echo esc_attr( $code === $current_language ? 'is-active' : '' ); ?>" href="<?php echo esc_url( $url ); ?>">
            <?php echo esc_html( App::get_language_label( $code ) ); ?>
        </a>
    <?php endforeach; ?>
    <a href="<?php echo esc_url( App::get_settings_url() ); ?>"><?php esc_html_e( 'Edit', 'wikipedia' ); ?></a>
</nav>
