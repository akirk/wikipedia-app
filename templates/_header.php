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
            --wiki-bg: var(--wp-app-color-background, #f6f7f7);
            --wiki-fg: var(--wp-app-color-text, #1d2327);
            --wiki-muted: var(--wp-app-color-muted, #646970);
            --wiki-line: var(--wp-app-color-border, #dcdcde);
            --wiki-card: var(--wp-app-color-surface, #fff);
            --wiki-card-alt: var(--wp-app-color-surface-alt, #f0f0f1);
            --wiki-primary: var(--wp-app-color-primary, #3858e9);
            --wiki-primary-hover: var(--wp-app-color-primary-hover, #2145d9);
            --wiki-focus: var(--wp-app-color-focus, var(--wiki-primary));
            --wiki-success-bg: #e8f5e9;
            --wiki-success-line: #9ccc9f;
            --wiki-error-bg: #fdecea;
            --wiki-error-line: #efb3ad;
        }
        :root[data-theme="dark"] {
            --wiki-success-bg: #19351f;
            --wiki-success-line: #3f6b42;
            --wiki-error-bg: #3d2424;
            --wiki-error-line: #7a3a3a;
        }
        body { margin: 0; background: var(--wiki-bg); color: var(--wiki-fg); font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen-Sans, Ubuntu, Cantarell, "Helvetica Neue", sans-serif; line-height: 1.55; }
        a { color: var(--wp-app-color-link, var(--wiki-primary)); }
        a:hover, a:focus { color: var(--wp-app-color-link-hover, var(--wiki-primary-hover)); }
        .wiki-shell { max-width: 1100px; margin: 0 auto; padding: 1.25rem 1rem 4rem; }
        .wiki-page-head { display: flex; gap: 1rem; justify-content: space-between; align-items: flex-start; margin: 0 0 1rem; }
        .wiki-page-head h1 { margin: 0 0 0.2rem; font-size: clamp(1.7rem, 4vw, 2.35rem); line-height: 1.15; letter-spacing: 0; }
        .wiki-subtitle { margin: 0; color: var(--wiki-muted); }
        .wiki-actions { display: flex; gap: 0.45rem; flex-wrap: wrap; justify-content: flex-end; }
        .wiki-search { display: grid; grid-template-columns: minmax(0, 1fr) minmax(8rem, 12rem) auto; gap: 0.55rem; align-items: end; margin: 1rem 0 1.2rem; }
        .wiki-search label { display: grid; gap: 0.25rem; margin: 0; font-weight: 600; }
        .wiki-search span { color: var(--wiki-muted); font-size: 0.84rem; font-weight: 500; }
        input[type="search"], input[type="text"], select {
            width: 100%; box-sizing: border-box; padding: 0.58rem 0.65rem; border: 1px solid var(--wiki-line); border-radius: 4px; background: var(--wiki-card); color: var(--wiki-fg); font: inherit;
        }
        input:focus, select:focus, button:focus, a:focus { outline: 2px solid var(--wiki-focus); outline-offset: 2px; }
        .wiki-btn, button.wiki-btn {
            display: inline-flex; align-items: center; justify-content: center; min-height: 2.45rem; padding: 0.55rem 0.9rem; border: 0; border-radius: 4px; background: var(--wiki-primary); color: #fff; font: inherit; font-weight: 600; text-decoration: none; cursor: pointer;
        }
        .wiki-btn:hover, .wiki-btn:focus, button.wiki-btn:hover, button.wiki-btn:focus { background: var(--wiki-primary-hover); color: #fff; }
        .wiki-btn.secondary { background: var(--wiki-card-alt); color: var(--wiki-fg); border: 1px solid var(--wiki-line); }
        .wiki-layout { display: grid; grid-template-columns: minmax(0, 1fr) minmax(17rem, 22rem); gap: 1rem; align-items: start; }
        .wiki-card { background: var(--wiki-card); border: 1px solid var(--wiki-line); border-radius: 6px; padding: 1rem; }
        .wiki-card h2, .wiki-card h3 { margin-top: 0; }
        .wiki-results, .wiki-saved-list { list-style: none; margin: 0; padding: 0; display: grid; gap: 0.7rem; }
        .wiki-result, .wiki-saved-item { border: 1px solid var(--wiki-line); border-radius: 6px; padding: 0.85rem; background: var(--wiki-card); }
        .wiki-result h2, .wiki-saved-item h3 { margin: 0 0 0.25rem; font-size: 1.1rem; }
        .wiki-meta { display: flex; gap: 0.65rem; flex-wrap: wrap; color: var(--wiki-muted); font-size: 0.86rem; }
        .wiki-result p, .wiki-saved-item p { margin: 0.45rem 0; color: var(--wiki-fg); }
        .wiki-inline-form { display: inline; }
        .wiki-notice { margin: 0.75rem 0; padding: 0.7rem 0.85rem; border: 1px solid var(--wiki-line); border-radius: 4px; background: var(--wiki-card-alt); }
        .wiki-notice.success { background: var(--wiki-success-bg); border-color: var(--wiki-success-line); }
        .wiki-notice.error { background: var(--wiki-error-bg); border-color: var(--wiki-error-line); }
        .wiki-language-links { display: flex; gap: 0.4rem; flex-wrap: wrap; max-height: 10rem; overflow: auto; padding: 0.25rem 0; }
        .wiki-chip { display: inline-flex; gap: 0.25rem; align-items: baseline; border: 1px solid var(--wiki-line); border-radius: 999px; padding: 0.2rem 0.65rem; background: var(--wiki-card); color: inherit; text-decoration: none; font-size: 0.88rem; }
        .wiki-chip:hover { border-color: var(--wiki-primary); }
        .wiki-chip small { color: var(--wiki-muted); }
        .wiki-article-tools { display: flex; gap: 0.5rem; flex-wrap: wrap; align-items: center; margin: 0.9rem 0; }
        .wiki-article { background: var(--wiki-card); border: 1px solid var(--wiki-line); border-radius: 6px; padding: clamp(1rem, 3vw, 1.6rem); overflow-wrap: anywhere; }
        .wiki-article img { max-width: 100%; height: auto; }
        .wiki-article table { display: block; max-width: 100%; overflow-x: auto; border-collapse: collapse; }
        .wiki-article th, .wiki-article td { border: 1px solid var(--wiki-line); padding: 0.35rem 0.5rem; vertical-align: top; }
        .wiki-article .infobox { float: right; max-width: 22rem; margin: 0 0 1rem 1rem; background: var(--wiki-card-alt); }
        .wiki-article .mw-editsection, .wiki-article .noprint, .wiki-article .metadata, .wiki-article .ambox { display: none; }
        .wiki-article h2 { margin-top: 1.8rem; border-bottom: 1px solid var(--wiki-line); padding-bottom: 0.25rem; }
        @media (max-width: 820px) {
            .wiki-layout, .wiki-search { grid-template-columns: 1fr; }
            .wiki-page-head { display: block; }
            .wiki-actions { justify-content: flex-start; margin-top: 0.75rem; }
            .wiki-article .infobox { float: none; max-width: 100%; margin: 0 0 1rem; }
        }
    </style>
</head>
<body>
<?php wp_app_body_open(); ?>
<main class="wiki-shell">
