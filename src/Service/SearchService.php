<?php

namespace App\Service;

use Elastica\Query;
use Elastica\Query\BoolQuery;
use Elastica\Query\MultiMatch;
use Elastica\Query\Term;
use FOS\ElasticaBundle\Finder\FinderInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class SearchService
{
    public function __construct(
        private FinderInterface $articlesFinder,
        private FinderInterface $postsFinder,
        private FinderInterface $pagesFinder,
        private FinderInterface $categoriesFinder,
        private UrlGeneratorInterface $urlGenerator
    ) {
    }

    /**
     * Search across all indexes
     *
     * @param string $searchTerm
     * @param int $limit Results per entity type
     * @return array Grouped results by entity type
     */
    public function searchAll(string $searchTerm, int $limit = 5): array
    {
        if (strlen($searchTerm) < 2) {
            return [
                'articles' => [],
                'posts' => [],
                'pages' => [],
                'categories' => [],
                'total' => 0
            ];
        }

        $results = [
            'articles' => $this->searchArticles($searchTerm, $limit),
            'posts' => $this->searchPosts($searchTerm, $limit),
            'pages' => $this->searchPages($searchTerm, $limit),
            'categories' => $this->searchCategories($searchTerm, $limit),
        ];

        $results['total'] = count($results['articles'])
            + count($results['posts'])
            + count($results['pages'])
            + count($results['categories']);

        return $results;
    }

    /**
     * Search articles/products
     */
    public function searchArticles(string $searchTerm, int $limit = 5): array
    {
        // Create MultiMatch query
        $multiMatch = new MultiMatch();
        $multiMatch->setQuery($searchTerm);
        $multiMatch->setFields([
            'name^3',
            'sku^2',
            'shortDescription^0.8',
            'description^0.5',
            'categoryName^2'
        ]);
        $multiMatch->setFuzziness('AUTO');
        $multiMatch->setParam('prefix_length', 1);

        // Create Bool query with filter
        $boolQuery = new BoolQuery();
        $boolQuery->addMust($multiMatch);
        $boolQuery->addFilter(new Term(['isActive' => true]));

        // Create main Query object
        $query = new Query($boolQuery);
        $query->setSize($limit);

        $articles = $this->articlesFinder->find($query);

        // phpcs:ignore Security.BadFunctions.CallbackFunctions -- Safe inline closure
        return array_map(function($article) {
            $imageUrl = null;
            $imageAlt = $article->getName();

            if ($article->getMainImage()) {
                $imageUrl = '/uploads/media/' . $article->getMainImage()->getFilename();
                $imageAlt = $article->getMainImage()->getAlt() ?? $article->getName();
            }

            $inStock = $article->getIgnoreStock() || $article->getStock() > 0;

            return [
                'id' => $article->getId(),
                'type' => 'product',
                'name' => $article->getName(),
                'description' => $article->getShortDescription()
                    ? substr(strip_tags($article->getShortDescription()), 0, 150)
                    : null,
                'grossPrice' => (float) $article->getGrossPrice(),
                'taxRate' => (float) $article->getTaxRate(),
                'sku' => $article->getSku(),
                'inStock' => $inStock,
                'categoryName' => $article->getCategory()?->getName(),
                'image' => $imageUrl,
                'imageAlt' => $imageAlt,
                'detailUrl' => $this->urlGenerator->generate('app_product_detail', [
                    'slug' => $article->getSlug()
                ], UrlGeneratorInterface::ABSOLUTE_PATH)
            ];
        }, $articles);
    }

    /**
     * Search blog posts
     */
    public function searchPosts(string $searchTerm, int $limit = 5): array
    {
        // Create MultiMatch query
        $multiMatch = new MultiMatch();
        $multiMatch->setQuery($searchTerm);
        $multiMatch->setFields([
            'title^3',
            'excerpt^1.5',
            'content^1.0',
            'metaDescription^0.8'
        ]);
        $multiMatch->setFuzziness('AUTO');
        $multiMatch->setParam('prefix_length', 1);

        // Create Bool query with filter
        $boolQuery = new BoolQuery();
        $boolQuery->addMust($multiMatch);
        $boolQuery->addFilter(new Term(['status' => 'published']));

        // Create main Query object
        $query = new Query($boolQuery);
        $query->setSize($limit);

        $posts = $this->postsFinder->find($query);

        // phpcs:ignore Security.BadFunctions.CallbackFunctions -- Safe inline closure
        return array_map(function($post) {
            $imageUrl = null;

            if ($post->getFeaturedImage()) {
                $imageUrl = '/uploads/media/' . $post->getFeaturedImage()->getFilename();
            }

            return [
                'id' => $post->getId(),
                'type' => 'post',
                'title' => $post->getTitle(),
                'excerpt' => $post->getExcerpt()
                    ? substr(strip_tags($post->getExcerpt()), 0, 150)
                    : null,
                'authorName' => $post->getAuthor()?->getEmail(),
                'image' => $imageUrl,
                'publishedAt' => $post->getPublishedAt()?->format('Y-m-d'),
                'detailUrl' => $this->urlGenerator->generate('app_blog_detail', [
                    'slug' => $post->getSlug()
                ], UrlGeneratorInterface::ABSOLUTE_PATH)
            ];
        }, $posts);
    }

    /**
     * Search CMS pages
     */
    public function searchPages(string $searchTerm, int $limit = 5): array
    {
        // Create MultiMatch query
        $multiMatch = new MultiMatch();
        $multiMatch->setQuery($searchTerm);
        $multiMatch->setFields([
            'title^3',
            'content^1.0',
            'metaDescription^0.8',
            'categoryName'
        ]);
        $multiMatch->setFuzziness('AUTO');
        $multiMatch->setParam('prefix_length', 1);

        // Create Bool query with filter
        $boolQuery = new BoolQuery();
        $boolQuery->addMust($multiMatch);
        $boolQuery->addFilter(new Term(['isPublished' => true]));

        // Create main Query object
        $query = new Query($boolQuery);
        $query->setSize($limit);

        $pages = $this->pagesFinder->find($query);

        // phpcs:ignore Security.BadFunctions.CallbackFunctions -- Safe inline closure
        return array_map(function($page) {
            return [
                'id' => $page->getId(),
                'type' => 'page',
                'title' => $page->getTitle(),
                'excerpt' => $page->getMetaDescription()
                    ? substr(strip_tags($page->getMetaDescription()), 0, 150)
                    : substr(strip_tags($page->getContent()), 0, 150),
                'categoryName' => $page->getCategory()?->getName(),
                'detailUrl' => $this->urlGenerator->generate('app_page_show', [
                    'slug' => $page->getSlug()
                ], UrlGeneratorInterface::ABSOLUTE_PATH)
            ];
        }, $pages);
    }

    /**
     * Search categories
     */
    public function searchCategories(string $searchTerm, int $limit = 5): array
    {
        // Create MultiMatch query
        $multiMatch = new MultiMatch();
        $multiMatch->setQuery($searchTerm);
        $multiMatch->setFields([
            'name^3',
            'description^1.0'
        ]);
        $multiMatch->setFuzziness('AUTO');
        $multiMatch->setParam('prefix_length', 1);

        // Create Bool query with filter
        $boolQuery = new BoolQuery();
        $boolQuery->addMust($multiMatch);
        $boolQuery->addFilter(new Term(['isActive' => true]));

        // Create main Query object
        $query = new Query($boolQuery);
        $query->setSize($limit);

        $categories = $this->categoriesFinder->find($query);

        // phpcs:ignore Security.BadFunctions.CallbackFunctions -- Safe inline closure
        return array_map(function($category) {
            return [
                'id' => $category->getId(),
                'type' => 'category',
                'name' => $category->getName(),
                'description' => $category->getDescription()
                    ? substr(strip_tags($category->getDescription()), 0, 150)
                    : null,
                'detailUrl' => $this->urlGenerator->generate('app_category_show', [
                    'slug' => $category->getSlug()
                ], UrlGeneratorInterface::ABSOLUTE_PATH)
            ];
        }, $categories);
    }

    /**
     * Get search suggestions (autocomplete)
     */
    public function getSuggestions(string $searchTerm, int $limit = 10): array
    {
        if (strlen($searchTerm) < 2) {
            return [];
        }

        // Quick search for name/title matches only
        $results = $this->searchAll($searchTerm, $limit);

        $suggestions = [];

        // Add product names
        foreach ($results['articles'] as $article) {
            $suggestions[] = [
                'text' => $article['name'],
                'type' => 'product',
                'url' => $article['detailUrl']
            ];
        }

        // Add post titles
        foreach ($results['posts'] as $post) {
            $suggestions[] = [
                'text' => $post['title'],
                'type' => 'post',
                'url' => $post['detailUrl']
            ];
        }

        // Add page titles
        foreach ($results['pages'] as $page) {
            $suggestions[] = [
                'text' => $page['title'],
                'type' => 'page',
                'url' => $page['detailUrl']
            ];
        }

        // Add category names
        foreach ($results['categories'] as $category) {
            $suggestions[] = [
                'text' => $category['name'],
                'type' => 'category',
                'url' => $category['detailUrl']
            ];
        }

        return array_slice($suggestions, 0, $limit);
    }
}
