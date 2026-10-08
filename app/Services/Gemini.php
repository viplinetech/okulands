<?php

/**
 * Crafted & Developed by Vipline Technologies Limited - ViplineTech (www.viplinetech.com)
 */

namespace App\Services;

use Illuminate\Support\Facades\Http;

/**
 * Thin wrapper around Google's Gemini API. Powers "OkuLands Smart AI" writing assistance in the
 * admin: property descriptions, blog posts, and anywhere else the admin writes content.
 */
class Gemini
{
    public function enabled(): bool
    {
        return filled(config('services.gemini.key'));
    }

    /**
     * @throws \RuntimeException when AI writing isn't configured, or the API call fails
     */
    public function generate(string $prompt): string
    {
        if (! $this->enabled()) {
            throw new \RuntimeException('AI writing is not set up yet. Add a Gemini API key to the server configuration.');
        }

        $model = config('services.gemini.model', 'gemini-2.5-flash');

        try {
            $response = Http::timeout(45)
                ->withHeaders(['x-goog-api-key' => config('services.gemini.key')])
                ->post(
                    "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent",
                    [
                        'contents' => [['parts' => [['text' => $prompt]]]],
                        'generationConfig' => ['temperature' => 0.7],
                    ]
                );
        } catch (\Throwable $e) {
            report($e);

            throw new \RuntimeException('Could not reach the AI service right now. Please try again shortly.');
        }

        if ($response->failed()) {
            report(new \RuntimeException('Gemini API error: '.$response->status().' '.$response->body()));

            throw new \RuntimeException('The AI service could not generate text right now. Please try again shortly.');
        }

        $text = trim((string) data_get($response->json(), 'candidates.0.content.parts.0.text'));

        if ($text === '') {
            throw new \RuntimeException('The AI did not return any text. Please try again.');
        }

        return $text;
    }
}
