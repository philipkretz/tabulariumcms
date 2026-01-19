<?php

namespace App\Controller\Admin;

use App\Service\AiContentGeneratorService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin/ai')]
class AiContentController extends AbstractController
{
    public function __construct(
        private AiContentGeneratorService $aiGenerator
    ) {
    }

    #[Route('/generate-description', name: 'admin_ai_generate_description', methods: ['POST'])]
    public function generateDescription(Request $request): JsonResponse
    {
        $title = $request->request->get('title', '');
        $shortDescription = $request->request->get('shortDescription');
        $type = $request->request->get('type', 'product');

        if (empty($title)) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Title is required'
            ], 400);
        }

        try {
            $content = $this->aiGenerator->generateLongDescription($title, $shortDescription, $type);
            
            return new JsonResponse([
                'success' => true,
                'content' => $content
            ]);
        } catch (\Exception $e) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Failed to generate content: ' . $e->getMessage()
            ], 500);
        }
    }

    #[Route('/generate-seo-title', name: 'admin_ai_generate_seo_title', methods: ['POST'])]
    public function generateSeoTitle(Request $request): JsonResponse
    {
        $title = $request->request->get('title', '');
        $type = $request->request->get('type', 'product');

        if (empty($title)) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Title is required'
            ], 400);
        }

        try {
            $seoTitle = $this->aiGenerator->generateSeoTitle($title, $type);
            
            return new JsonResponse([
                'success' => true,
                'content' => $seoTitle
            ]);
        } catch (\Exception $e) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Failed to generate SEO title: ' . $e->getMessage()
            ], 500);
        }
    }

    #[Route('/generate-seo-description', name: 'admin_ai_generate_seo_description', methods: ['POST'])]
    public function generateSeoDescription(Request $request): JsonResponse
    {
        $title = $request->request->get('title', '');
        $content = $request->request->get('content');
        $type = $request->request->get('type', 'product');

        if (empty($title)) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Title is required'
            ], 400);
        }

        try {
            $seoDescription = $this->aiGenerator->generateSeoDescription($title, $content, $type);
            
            return new JsonResponse([
                'success' => true,
                'content' => $seoDescription
            ]);
        } catch (\Exception $e) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Failed to generate SEO description: ' . $e->getMessage()
            ], 500);
        }
    }

    #[Route('/generate-seo-keywords', name: 'admin_ai_generate_seo_keywords', methods: ['POST'])]
    public function generateSeoKeywords(Request $request): JsonResponse
    {
        $title = $request->request->get('title', '');
        $content = $request->request->get('content');
        $type = $request->request->get('type', 'product');

        if (empty($title)) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Title is required'
            ], 400);
        }

        try {
            $seoKeywords = $this->aiGenerator->generateSeoKeywords($title, $content, $type);
            
            return new JsonResponse([
                'success' => true,
                'content' => $seoKeywords
            ]);
        } catch (\Exception $e) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Failed to generate SEO keywords: ' . $e->getMessage()
            ], 500);
        }
    }
}
