<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

use Akirk\Wikipedia\App;

$selected_languages = App::get_user_languages();
$supported_languages = App::get_supported_languages();
foreach ( $selected_languages as $code ) {
    if ( ! isset( $supported_languages[ $code ] ) ) {
        $supported_languages[ $code ] = App::get_language_label( $code );
    }
}

$page_title = __( 'Wikipedia settings', 'wikipedia' );
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
        <legend><?php esc_html_e( 'Search languages', 'wikipedia' ); ?></legend>
        <div class="wiki-language-options">
            <?php foreach ( $supported_languages as $code => $label ) : ?>
                <label>
                    <input type="checkbox" name="languages[]" value="<?php echo esc_attr( $code ); ?>" <?php checked( in_array( $code, $selected_languages, true ) ); ?>>
                    <span><?php echo esc_html( $label . ' (' . $code . ')' ); ?></span>
                </label>
            <?php endforeach; ?>
        </div>
    </fieldset>

    <p class="wiki-settings-actions">
        <button class="wiki-btn" type="submit"><?php esc_html_e( 'Save settings', 'wikipedia' ); ?></button>
    </p>
</form>
<?php include __DIR__ . '/_footer.php'; ?>
