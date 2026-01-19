<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;
use Psr\Log\LoggerInterface;

class AiContentGeneratorService
{
    private const OPENAI_API_URL = 'https://api.openai.com/v1/chat/completions';
    
    public function __construct(
        private HttpClientInterface $httpClient,
        private LoggerInterface $logger,
        private string $openaiApiKey = ''
    ) {
    }

    public function generateLongDescription(
        string $title,
        ?string $shortDescription = null,
        string $type = 'product'
    ): string {
        $prompt = $this->buildLongDescriptionPrompt($title, $shortDescription, $type);
        return $this->callOpenAI($prompt);
    }

    public function generateSeoTitle(string $title, string $type = 'product'): string
    {
        $typeLabel = ucfirst($type);
        $prompt = "Create an SEO-optimized title (max 60 characters) for this {$typeLabel}: \"{$title}\". 
        The title should be compelling, include the main keyword, and be under 60 characters. 
        Return ONLY the SEO title, nothing else.";
        
        return $this->callOpenAI($prompt, 100);
    }

    public function generateSeoDescription(
        string $title,
        ?string $content = null,
        string $type = 'product'
    ): string {
        $typeLabel = ucfirst($type);
        $prompt = "Create an SEO-optimized meta description (max 155 characters) for this {$typeLabel}: \"{$title}\".";
        
        if ($content) {
            $excerpt = substr(strip_tags($content), 0, 200);
            $prompt .= "\n\nContent excerpt: {$excerpt}";
        }
        
        $prompt .= "\n\nThe description should be compelling, include relevant keywords, and be under 155 characters. Return ONLY the meta description, nothing else.";
        
        return $this->callOpenAI($prompt, 100);
    }

    public function generateSeoKeywords(
        string $title,
        ?string $content = null,
        string $type = 'product'
    ): string {
        $typeLabel = ucfirst($type);
        $prompt = "Generate 5-10 relevant SEO keywords (comma-separated) for this {$typeLabel}: \"{$title}\".";
        
        if ($content) {
            $excerpt = substr(strip_tags($content), 0, 200);
            $prompt .= "\n\nContent excerpt: {$excerpt}";
        }
        
        $prompt .= "\n\nReturn ONLY the keywords separated by commas, nothing else.";
        
        return $this->callOpenAI($prompt, 100);
    }

    private function buildLongDescriptionPrompt(
        string $title,
        ?string $shortDescription,
        string $type
    ): string {
        $typeLabel = ucfirst($type);
        $prompt = "Write a detailed, engaging, and SEO-optimized long description (200-300 words) for this {$typeLabel}: \"{$title}\".";
        
        if ($shortDescription) {
            $prompt .= "\n\nShort description: {$shortDescription}";
        }
        
        $prompt .= "\n\nThe description should:
- Be informative and persuasive
- Highlight key features and benefits
- Include relevant keywords naturally
- Be written in a professional yet engaging tone
- Be formatted in paragraphs (use HTML <p> tags)
- Be approximately 200-300 words

Return ONLY the HTML-formatted description, nothing else.";
        
        return $prompt;
    }

    private function callOpenAI(string $prompt, int $maxTokens = 500): string
    {
        // Check if API key is configured
        if (empty($this->openaiApiKey)) {
            $this->logger->warning('OpenAI API key not configured');
            return $this->getFallbackContent($prompt);
        }

        try {
            $response = $this->httpClient->request('POST', self::OPENAI_API_URL, [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->openaiApiKey,
                    'Content-Type' => 'application/json',
                ],
                'json' => [
                    'model' => 'gpt-4o-mini',
                    'messages' => [
                        [
                            'role' => 'system',
                            'content' => 'You are a professional copywriter and SEO expert. Generate high-quality, engaging content that is optimized for search engines and user experience.'
                        ],
                        [
                            'role' => 'user',
                            'content' => $prompt
                        ]
                    ],
                    'max_tokens' => $maxTokens,
                    'temperature' => 0.7,
                ],
                'timeout' => 30,
            ]);

            $data = $response->toArray();
            
            if (isset($data['choices'][0]['message']['content'])) {
                return trim($data['choices'][0]['message']['content']);
            }

            $this->logger->error('Unexpected OpenAI response format', ['response' => $data]);
            return $this->getFallbackContent($prompt);

        } catch (\Exception $e) {
            $this->logger->error('OpenAI API call failed', [
                'error' => $e->getMessage(),
                'prompt' => substr($prompt, 0, 100)
            ]);
            return $this->getFallbackContent($prompt);
        }
    }

    private function getFallbackContent(string $prompt): string
    {
        if (str_contains($prompt, 'SEO-optimized title')) {
            return '[AI Generation Not Available - Please configure OpenAI API key]';
        }
        if (str_contains($prompt, 'meta description')) {
            return 'High-quality product with excellent features. Contact us for more information.';
        }
        if (str_contains($prompt, 'keywords')) {
            return 'product, quality, shop, buy, online';
        }
        return '<p>AI-generated content is not available. Please configure your OpenAI API key in the environment variables.</p>';
    }
}
