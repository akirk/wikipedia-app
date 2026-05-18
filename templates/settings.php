<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

use Akirk\Wikipedia\App;

$selected_languages = App::get_user_languages();
$selected_language_labels = [];
foreach ( $selected_languages as $code ) {
    $selected_language_labels[ $code ] = App::get_language_label( $code );
}

$page_title = __( 'Wordopedia settings', 'wikipedia' );
$wiki_current_nav = 'settings';
include __DIR__ . '/_header.php';
?>
<div class="wiki-page-head">
    <div>
        <h1><?php esc_html_e( 'Settings', 'wikipedia' ); ?></h1>
        <p class="wiki-subtitle"><?php esc_html_e( 'Choose the Wikipedia languages you search most often.', 'wikipedia' ); ?></p>
    </div>
</div>

<?php if ( isset( $_GET['settings_saved'] ) ) : ?>
    <div class="wiki-notice success"><?php esc_html_e( 'Settings saved.', 'wikipedia' ); ?></div>
<?php endif; ?>

<form class="wiki-settings-form" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
    <?php wp_nonce_field( App::NONCE_SAVE_SETTINGS ); ?>
    <input type="hidden" name="action" value="wikipedia_save_settings">

    <fieldset class="wiki-fieldset">
        <legend><?php esc_html_e( 'Language versions', 'wikipedia' ); ?></legend>
        <ol
            class="wiki-language-order"
            id="wiki-language-order"
            data-wiki-language-order
            data-move-up-text="<?php esc_attr_e( 'Move up', 'wikipedia' ); ?>"
            data-move-down-text="<?php esc_attr_e( 'Move down', 'wikipedia' ); ?>"
            data-remove-text="<?php esc_attr_e( 'Remove', 'wikipedia' ); ?>"
        >
            <?php foreach ( $selected_language_labels as $code => $label ) : ?>
                <li data-wiki-language-item data-language-code="<?php echo esc_attr( $code ); ?>">
                    <input type="hidden" name="languages[]" value="<?php echo esc_attr( $code ); ?>">
                    <span class="wiki-language-name"><?php echo esc_html( $label . ' (' . $code . ')' ); ?></span>
                    <button class="wiki-btn secondary wiki-icon-btn" type="button" data-wiki-language-move="up" aria-label="<?php echo esc_attr( sprintf( __( 'Move %s up', 'wikipedia' ), $label ) ); ?>" title="<?php esc_attr_e( 'Move up', 'wikipedia' ); ?>">&uarr;</button>
                    <button class="wiki-btn secondary wiki-icon-btn" type="button" data-wiki-language-move="down" aria-label="<?php echo esc_attr( sprintf( __( 'Move %s down', 'wikipedia' ), $label ) ); ?>" title="<?php esc_attr_e( 'Move down', 'wikipedia' ); ?>">&darr;</button>
                    <button class="wiki-btn secondary wiki-icon-btn" type="button" data-wiki-language-remove aria-label="<?php echo esc_attr( sprintf( __( 'Remove %s', 'wikipedia' ), $label ) ); ?>" title="<?php esc_attr_e( 'Remove', 'wikipedia' ); ?>">&times;</button>
                </li>
            <?php endforeach; ?>
        </ol>
        <div class="wiki-language-picker" data-wiki-language-picker data-language-list="wiki-language-order" data-loading-text="<?php esc_attr_e( 'Loading Wikipedia language versions...', 'wikipedia' ); ?>" data-empty-text="<?php esc_attr_e( 'No Wikipedia language versions found.', 'wikipedia' ); ?>">
            <label class="wiki-search-field">
                <input type="search" autocomplete="off" placeholder="<?php esc_attr_e( 'Search Wikipedia language versions', 'wikipedia' ); ?>" aria-label="<?php esc_attr_e( 'Search Wikipedia language versions', 'wikipedia' ); ?>" data-wiki-language-search>
            </label>
            <div class="wiki-language-results" data-wiki-language-results hidden></div>
        </div>
    </fieldset>

    <p class="wiki-settings-actions">
        <button class="wiki-btn" type="submit"><?php esc_html_e( 'Save settings', 'wikipedia' ); ?></button>
    </p>
</form>
<?php include __DIR__ . '/_footer.php'; ?>
