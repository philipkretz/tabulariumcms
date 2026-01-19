<?php
namespace App\Service;

use App\Repository\SiteSettingsRepository;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class AiService
{
    public function __construct(
        private HttpClientInterface $httpClient,
        private SiteSettingsRepository $settingsRepository
    ) {}

    public function generateContent(string $prompt, ?string $provider = null): string
    {
        $provider = $provider ?? $this->getDefaultProvider();

        return match($provider) {
            "openai" => $this->generateWithOpenAI($prompt),
            "gemini" => $this->generateWithGemini($prompt),
            "claude" => $this->generateWithClaude($prompt),
            default => throw new \Exception("Unsupported AI provider: {$provider}"),
        };
    }

    private function generateWithOpenAI(string $prompt): string
    {
        $apiKey = $this->getSetting("ai_openai_api_key");
        $model = $this->getSetting("ai_openai_model") ?? "gpt-4o";

        if (empty($apiKey)) {
            throw new \Exception("OpenAI API key not configured");
        }

        try {
            $response = $this->httpClient->request("POST", "https://api.openai.com/v1/chat/completions", [
                "headers" => [
                    "Authorization" => "Bearer {$apiKey}",
                    "Content-Type" => "application/json",
                ],
                "json" => [
                    "model" => $model,
                    "messages" => [
                        ["role" => "user", "content" => $prompt]
                    ],
                ],
            ]);

            $data = $response->toArray();
            return $data["choices"][0]["message"]["content"] ?? "";
        } catch (\Exception $e) {
            throw new \Exception("OpenAI API error: " . $e->getMessage());
        }
    }

    private function generateWithGemini(string $prompt): string
    {
        $apiKey = $this->getSetting("ai_gemini_api_key");
        $model = $this->getSetting("ai_gemini_model") ?? "gemini-2.0-flash-exp";

        if (empty($apiKey)) {
            throw new \Exception("Gemini API key not configured");
        }

        try {
            $response = $this->httpClient->request("POST", "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$apiKey}", [
                "headers" => ["Content-Type" => "application/json"],
                "json" => [
                    "contents" => [
                        ["parts" => [["text" => $prompt]]]
                    ],
                ],
            ]);

            $data = $response->toArray();
            return $data["candidates"][0]["content"]["parts"][0]["text"] ?? "";
        } catch (\Exception $e) {
            throw new \Exception("Gemini API error: " . $e->getMessage());
        }
    }

    private function generateWithClaude(string $prompt): string
    {
        $apiKey = $this->getSetting("ai_claude_api_key");
        $model = $this->getSetting("ai_claude_model") ?? "claude-3-5-sonnet-20241022";

        if (empty($apiKey)) {
            throw new \Exception("Claude API key not configured");
        }

        try {
            $response = $this->httpClient->request("POST", "https://api.anthropic.com/v1/messages", [
                "headers" => [
                    "x-api-key" => $apiKey,
                    "anthropic-version" => "2023-06-01",
                    "Content-Type" => "application/json",
                ],
                "json" => [
                    "model" => $model,
                    "max_tokens" => 4096,
                    "messages" => [
                        ["role" => "user", "content" => $prompt]
                    ],
                ],
            ]);

            $data = $response->toArray();
            return $data["content"][0]["text"] ?? "";
        } catch (\Exception $e) {
            throw new \Exception("Claude API error: " . $e->getMessage());
        }
    }

    public function generateDescription(string $title, string $context = ""): string
    {
        $prompt = "Generate a compelling SEO-optimized description (max 160 characters) for:\nTitle: {$title}";
        if ($context) {
            $prompt .= "\nContext: {$context}";
        }
        return $this->generateContent($prompt);
    }

    public function generateSlug(string $title): string
    {
        $prompt = "Generate a clean, SEO-friendly URL slug (lowercase, hyphens, no special chars) for: {$title}. Return ONLY the slug, nothing else.";
        return trim($this->generateContent($prompt));
    }

    public function generateMetaKeywords(string $title, string $description): string
    {
        $prompt = "Generate 5-10 relevant SEO keywords (comma-separated) for:\nTitle: {$title}\nDescription: {$description}";
        return $this->generateContent($prompt);
    }

    private function getSetting(string $key): ?string
    {
        $setting = $this->settingsRepository->findOneBy(["settingKey" => $key]);
        return $setting?->getSettingValue();
    }

    private function getDefaultProvider(): string
    {
        return $this->getSetting("ai_default_provider") ?? "openai";
    }
}