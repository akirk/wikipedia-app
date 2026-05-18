<?php

use Akirk\Wikipedia\App;
use PHPUnit\Framework\TestCase;

class AppHelpersTest extends TestCase {
    protected function tearDown(): void {
        unset( $GLOBALS['wikipedia_app_test_user_locale'] );
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

    public function test_default_language_uses_user_locale(): void {
        $GLOBALS['wikipedia_app_test_user_locale'] = 'de_DE';

        $this->assertSame( 'de', App::get_default_language() );
        $this->assertSame( 'de', App::normalize_language() );
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

    private function invokePrivateStatic( string $method, array $args ) {
        $reflection = new ReflectionMethod( App::class, $method );
        $reflection->setAccessible( true );

        return $reflection->invokeArgs( null, $args );
    }
}
