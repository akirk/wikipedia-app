<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

use Akirk\Wikipedia\App;

$wiki_saved_search = isset( $wiki_saved_search ) ? (string) $wiki_saved_search : '';
$wiki_saved_search_action = isset( $wiki_saved_search_action ) ? (string) $wiki_saved_search_action : App::get_saved_articles_url();
$wiki_saved_search_target = isset( $wiki_saved_search_target ) ? (string) $wiki_saved_search_target : 'wiki-saved-list';
?>
<form class="wiki-search wiki-compact-search" method="get" action="<?php echo esc_url( $wiki_saved_search_action ); ?>">
    <label class="wiki-search-field">
        <input type="search" name="s" value="<?php echo esc_attr( $wiki_saved_search ); ?>" autocomplete="off" placeholder="<?php esc_attr_e( 'Search saved articles', 'wikipedia' ); ?>" aria-label="<?php esc_attr_e( 'Search saved articles', 'wikipedia' ); ?>">
    </label>
    <button class="wiki-btn wiki-search-submit" type="submit"><?php esc_html_e( 'Search', 'wikipedia' ); ?></button>
</form>
