<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LocaleTest extends TestCase
{
    use RefreshDatabase;

    public function test_english_pages_contain_no_nepali_text(): void
    {
        foreach (['/', '/scholarships', '/verify/award'] as $uri) {
            $response = $this->withSession(['locale' => 'en'])->get($uri);

            $response->assertOk();

            $body = preg_replace('#<script.*?</script>|<style.*?</style>#su', '', $response->getContent());

            $this->assertDoesNotMatchRegularExpression(
                '/[\x{0900}-\x{097F}]/u',
                $body,
                "Nepali text leaked into the English page {$uri}"
            );
        }
    }

    public function test_nepali_locale_renders_nepali_homepage(): void
    {
        $response = $this->withSession(['locale' => 'np'])->get('/');

        $response->assertOk();
        $response->assertSee(trans('home.slides.slide_1.title', [], 'np'));
        $response->assertSee(trans('common.switch_language', [], 'np'));
        $response->assertDontSee(trans('common.switch_language', [], 'en'));
        $response->assertDontSee(trans('home.slides.slide_1.label', [], 'en'));
    }

    public function test_nepali_locale_renders_nepali_scholarship_browse_page(): void
    {
        $response = $this->withSession(['locale' => 'np'])->get('/scholarships');

        $response->assertOk();
        $response->assertSee(trans('scholarship.index.search_label', [], 'np'));
        $response->assertSee(trans('scholarship.index.apply_filters', [], 'np'));
        $response->assertDontSee(trans('scholarship.index.apply_filters', [], 'en'));
        $response->assertDontSee(trans('scholarship.index.search_placeholder', [], 'en'));
    }

    public function test_nepali_locale_renders_nepali_verification_page(): void
    {
        $response = $this->withSession(['locale' => 'np'])->get('/verify/award');

        $response->assertOk();
        $response->assertSee(trans('verify.instructions', [], 'np'));
        $response->assertDontSee(trans('verify.submit', [], 'en'));
    }
}
