<?php

use Akirk\Wikipedia\App;
use PHPUnit\Framework\TestCase;

class AppHelpersTest extends TestCase {
    protected function tearDown(): void {
        unset( $GLOBALS['wikipedia_app_test_user_locale'] );
        unset( $GLOBALS['wikipedia_app_test_home_url'] );
    }

    /** @dataProvider localeLanguages */
    public function test_language_from_locale( string $locale, string $expected ): void {
        $this->assertSame( $expected, App::language_from_locale( $locale ) );
    }

    public static function localeLanguages(): array {
        return [
            'english us'     => [ 'en_US', 'en' ],
            'german formal'  => [ 'de_DE_formal', 'de' ],
            'portuguese br'  => [ 'pt_BR', 'pt' ],
            'chinese taiwan' => [ 'zh_TW', 'zh' ],
            'nynorsk'        => [ 'nn_NO', 'nn' ],
            'invalid blank'  => [ '', 'en' ],
        ];
    }

    public function test_locale_default_language_uses_user_locale(): void {
        $GLOBALS['wikipedia_app_test_user_locale'] = 'de_DE';

        $this->assertSame( 'de', App::get_locale_default_language() );
    }

    public function test_default_language_uses_english_without_preferences(): void {
        $GLOBALS['wikipedia_app_test_user_locale'] = 'de_DE';

        $this->assertSame( 'en', App::get_default_language() );
        $this->assertSame( 'en', App::normalize_language() );
    }

    public function test_normalize_language_rejects_invalid_subdomain(): void {
        $result = App::normalize_language( '../en' );

        $this->assertInstanceOf( WP_Error::class, $result );
        $this->assertSame( 'wikipedia_invalid_language', $result->get_error_code() );
    }

    public function test_language_labels_include_supported_and_unknown_codes(): void {
        $this->assertSame( 'English', App::get_language_label( 'en' ) );
        $this->assertSame( 'SCO', App::get_language_label( 'sco' ) );
    }

    public function test_normalize_language_list_dedupes_and_rejects_invalid_codes(): void {
        $this->assertSame(
            [ 'de', 'en', 'simple' ],
            App::normalize_language_list( [ 'DE', '../bad', 'en', 'de', 'simple' ] )
        );
    }

    public function test_saved_article_slug_includes_language_and_title(): void {
        $this->assertSame(
            'de-albert-einstein',
            App::build_article_slug( 'de', 'Albert Einstein', 736 )
        );
    }

    public function test_article_url_prefers_title_for_readable_urls(): void {
        $this->assertSame(
            'https://example.test/wikipedia/article/de?title=Albert%20Einstein',
            App::get_article_url( 'de', 'Albert Einstein', 736 )
        );
    }

    public function test_article_url_can_use_title(): void {
        $this->assertSame(
            'https://example.test/wikipedia/article/en?title=Albert%20Einstein',
            App::get_article_url( 'en', 'Albert Einstein' )
        );
    }

    public function test_article_url_can_use_page_id_when_title_is_missing(): void {
        $this->assertSame(
            'https://example.test/wikipedia/article/de?page_id=736',
            App::get_article_url( 'de', '', 736 )
        );
    }

    public function test_saved_articles_url(): void {
        $this->assertSame( 'https://example.test/wikipedia/saved', App::get_saved_articles_url() );
    }

    public function test_settings_and_list_urls(): void {
        $this->assertSame( 'https://example.test/wikipedia/settings', App::get_settings_url() );
        $this->assertSame( 'https://example.test/wikipedia/list/science', App::get_list_url( 'Science' ) );
    }

    public function test_article_allowed_html_contains_expected_article_tags(): void {
        $allowed = App::article_allowed_html();

        $this->assertArrayHasKey( 'a', $allowed );
        $this->assertArrayHasKey( 'href', $allowed['a'] );
        $this->assertArrayHasKey( 'table', $allowed );
        $this->assertArrayHasKey( 'img', $allowed );
        $this->assertArrayHasKey( 'src', $allowed['img'] );
    }

    public function test_snippet_text_is_plain_and_normalized(): void {
        $text = "  Albert&nbsp;<strong>Einstein</strong>\r\n\r\n\r\n developed\t relativity.  ";

        $this->assertSame(
            "Albert Einstein\n\ndeveloped relativity.",
            $this->invokePrivateStatic( 'normalize_snippet_text', [ $text ] )
        );
    }

    public function test_snippet_excerpt_trims_to_word_limit(): void {
        $this->assertSame(
            'one two three...',
            $this->invokePrivateStatic( 'snippet_excerpt', [ 'one two three four five', 3 ] )
        );
    }

    public function test_snippet_content_is_stored_as_blocks_and_read_as_text(): void {
        $content = $this->invokePrivateStatic( 'snippet_content_for_storage', [ "one\ntwo\n\nthree & four" ] );

        $this->assertStringContainsString( '<!-- wp:paragraph -->', $content );
        $this->assertStringContainsString( '<p>one<br>two</p>', $content );
        $this->assertStringContainsString( '<p>three &amp; four</p>', $content );
        $this->assertSame(
            "one\ntwo\n\nthree & four",
            $this->invokePrivateStatic( 'snippet_plain_text_from_content', [ $content ] )
        );
    }

    public function test_wikipedia_article_href_is_rewritten_to_app_url(): void {
        $this->assertSame(
            'https://example.test/wikipedia/article/en?title=Albert%20Einstein#Life',
            $this->invokePrivateStatic( 'app_url_from_wikipedia_href', [ '/wiki/Albert_Einstein#Life', 'en' ] )
        );
    }

    public function test_cross_language_wikipedia_href_is_rewritten_to_that_language(): void {
        $this->assertSame(
            'https://example.test/wikipedia/article/de?title=Albert%20Einstein',
            $this->invokePrivateStatic( 'app_url_from_wikipedia_href', [ 'https://de.wikipedia.org/wiki/Albert_Einstein', 'en' ] )
        );
    }

    public function test_non_article_or_external_hrefs_are_not_rewritten(): void {
        $this->assertSame( '', $this->invokePrivateStatic( 'app_url_from_wikipedia_href', [ '/wiki/File:Example.jpg', 'en' ] ) );
        $this->assertSame( '', $this->invokePrivateStatic( 'app_url_from_wikipedia_href', [ 'https://example.com/wiki/Albert_Einstein', 'en' ] ) );
    }

    public function test_article_resource_nodes_are_removed(): void {
        $html = '<style>.mw-parser-output .hatnote{font-style:italic}</style><link rel="mw-deduplicated-inline-style" href="mw-data:TemplateStyles"><p>Article body</p>';
        $cleaned = $this->invokePrivateStatic( 'remove_article_resource_nodes', [ $html ] );

        $this->assertStringNotContainsString( 'mw-parser-output', $cleaned );
        $this->assertStringNotContainsString( '<style', $cleaned );
        $this->assertStringNotContainsString( '<link', $cleaned );
        $this->assertStringContainsString( 'Article body', $cleaned );
    }

    public function test_wikipedia_request_headers_identify_normal_wordpress_sites(): void {
        $headers = $this->invokePrivateStatic( 'wikipedia_request_headers', [] );
        $user_agent = $this->invokePrivateStatic( 'wikipedia_user_agent', [] );

        $this->assertSame( 'application/json', $headers['Accept'] );
        $this->assertArrayNotHasKey( 'User-Agent', $headers );
        $this->assertStringContainsString( 'Wikipedia WordPress App/1.0', $user_agent );
        $this->assertStringContainsString( 'https://example.test/', $user_agent );
    }

    /**
     * @runInSeparateProcess
     */
    public function test_wordpress_playground_detection_uses_playground_constant(): void {
        if ( ! defined( 'PLAYGROUND_AUTO_LOGIN_AS_USER' ) ) {
            define( 'PLAYGROUND_AUTO_LOGIN_AS_USER', 1 );
        }

        $this->assertTrue( $this->invokePrivateStatic( 'is_wordpress_playground', [] ) );
    }

    public function test_wikipedia_http_error_prefers_api_error_body(): void {
        $error = $this->invokePrivateStatic( 'wikipedia_http_error', [
            400,
            [ 'error' => [ 'info' => '<strong>Bad request</strong>' ] ],
            [],
        ] );

        $this->assertInstanceOf( WP_Error::class, $error );
        $this->assertSame( 'wikipedia_api_error', $error->get_error_code() );
        $this->assertSame( 'Bad request', $error->get_error_message() );
    }

    public function test_wikipedia_http_error_reports_retry_after_for_rate_limits(): void {
        $error = $this->invokePrivateStatic( 'wikipedia_http_error', [
            429,
            [],
            [ 'headers' => [ 'retry-after' => '60' ] ],
        ] );

        $this->assertInstanceOf( WP_Error::class, $error );
        $this->assertSame( 'wikipedia_rate_limited', $error->get_error_code() );
        $this->assertStringContainsString( '60', $error->get_error_message() );
    }

    private function invokePrivateStatic( string $method, array $args ) {
        $reflection = new ReflectionMethod( App::class, $method );
        $reflection->setAccessible( true );

        return $reflection->invokeArgs( null, $args );
    }
}
