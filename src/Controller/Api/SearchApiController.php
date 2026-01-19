<?php

namespace App\Controller\Api;

use App\Service\SearchService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\RateLimiter\RateLimiterFactory;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/search', name: 'api_search_', priority: 10)]
class SearchApiController extends AbstractController
{
    public function __construct(
        private SearchService $searchService
    ) {
    }

    /**
     * Search API endpoint with rate limiting
     *
     * @param Request $request
     * @param RateLimiterFactory $searchApiLimiter
     * @return JsonResponse
     */
    #[Route('', name: 'query', methods: ['GET'])]
    public function search(Request $request, RateLimiterFactory $searchApiLimiter): JsonResponse
    {
        // Rate limiting: 10 requests per minute per IP
        $limiter = $searchApiLimiter->create($request->getClientIp());

        if (!$limiter->consume(1)->isAccepted()) {
            return $this->json([
                'success' => false,
                'error' => 'Rate limit exceeded. Please try again in a minute.',
                'data' => []
            ], Response::HTTP_TOO_MANY_REQUESTS);
        }

        // Get and sanitize search query
        $query = $request->query->get('q', '');
        $query = $this->sanitizeInput($query);

        // Validation
        if (strlen($query) < 2) {
            return $this->json([
                'success' => false,
                'error' => 'Search query must be at least 2 characters long.',
                'data' => []
            ], Response::HTTP_BAD_REQUEST);
        }

        if (strlen($query) > 100) {
            return $this->json([
                'success' => false,
                'error' => 'Search query must not exceed 100 characters.',
                'data' => []
            ], Response::HTTP_BAD_REQUEST);
        }

        // Get limit parameter (default: 5, max: 20)
        $limit = min(20, max(1, (int) $request->query->get('limit', 5)));

        try {
            // Perform search
            $results = $this->searchService->searchAll($query, $limit);

            return $this->json([
                'success' => true,
                'query' => $query,
                'data' => $results,
                'total' => $results['total']
            ]);
        } catch (\Exception $e) {
            // Log error in production
            // $this->logger->error('Search error', ['exception' => $e]);

            return $this->json([
                'success' => false,
                'error' => 'An error occurred while searching. Please try again.',
                'data' => []
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Autocomplete suggestions endpoint
     *
     * @param Request $request
     * @param RateLimiterFactory $searchApiLimiter
     * @return JsonResponse
     */
    #[Route('/suggestions', name: 'suggestions', methods: ['GET'])]
    public function suggestions(Request $request, RateLimiterFactory $searchApiLimiter): JsonResponse
    {
        // Rate limiting
        $limiter = $searchApiLimiter->create($request->getClientIp());

        if (!$limiter->consume(1)->isAccepted()) {
            return $this->json([
                'success' => false,
                'error' => 'Rate limit exceeded.',
                'data' => []
            ], Response::HTTP_TOO_MANY_REQUESTS);
        }

        // Get and sanitize search query
        $query = $request->query->get('q', '');
        $query = $this->sanitizeInput($query);

        // Validation
        if (strlen($query) < 2) {
            return $this->json([
                'success' => true,
                'data' => []
            ]);
        }

        if (strlen($query) > 100) {
            return $this->json([
                'success' => false,
                'error' => 'Query too long.',
                'data' => []
            ], Response::HTTP_BAD_REQUEST);
        }

        // Get limit parameter (default: 10, max: 20)
        $limit = min(20, max(1, (int) $request->query->get('limit', 10)));

        try {
            // Get suggestions
            $suggestions = $this->searchService->getSuggestions($query, $limit);

            return $this->json([
                'success' => true,
                'query' => $query,
                'data' => $suggestions
            ]);
        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'error' => 'An error occurred.',
                'data' => []
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Sanitize user input to prevent XSS and injection attacks
     *
     * @param string $input
     * @return string
     */
    private function sanitizeInput(string $input): string
    {
        // Remove HTML tags
        $input = strip_tags($input);

        // Remove potentially dangerous characters
        $input = str_replace(['<', '>', '"', "'", '\\'], '', $input);

        // Trim whitespace
        $input = trim($input);

        // Remove multiple consecutive spaces
        $input = preg_replace('/\s+/', ' ', $input);

        return $input;
    }
}
