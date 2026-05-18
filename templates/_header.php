<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>
<!DOCTYPE html>
<html <?php wp_app_language_attributes(); ?>>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo wp_app_title( isset( $page_title ) ? $page_title : __( 'Wikipedia', 'wikipedia' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- wp_app_title escapes. ?></title>
    <?php wp_app_head(); ?>
    <style>
        :root {
            color-scheme: light dark;
            --wiki-bg: var(--wp-app-color-background, #fff);
            --wiki-fg: var(--wp-app-color-text, #202122);
            --wiki-muted: var(--wp-app-color-muted, #54595d);
            --wiki-line: var(--wp-app-color-border, #a2a9b1);
            --wiki-line-soft: #eaecf0;
            --wiki-card: var(--wp-app-color-surface, #fff);
            --wiki-card-alt: var(--wp-app-color-surface-alt, #f8f9fa);
            --wiki-primary: var(--wp-app-color-primary, #36c);
            --wiki-primary-hover: var(--wp-app-color-primary-hover, #2a4b8d);
            --wiki-focus: var(--wp-app-color-focus, #36c);
            --wiki-success-bg: #f1f8f1;
            --wiki-success-line: #8cb58c;
            --wiki-error-bg: #fff2f2;
            --wiki-error-line: #d33;
            --wiki-control-height: 2.25rem;
            --wiki-control-pad-x: 0.65rem;
            --wiki-serif: Georgia, "Times New Roman", Times, serif;
            --wiki-sans: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Lato, Helvetica, Arial, sans-serif;
        }
        :root[data-theme="dark"] {
            --wiki-bg: var(--wp-app-color-background, #101418);
            --wiki-fg: var(--wp-app-color-text, #eaecf0);
            --wiki-muted: var(--wp-app-color-muted, #a2a9b1);
            --wiki-line: var(--wp-app-color-border, #54595d);
            --wiki-line-soft: #33373d;
            --wiki-card: var(--wp-app-color-surface, #161b22);
            --wiki-card-alt: var(--wp-app-color-surface-alt, #1f242b);
            --wiki-primary: var(--wp-app-color-primary, #6b9cff);
            --wiki-primary-hover: var(--wp-app-color-primary-hover, #9bbcff);
            --wiki-success-bg: #172b1b;
            --wiki-success-line: #3f6b42;
            --wiki-error-bg: #351f1f;
            --wiki-error-line: #a34b4b;
        }
        body { margin: 0; background: var(--wiki-bg); color: var(--wiki-fg); font-family: var(--wiki-sans); font-size: 15px; line-height: 1.6; }
        a { color: var(--wiki-primary); text-decoration: none; }
        a:hover, a:focus { color: var(--wiki-primary-hover); text-decoration: underline; }
        .wiki-shell { max-width: 1180px; margin: 0 auto; padding: 0.75rem 1.35rem 4rem; }
        .wiki-topbar { display: flex; align-items: center; justify-content: space-between; gap: 1rem; margin: 0 0 1.1rem; padding: 0.2rem 0 0.65rem; border-bottom: 1px solid var(--wiki-line-soft); }
        .wiki-brand { display: inline-flex; align-items: center; gap: 0.55rem; color: var(--wiki-fg); text-decoration: none; }
        .wiki-brand:hover, .wiki-brand:focus { color: var(--wiki-fg); text-decoration: none; }
        .wiki-brand-mark { display: inline-flex; align-items: center; justify-content: center; width: 2.2rem; height: 2.2rem; border: 1px solid var(--wiki-line); border-radius: 50%; font-family: var(--wiki-serif); font-size: 1.45rem; background: var(--wiki-card-alt); }
        .wiki-wordmark { display: block; font-family: var(--wiki-serif); font-size: 1.35rem; line-height: 1; }
        .wiki-tagline { display: block; margin-top: 0.1rem; color: var(--wiki-muted); font-size: 0.72rem; line-height: 1; }
        .wiki-nav { display: flex; align-items: flex-end; justify-content: flex-end; gap: 0.85rem; flex-wrap: wrap; }
        .wiki-nav a { display: inline-flex; align-items: center; min-height: 2rem; padding: 0.2rem 0 0.35rem; border-bottom: 2px solid transparent; color: var(--wiki-primary); font-size: 0.92rem; text-decoration: none; }
        .wiki-nav a:hover, .wiki-nav a:focus { border-bottom-color: var(--wiki-primary); color: var(--wiki-primary-hover); text-decoration: none; }
        .wiki-nav-menu { position: relative; min-height: 2rem; }
        .wiki-nav-menu summary { display: inline-flex; align-items: center; justify-content: center; min-width: 2rem; min-height: 2rem; border-bottom: 2px solid transparent; color: var(--wiki-primary); cursor: pointer; list-style: none; }
        .wiki-nav-menu summary::-webkit-details-marker { display: none; }
        .wiki-nav-menu summary:hover, .wiki-nav-menu summary:focus { border-bottom-color: var(--wiki-primary); color: var(--wiki-primary-hover); }
        .wiki-nav-menu-panel { position: absolute; z-index: 30; top: calc(100% + 0.2rem); right: 0; display: grid; gap: 0.35rem; min-width: 12rem; padding: 0.45rem; border: 1px solid var(--wiki-line); background: var(--wiki-card); box-shadow: 0 0.25rem 0.65rem rgba(0, 0, 0, 0.12); }
        .wiki-nav-menu-panel .wiki-btn, .wiki-nav-menu-panel button.wiki-btn, .wiki-nav-menu-panel .wiki-language-switcher { width: 100%; }
        .wiki-nav-menu-panel .wiki-language-switcher select { flex: 1; max-width: none; }
        .wiki-page-head { display: flex; gap: 1rem; justify-content: space-between; align-items: flex-end; margin: 0 0 0.85rem; padding-bottom: 0.35rem; border-bottom: 1px solid var(--wiki-line); }
        .wiki-page-head h1 { margin: 0 0 0.15rem; font-family: var(--wiki-serif); font-size: clamp(1.85rem, 4vw, 2.45rem); font-weight: 400; line-height: 1.18; letter-spacing: 0; }
        .wiki-article-head { display: block; margin-bottom: 0.8rem; }
        .wiki-subtitle { margin: 0; color: var(--wiki-muted); font-size: 0.92rem; }
        .wiki-saved-status { display: inline-flex; align-items: center; gap: 0.25rem; margin-left: 0.55rem; color: var(--wiki-fg); font-weight: 600; }
        .wiki-actions { display: flex; gap: 0.3rem; flex-wrap: wrap; justify-content: flex-end; }
        .wiki-language-switcher { display: inline-flex; align-items: center; height: var(--wiki-control-height); border: 1px solid var(--wiki-line); border-radius: 2px; background: var(--wiki-card-alt); overflow: hidden; box-sizing: border-box; }
        .wiki-language-switcher span { display: inline-flex; align-items: center; align-self: stretch; padding: 0 var(--wiki-control-pad-x); color: var(--wiki-muted); font-size: 0.82rem; font-weight: 600; border-right: 1px solid var(--wiki-line); white-space: nowrap; }
        .wiki-language-switcher select { width: auto; min-width: 10rem; max-width: 16rem; height: calc(var(--wiki-control-height) - 2px); min-height: 0; border: 0; border-radius: 0; background: transparent; font-weight: 500; }
        .wiki-search { display: grid; grid-template-columns: minmax(0, 1fr) minmax(8rem, 12rem) auto; gap: 0.5rem; align-items: end; margin: 1rem 0 1.35rem; padding: 0.65rem; border: 1px solid var(--wiki-line); background: var(--wiki-card-alt); }
        .wiki-search label { display: grid; gap: 0.25rem; margin: 0; font-weight: 600; }
        .wiki-search span { color: var(--wiki-muted); font-size: 0.82rem; font-weight: 500; }
        .wiki-search-field { position: relative; }
        input[type="search"], input[type="text"], select {
            width: 100%; height: var(--wiki-control-height); min-height: var(--wiki-control-height); box-sizing: border-box; padding: 0 var(--wiki-control-pad-x); border: 1px solid var(--wiki-line); border-radius: 2px; background: var(--wiki-card); color: var(--wiki-fg); font: inherit; line-height: 1.2;
        }
        input[type="search"], input[type="text"] { appearance: none; }
        .wiki-autocomplete { position: absolute; z-index: 20; left: 0; right: 0; top: calc(100% + 0.2rem); border: 1px solid var(--wiki-line); background: var(--wiki-card); box-shadow: 0 0.25rem 0.65rem rgba(0, 0, 0, 0.12); font-weight: 400; }
        .wiki-autocomplete[hidden] { display: none; }
        .wiki-autocomplete-option { display: block; width: 100%; box-sizing: border-box; padding: 0.42rem 0.65rem; border: 0; border-bottom: 1px solid var(--wiki-line-soft); background: transparent; color: var(--wiki-fg); font: inherit; line-height: 1.25; text-align: left; cursor: pointer; }
        .wiki-autocomplete-option:last-child { border-bottom: 0; }
        .wiki-autocomplete-option:hover, .wiki-autocomplete-option:focus, .wiki-autocomplete-option.is-active { background: var(--wiki-card-alt); color: var(--wiki-primary); outline: 0; }
        input:focus, select:focus, button:focus, a:focus { outline: 2px solid var(--wiki-focus); outline-offset: 2px; }
        .wiki-btn, button.wiki-btn {
            display: inline-flex; align-items: center; justify-content: center; height: var(--wiki-control-height); min-height: var(--wiki-control-height); box-sizing: border-box; padding: 0 var(--wiki-control-pad-x); border: 1px solid var(--wiki-primary); border-radius: 2px; background: var(--wiki-primary); color: #fff; font: inherit; font-weight: 600; line-height: 1.2; text-decoration: none; cursor: pointer; white-space: nowrap;
        }
        .wiki-btn:hover, .wiki-btn:focus, button.wiki-btn:hover, button.wiki-btn:focus { background: var(--wiki-primary-hover); border-color: var(--wiki-primary-hover); color: #fff; text-decoration: none; }
        .wiki-btn.secondary { background: var(--wiki-card); color: var(--wiki-primary); border-color: transparent; }
        .wiki-btn.secondary:hover, .wiki-btn.secondary:focus { background: var(--wiki-card-alt); border-color: var(--wiki-line); color: var(--wiki-primary-hover); }
        .wiki-card { background: var(--wiki-card-alt); border: 1px solid var(--wiki-line); border-radius: 2px; padding: 0.85rem; }
        .wiki-card h2, .wiki-card h3 { margin-top: 0; }
        .wiki-results, .wiki-saved-list { list-style: none; margin: 0; padding: 0; }
        .wiki-result, .wiki-saved-item { border-bottom: 1px solid var(--wiki-line-soft); padding: 0.78rem 0; background: transparent; }
        .wiki-result:first-child, .wiki-saved-item:first-child { border-top: 1px solid var(--wiki-line-soft); }
        .wiki-result h2, .wiki-saved-item h2, .wiki-saved-item h3 { margin: 0 0 0.2rem; font-family: var(--wiki-sans); font-size: 1.12rem; font-weight: 400; line-height: 1.35; }
        .wiki-meta { display: flex; gap: 0.65rem; flex-wrap: wrap; color: var(--wiki-muted); font-size: 0.84rem; }
        .wiki-result p, .wiki-saved-item p { max-width: 72rem; margin: 0.45rem 0; color: var(--wiki-fg); }
        .wiki-section-head { display: flex; align-items: flex-end; justify-content: space-between; gap: 1rem; margin: 1.8rem 0 0.65rem; padding-bottom: 0.25rem; border-bottom: 1px solid var(--wiki-line); }
        .wiki-section-head h2 { margin: 0 0 0.1rem; font-family: var(--wiki-serif); font-size: 1.55rem; font-weight: 400; }
        .wiki-alpha-index { display: flex; flex-wrap: wrap; gap: 0.28rem; margin: 0.85rem 0; }
        .wiki-alpha-index .wiki-chip { min-width: 2rem; justify-content: center; box-sizing: border-box; }
        .wiki-alpha-section { margin-top: 1.15rem; }
        .wiki-alpha-heading { margin: 0 0 0.25rem; padding-bottom: 0.2rem; border-bottom: 1px solid var(--wiki-line-soft); font-family: var(--wiki-serif); font-size: 1.15rem; font-weight: 400; }
        .wiki-alpha-list { list-style: none; padding: 0; margin: 0; }
        .wiki-alpha-list li { border-bottom: 1px solid var(--wiki-line-soft); }
        .wiki-alpha-list a { display: flex; gap: 0.65rem; align-items: baseline; padding: 0.38rem 0; color: inherit; text-decoration: none; }
        .wiki-alpha-list a:hover .wiki-alpha-title, .wiki-alpha-list a:focus .wiki-alpha-title { color: var(--wiki-primary); text-decoration: underline; }
        .wiki-alpha-title { flex: 1; min-width: 0; overflow-wrap: anywhere; }
        .wiki-alpha-list .wiki-meta { justify-content: flex-end; font-size: 0.82rem; }
        .wiki-inline-form { display: inline; margin: 0; }
        .wiki-notice { margin: 0.75rem 0; padding: 0.65rem 0.8rem; border: 1px solid var(--wiki-line); border-radius: 2px; background: var(--wiki-card-alt); }
        .wiki-notice.success { background: var(--wiki-success-bg); border-color: var(--wiki-success-line); }
        .wiki-notice.error { background: var(--wiki-error-bg); border-color: var(--wiki-error-line); }
        .wiki-chip { display: inline-flex; gap: 0.25rem; align-items: baseline; border: 1px solid var(--wiki-line); border-radius: 2px; padding: 0.16rem 0.52rem; background: var(--wiki-card); color: var(--wiki-primary); text-decoration: none; font-size: 0.86rem; }
        .wiki-chip:hover, .wiki-chip:focus { border-color: var(--wiki-primary); background: var(--wiki-card-alt); text-decoration: none; }
        .wiki-chip small { color: var(--wiki-muted); }
        .wiki-language-tabs, .wiki-list-tabs { display: flex; gap: 0.35rem; flex-wrap: wrap; align-items: center; margin: -0.7rem 0 1.1rem; }
        .wiki-language-tabs a, .wiki-list-tabs a { display: inline-flex; align-items: center; min-height: 1.8rem; padding: 0 0.5rem; border: 1px solid transparent; border-radius: 2px; color: var(--wiki-primary); text-decoration: none; font-size: 0.86rem; }
        .wiki-language-tabs a:hover, .wiki-language-tabs a:focus, .wiki-list-tabs a:hover, .wiki-list-tabs a:focus { border-color: var(--wiki-line); background: var(--wiki-card-alt); text-decoration: none; }
        .wiki-language-tabs a.is-active, .wiki-list-tabs a.is-active { border-color: var(--wiki-line); background: var(--wiki-card); color: var(--wiki-fg); font-weight: 600; }
        .wiki-settings-form { max-width: 48rem; }
        .wiki-fieldset { margin: 1rem 0; padding: 0.85rem; border: 1px solid var(--wiki-line); background: var(--wiki-card-alt); }
        .wiki-fieldset legend { padding: 0 0.35rem; font-weight: 600; }
        .wiki-language-options { display: grid; grid-template-columns: repeat(auto-fit, minmax(11rem, 1fr)); gap: 0.35rem 0.75rem; }
        .wiki-language-options label { display: flex; gap: 0.45rem; align-items: center; margin: 0; font-weight: 400; }
        .wiki-language-options input { flex: 0 0 auto; }
        .wiki-settings-actions { margin: 1rem 0; }
        .wiki-article-tools { display: flex; gap: 0.35rem; flex-wrap: wrap; align-items: center; margin: 0.65rem 0 0; }
        .wiki-article { background: transparent; border: 0; border-radius: 0; padding: 0; overflow-wrap: anywhere; font-size: 0.95rem; }
        .wiki-article p { margin: 0.5rem 0; }
        .wiki-article img { max-width: 100%; height: auto; }
        .wiki-article table { max-width: 100%; border-collapse: collapse; font-size: 0.88rem; }
        .wiki-article > table:not(.infobox), .wiki-article .wikitable { display: block; overflow-x: auto; }
        .wiki-article th, .wiki-article td { border: 1px solid var(--wiki-line); padding: 0.32rem 0.45rem; vertical-align: top; }
        .wiki-article th { background: var(--wiki-card-alt); font-weight: 600; }
        .wiki-article .infobox { float: right; clear: right; width: 22rem; max-width: 40%; margin: 0.2rem 0 1rem 1rem; background: var(--wiki-card-alt); border: 1px solid var(--wiki-line); font-size: 0.86rem; line-height: 1.45; }
        .wiki-article .infobox caption, .wiki-article .infobox th { text-align: center; }
        .wiki-article .toc, .wiki-article #toc { display: table; max-width: 24rem; margin: 1rem 0; padding: 0.65rem 0.85rem; border: 1px solid var(--wiki-line); background: var(--wiki-card-alt); font-size: 0.9rem; }
        .wiki-article .toc ul, .wiki-article #toc ul { list-style: none; margin: 0.25rem 0 0 0.75rem; padding: 0; }
        .wiki-article .tocnumber { color: var(--wiki-muted); }
        .wiki-article .mw-editsection, .wiki-article .noprint, .wiki-article .metadata, .wiki-article .ambox { display: none; }
        .wiki-article h1, .wiki-article h2, .wiki-article h3, .wiki-article h4 { font-family: var(--wiki-serif); font-weight: 400; line-height: 1.25; letter-spacing: 0; }
        .wiki-article h2 { margin: 1.55rem 0 0.55rem; border-bottom: 1px solid var(--wiki-line); padding-bottom: 0.2rem; font-size: 1.55rem; }
        .wiki-article h3 { margin: 1.2rem 0 0.35rem; font-size: 1.25rem; }
        .wiki-article h4 { margin: 1rem 0 0.3rem; font-size: 1.05rem; font-family: var(--wiki-sans); font-weight: 600; }
        @media (max-width: 820px) {
            .wiki-shell { padding: 0.65rem 1rem 3rem; }
            .wiki-search { grid-template-columns: 1fr; }
            .wiki-topbar { align-items: flex-start; }
            .wiki-page-head, .wiki-section-head { display: block; }
            .wiki-section-head .wiki-btn { margin-top: 0.75rem; }
            .wiki-alpha-list a { display: block; }
            .wiki-alpha-list .wiki-meta { justify-content: flex-start; margin-top: 0.15rem; }
            .wiki-actions { justify-content: flex-start; margin-top: 0.75rem; }
            .wiki-language-switcher { width: 100%; }
            .wiki-language-switcher select { flex: 1; max-width: none; }
            .wiki-article .infobox { float: none; width: 100%; max-width: 100%; margin: 0 0 1rem; }
            .wiki-article table { display: block; overflow-x: auto; }
        }
        @media (max-width: 520px) {
            .wiki-topbar { display: block; }
            .wiki-nav { justify-content: flex-start; margin-top: 0.75rem; }
            .wiki-nav-menu-panel { left: 0; right: auto; }
            .wiki-wikipedia-search, .wiki-compact-search { padding: 0.45rem; }
            .wiki-wikipedia-search .wiki-search-language, .wiki-wikipedia-search .wiki-search-submit,
            .wiki-compact-search .wiki-search-language, .wiki-compact-search .wiki-search-submit { display: none; }
            .wiki-wikipedia-search .wiki-search-field > span,
            .wiki-compact-search .wiki-search-field > span { position: absolute; width: 1px; height: 1px; margin: -1px; overflow: hidden; clip: rect(0, 0, 0, 0); white-space: nowrap; }
            .wiki-wikipedia-search input[type="search"], .wiki-compact-search input[type="search"] { height: 2.5rem; min-height: 2.5rem; font-size: 1rem; }
        }
    </style>
</head>
<body>
<?php wp_app_body_open(); ?>
<main class="wiki-shell">
    <nav class="wiki-topbar" aria-label="<?php esc_attr_e( 'Wikipedia app', 'wikipedia' ); ?>">
        <a class="wiki-brand" href="<?php echo esc_url( \Akirk\Wikipedia\App::get_app_url() ); ?>">
            <span class="wiki-brand-mark" aria-hidden="true">W</span>
            <span>
                <span class="wiki-wordmark"><?php esc_html_e( 'Wikipedia', 'wikipedia' ); ?></span>
                <span class="wiki-tagline"><?php esc_html_e( 'Inside WordPress', 'wikipedia' ); ?></span>
            </span>
        </a>
        <div class="wiki-nav">
            <a href="<?php echo esc_url( \Akirk\Wikipedia\App::get_app_url() ); ?>"><?php esc_html_e( 'Search', 'wikipedia' ); ?></a>
            <a href="<?php echo esc_url( \Akirk\Wikipedia\App::get_saved_articles_url() ); ?>"><?php esc_html_e( 'Saved articles', 'wikipedia' ); ?></a>
            <a href="<?php echo esc_url( \Akirk\Wikipedia\App::get_settings_url() ); ?>"><?php esc_html_e( 'Settings', 'wikipedia' ); ?></a>
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
