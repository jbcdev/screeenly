<?php

use Screeenly\Models\ApiKey;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class ApiV1ScreenshotTest extends TestCase
{
    use DatabaseTransactions;
    use InteractsWithBrowser;

    /** @test */
    public function it_return_an_error_if_no_api_key_was_passed_to_the_api()
    {
        $this->json('POST', '/api/v1/fullsize', [])
            ->seeJsonEquals([
                'title' => 'An error accoured',
                'message' => 'No API Key specified.',
            ]);
    }

    /** @test */
    public function it_returns_an_error_if_no_url_was_passed_to_the_api()
    {
        $apiKey = factory(ApiKey::class)->create();

        $this->json('POST', '/api/v1/fullsize', [
                'key' => $apiKey->key,
            ])
            ->seeJsonEquals([
                'title' => 'An error accoured',
                'message' => 'Validation Error: The url field is required.',
            ]);
    }

    /** @test */
    public function it_returns_an_error_if_width_is_to_big()
    {
        $apiKey = factory(ApiKey::class)->create();

        $this->json('POST', '/api/v1/fullsize', [
                'key' => $apiKey->key,
                'url' => 'http://foo.bar',
                'width' => '5000'
            ])
            ->seeJsonEquals([
                'title' => 'An error accoured',
                'message' => 'Validation Error: The width may not be greater than 2000.',
            ]);
    }

    /** @test */
    public function it_returns_an_errof_if_url_has_no_protocol_prefix()
    {
        $apiKey = factory(ApiKey::class)->create();

        $this->json('POST', '/api/v1/fullsize', [
                'key' => $apiKey->key,
                'url' => 'foo.com',
            ])
            ->seeJsonEquals([
                'title' => 'An error accoured',
                'message' => 'Validation Error: The url format is invalid.',
            ]);
    }

    /** @test */
    public function it_returns_path_and_base64_representation_of_to_image_on_successful_request()
    {
        $apiKey = factory(ApiKey::class)->create();
        $this->replaceBinding();

        $this->json('POST', '/api/v1/fullsize', [
                'key' => $apiKey->key,
                'url' => 'http://foo.com',
            ])
            ->seeJsonStructure([
                'data' => [
                    'path',
                    'base64',
                    'base64_raw',
                ],
            ]);
            // ->seeJsonContains([
            //     'data' => [
            //         'path' => 'http://localhost/storage/test-screenshot.jpg',
            //         'base64' => 'data:image/jpg;base64,' . base64_encode($this->getTestScreenshotFile()),
            //         'base64_raw' => base64_encode($this->getTestScreenshotFile())
            //     ]
            // ]);
    }

    /** @test */
    public function it_returns_generic_error_if_something_goes_wrong()
    {
        $this->json('POST', '/api/v1/does-not-exist', [])
            ->seeJson([
                'title' => 'An error accoured',
                'message' => 'An internal error accoured.',
            ]);
    }
}
