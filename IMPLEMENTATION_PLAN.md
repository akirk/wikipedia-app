# Wikipedia App Implementation Plan

## Original Request

Build on the existing WpApp scaffold to create a Wikipedia browser app. The primary experience should let users search Wikipedia in a chosen language, read articles inside the app, follow article links while staying in the app, see other language versions of the current article, and optionally save articles as local WordPress sources that can be refetched later.

## Generated App Details

- `plugin_slug`: `wikipedia-app`
- `plugin_dir`: `/home/kirk.at/www/public/wp-content/plugins/wikipedia-app`
- `plugin_file`: `wikipedia-app.php`
- `url_path`: `wikipedia`
- `url`: `home_url( '/wikipedia/' )`

## Status

- Local Git repository initialized on `main`.
- Initial scaffold committed as `4ca5715 Initial scaffold`.
- GitHub remote was intentionally skipped; user will handle it later.
- Implementation in progress; direction corrected so browsing/searching Wikipedia is the main app, and saving is secondary.

## Checklist

- [x] Read `../ai-assistant/skills/wp-app.md`.
- [x] Read generated `wikipedia-app.php`, `src/App.php`, and `templates/index.php`.
- [x] Read AI Assistant plugin integration guidance.
- [x] Register private `wikipedia_article` CPT with REST/admin UI support and admin metadata columns.
- [x] Add multilingual Wikipedia search/fetch helpers using WordPress HTTP APIs.
- [x] Add app UI to choose search language, search Wikipedia, read article HTML in the app, follow article links inside the app, and open other language versions.
- [x] Add saved article route.
- [x] Register focused WordPress Abilities.
- [x] Add PHPUnit through Composer and unit tests for pure helpers.
- [x] Add `blueprint.json` for WordPress Playground activation.
- [x] Run PHP syntax checks and PHPUnit.

## Files Read Or Changed

- Read `../ai-assistant/skills/wp-app.md` for WpApp lifecycle, storage, and ability guidance.
- Read `../ai-assistant/docs/plugin-integration.md` for AI Assistant ability domains and result instructions.
- Read `vendor/akirk/wp-app/src/abstract-baseapp.php`, `class-wpapp.php`, `class-router.php`, and `functions.php` for lifecycle and template route behavior.
- Read `../cookbook/src/App.php` and template examples for local WpApp patterns.
- Changed `IMPLEMENTATION_PLAN.md` to keep this work resumable and record the corrected product direction.
- Changed `src/App.php` to implement the CPT, origin/refetch post meta, admin columns, Wikipedia search/fetch/parse helpers, internal link rewriting, language-link formatting, save/refetch handlers, app routes, and AI Assistant abilities.
- Changed `templates/index.php` to provide the Wikipedia search interface, language selector, results, and saved-local-source list.
- Added `templates/article.php` for live in-app Wikipedia reading, language switching, internal link browsing, and save action.
- Added `templates/saved.php` for local saved-source reading and refetching.
- Added `templates/_header.php` and `templates/_footer.php` shared app chrome and styles.
- Changed `composer.json` and `composer.lock` to add `phpunit/phpunit` as a dev dependency and `composer test`.
- Added `phpunit.xml.dist`, `tests/bootstrap.php`, and `tests/AppHelpersTest.php` for database-free unit tests.
- Added `blueprint.json` to set permalinks, activate the mounted plugin, log in, and land on `/wikipedia/` in WordPress Playground.

## Decisions And Assumptions

- Use WordPress-native storage: one private CPT plus post meta for origin/refetch data.
- Do not add a taxonomy for now. Saved articles should be searchable in the CPT admin UI and carry language/origin metadata in post meta.
- Default Wikipedia search language comes from the current user's WordPress locale (`get_user_locale()`), falling back to site locale and then `en`.
- Treat multilingual browsing as first-class: UI, abilities, saved metadata, saved slugs, and saved-article filtering carry a language code.
- Saved article slugs include language and Wikipedia page ID, e.g. `de-albert-einstein-736`.
- Saving an article is idempotent by Wikipedia page ID and language; existing saved articles are updated/refetched.
- App access requires logged-in users. Saving requires `edit_posts`; read/search abilities require `read`.
- Use Wikipedia's public MediaWiki API through `wp_remote_get()`.
- Article HTML should be sanitized for output and internal `/wiki/...` links rewritten back into the app so browsing stays inside `/wikipedia/`.

## Blockers And Risks

- Network access from the WordPress server is required for live Wikipedia searches and fetches.
- Runtime testing through WordPress was not performed yet.
- MediaWiki article HTML varies by language/site; link rewriting should remain conservative and leave non-article/special links pointing to Wikipedia.
- `composer validate` passes with Composer's pre-existing warning about the `version` field in `composer.json`.
