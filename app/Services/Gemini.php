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

        $model = config('services.gemini.model', 'gemini-3.8-flash');

        try {
            $response = Http::timeout(15)
                // Gemini's flash models return a 503 under high demand fairly often; this is
                // transient (seconds, not minutes), so a couple of quick retries clears most of
                // them without the admin having to notice and click the button again themselves.
                // Kept short (worst case: 3 attempts x 15s + 2 x 0.8s ~ 47s) to stay well inside
                // typical shared-hosting PHP execution limits.
                ->retry(2, 800, fn ($e, $req) => $e instanceof \Illuminate\Http\Client\RequestException && $e->response->status() === 503, throw: false)
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

            $message = $response->status() === 503
                ? 'OkuLands Smart AI is unusually busy right now. Please try again in a moment.'
                : 'The AI service could not generate text right now. Please try again shortly.';

            throw new \RuntimeException($message);
        }

        $text = trim((string) data_get($response->json(), 'candidates.0.content.parts.0.text'));

        if ($text === '') {
            throw new \RuntimeException('The AI did not return any text. Please try again.');
        }

        return $text;
    }
}
