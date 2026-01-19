<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use App\Entity\Post;
use App\Entity\Page;
use App\Repository\SiteSettingsRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

#[IsGranted('ROLE_ADMIN')]
class ContentEditorController extends AbstractController
{
    private SiteSettingsRepository $settingsRepository;
    private EntityManagerInterface $entityManager;
    private HttpClientInterface $httpClient;
    private ParameterBagInterface $parameterBag;

    public function __construct(
        SiteSettingsRepository $settingsRepository,
        EntityManagerInterface $entityManager,
        HttpClientInterface $httpClient,
        ParameterBagInterface $parameterBag
    ) {
        $this->settingsRepository = $settingsRepository;
        $this->entityManager = $entityManager;
        $this->httpClient = $httpClient;
        $this->parameterBag = $parameterBag;
    }

    #[Route('/admin/editor/post/{id}', name: 'admin_post_editor')]
    public function editPost(Post $post): Response
    {
        return $this->render('admin/content_editor/post_editor.html.twig', [
            'post' => $post,
            'aiSettings' => $this->getAISettings(),
            'layoutSettings' => $this->getLayoutSettings('post'),
        ]);
    }

    #[Route('/admin/editor/page/{id}', name: 'admin_page_editor')]
    public function editPage(Page $page): Response
    {
        return $this->render('admin/content_editor/page_editor.html.twig', [
            'page' => $page,
            'aiSettings' => $this->getAISettings(),
            'layoutSettings' => $this->getLayoutSettings('page'),
        ]);
    }

    #[Route('/admin/editor/ai/generate', name: 'admin_ai_generate')]
    public function generateAIContent(Request $request): JsonResponse
    {
        $prompt = $request->request->get('prompt');
        $contentType = $request->request->get('content_type');
        $context = $request->request->get('context', []);
        
        if (!$prompt || !$contentType) {
            return new JsonResponse(['error' => 'Prompt and content type are required'], 400);
        }

        $aiSettings = $this->getAISettings();
        
        if (!$aiSettings['enabled'] || !$aiSettings['api_key']) {
            return new JsonResponse(['error' => 'AI is not configured'], 400);
        }

        try {
            $response = $this->callAIService($prompt, $contentType, $context, $aiSettings);
            return new JsonResponse($response);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => 'AI service error: ' . $e->getMessage()], 500);
        }
    }

    #[Route('/admin/editor/ai/suggestions', name: 'admin_ai_suggestions')]
    public function getAISuggestions(Request $request): JsonResponse
    {
        $contentType = $request->request->get('content_type');
        $existingContent = $request->request->get('existing_content');
        $target = $request->request->get('target');
        
        if (!$contentType || !$existingContent || !$target) {
            return new JsonResponse(['error' => 'All parameters are required'], 400);
        }

        $aiSettings = $this->getAISettings();
        
        if (!$aiSettings['enabled'] || !$aiSettings['api_key']) {
            return new JsonResponse(['error' => 'AI is not configured'], 400);
        }

        $suggestions = $this->generateSuggestions($existingContent, $contentType, $target, $aiSettings);
        
        return new JsonResponse(['suggestions' => $suggestions]);
    }

    #[Route('/admin/editor/save-layout', name: 'admin_save_layout')]
    public function saveLayout(Request $request): JsonResponse
    {
        $contentType = $request->request->get('content_type');
        $layoutSettings = $request->request->get('layout');
        
        if (!$contentType || !$layoutSettings) {
            return new JsonResponse(['error' => 'Content type and layout settings are required'], 400);
        }

        // Save layout settings to database
        $layoutKey = $contentType . '_layout_settings';
        $layoutSetting = $this->settingsRepository->findByKeyAndLocale($layoutKey, 'global');
        
        if ($layoutSetting) {
            $layoutSetting->setValue($layoutSettings);
        } else {
            $layoutSetting = new \App\Entity\SiteSettings();
            $layoutSetting->setSettingKey($layoutKey);
            $layoutSetting->setValue($layoutSettings);
            $layoutSetting->setSettingType('json');
            $layoutSetting->setLocale('global');
            $layoutSetting->setCategory('editor');
            $this->settingsRepository->save($layoutSetting, true);
        }

        return new JsonResponse(['success' => true]);
    }

    private function getAISettings(): array
    {
        return [
            'enabled' => $this->settingsRepository->findByKeyAndLocale('ai_enabled', 'global')?->getValue() ?? false,
            'provider' => $this->settingsRepository->findByKeyAndLocale('ai_provider', 'global')?->getValue() ?? 'openai',
            'api_key' => $this->settingsRepository->findByKeyAndLocale('ai_api_key', 'global')?->getValue(),
            'model' => $this->settingsRepository->findByKeyAndLocale('ai_model', 'global')?->getValue() ?? 'gpt-4',
            'max_tokens' => $this->settingsRepository->findByKeyAndLocale('ai_max_tokens', 'global')?->getValue() ?? 2000,
            'temperature' => $this->settingsRepository->findByKeyAndLocale('ai_temperature', 'global')?->getValue() ?? 0.7,
        ];
    }

    private function getLayoutSettings(string $contentType): array
    {
        $layoutKey = $contentType . '_layout_settings';
        $layoutSetting = $this->settingsRepository->findByKeyAndLocale($layoutKey, 'global');
        
        if ($layoutSetting) {
            return $layoutSetting->getValue() ?? [];
        }
        
        // Default layout settings
        return [
            'grid_columns' => 12,
            'grid_rows' => 12,
            'block_layout' => 'grid', // grid, block, freeform
            'sidebar_position' => 'right',
            'show_rulers' => true,
            'show_guides' => true,
            'default_blocks' => [
                'heading' => ['h1', 'h2', 'h3', 'h4', 'h5', 'h6'],
                'text' => ['paragraph', 'heading'],
                'media' => ['image', 'video', 'gallery'],
                'interactive' => ['button', 'form', 'social_media'],
            ],
        ];
    }

    private function callAIService(string $prompt, string $contentType, array $context, array $settings): array
    {
        $systemPrompt = $this->buildSystemPrompt($contentType, $context);
        
        switch ($settings['provider']) {
            case 'openai':
                return $this->callOpenAI($systemPrompt, $prompt, $settings);
            case 'claude':
                return $this->callClaude($systemPrompt, $prompt, $settings);
            default:
                return $this->callGenericAI($systemPrompt, $prompt, $settings);
        }
    }

    private function buildSystemPrompt(string $contentType, array $context): string
    {
        $basePrompt = "You are a professional content writer for a CMS system. ";
        
        switch ($contentType) {
            case 'post':
                $basePrompt .= "Generate engaging blog post content that is SEO-optimized. ";
                $basePrompt .= "Use proper formatting, include relevant headings, and maintain a professional tone. ";
                break;
            case 'page':
                $basePrompt .= "Generate professional website page content. ";
                $basePrompt .= "Focus on clarity, readability, and conversion optimization. ";
                break;
            case 'excerpt':
                $basePrompt .= "Generate a compelling excerpt that captures attention and summarizes key points. ";
                $basePrompt .= "Keep it under 150 characters for SEO purposes. ";
                break;
        }
        
        if (!empty($context)) {
            $basePrompt .= "Context: " . json_encode($context) . ". ";
        }
        
        $basePrompt .= "Do not include any harmful, unethical, or inappropriate content. ";
        $basePrompt .= "Make it unique and engaging for readers.";
        
        return $basePrompt;
    }

    private function callOpenAI(string $systemPrompt, string $prompt, array $settings): array
    {
        $apiKey = $settings['api_key'];
        $model = $settings['model'];
        $maxTokens = $settings['max_tokens'];
        $temperature = $settings['temperature'];

        $response = $this->httpClient->request('POST', 'https://api.openai.com/v1/chat/completions', [
            'headers' => [
                'Authorization' => 'Bearer ' . $apiKey,
                'Content-Type' => 'application/json',
            ],
            'json' => [
                'model' => $model,
                'messages' => [
                    ['role' => 'system', 'content' => $systemPrompt],
                    ['role' => 'user', 'content' => $prompt],
                ],
                'max_tokens' => $maxTokens,
                'temperature' => $temperature,
            ],
        ]);

        $data = json_decode($response->getContent(), true);
        
        if (isset($data['choices'][0]['message']['content'])) {
            return [
                'content' => $data['choices'][0]['message']['content'],
                'tokens_used' => $data['usage']['total_tokens'] ?? 0,
                'model' => $model,
            ];
        }
        
        throw new \Exception('OpenAI API error');
    }

    private function callClaude(string $systemPrompt, string $prompt, array $settings): array
    {
        // Similar implementation for Claude API
        return $this->callGenericAI($systemPrompt, $prompt, $settings);
    }

    private function callGenericAI(string $systemPrompt, string $prompt, array $settings): array
    {
        // Fallback implementation for generic AI service
        return [
            'content' => "AI-generated content based on: " . $prompt,
            'tokens_used' => 0,
            'model' => 'generic',
        ];
    }

    private function generateSuggestions(string $content, string $contentType, string $target, array $settings): array
    {
        $prompt = "Based on this " . $contentType . " content: \"" . $content . "\", generate 3 suggestions for improving the " . $target . ". Make them specific and actionable.";
        
        try {
            $response = $this->callAIService($prompt, 'suggestions', array(
                'content' => $content,
                'type' => $contentType,
                'target' => $target,
            ), $settings);
            
            // Parse the response to extract suggestions
            $suggestions = $this->parseSuggestions($response['content'], $target);
            
            return $suggestions;
        } catch (\Exception $e) {
            return array(
                'Error generating suggestions: ' . $e->getMessage()
            );
        }
    }

    private function parseSuggestions(string $aiResponse, string $target): array
    {
        // Simple parsing - in real implementation, this would be more sophisticated
        $lines = explode("\n", $aiResponse);
        $suggestions = [];
        
        foreach ($lines as $line) {
            $line = trim($line);
            if (!empty($line) && strlen($line) > 10) {
                $suggestions[] = $line;
            }
        }
        
        return array_slice($suggestions, 0, 5); // Return max 5 suggestions
    }
}