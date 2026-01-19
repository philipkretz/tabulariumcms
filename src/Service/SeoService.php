<?php
namespace App\Service;

class SeoService
{
    public function generateMetaTags(array $data): array
    {
        $defaults = [
            'title' => 'TabulariumCMS - Professional E-commerce Platform',
            'description' => 'Powerful, AI-driven e-commerce CMS with multi-vendor support, visual editor, and advanced features',
            'keywords' => 'cms, e-commerce, online shop, marketplace, multi-vendor',
            'og_image' => '/assets/og-image.jpg',
            'canonical' => null,
            'robots' => 'index, follow',
        ];

        $meta = array_merge($defaults, $data);

        return [
            'title' => $this->sanitizeTitle($meta['title']),
            'description' => $this->sanitizeDescription($meta['description']),
            'keywords' => $meta['keywords'],
            'og_title' => $meta['title'],
            'og_description' => $this->sanitizeDescription($meta['description']),
            'og_image' => $meta['og_image'],
            'og_type' => $meta['og_type'] ?? 'website',
            'twitter_card' => 'summary_large_image',
            'canonical' => $meta['canonical'],
            'robots' => $meta['robots'],
        ];
    }

    public function generateStructuredData(string $type, array $data): array
    {
        return match($type) {
            'Organization' => $this->getOrganizationSchema($data),
            'WebSite' => $this->getWebSiteSchema($data),
            'Product' => $this->getProductSchema($data),
            'Article' => $this->getArticleSchema($data),
            'BreadcrumbList' => $this->getBreadcrumbSchema($data),
            'FAQPage' => $this->getFAQSchema($data),
            default => [],
        };
    }

    private function getOrganizationSchema(array $data): array
    {
        return [
            '@context' => 'https://schema.org',
            '@type' => 'Organization',
            'name' => $data['name'] ?? 'TabulariumCMS',
            'url' => $data['url'] ?? 'https://example.com',
            'logo' => $data['logo'] ?? 'https://example.com/logo.png',
            'description' => $data['description'] ?? 'Professional e-commerce platform',
            'contactPoint' => [
                '@type' => 'ContactPoint',
                'telephone' => $data['phone'] ?? '+1-000-000-0000',
                'contactType' => 'customer service',
            ],
            'sameAs' => $data['social'] ?? [],
        ];
    }

    private function getWebSiteSchema(array $data): array
    {
        return [
            '@context' => 'https://schema.org',
            '@type' => 'WebSite',
            'name' => $data['name'] ?? 'TabulariumCMS',
            'url' => $data['url'] ?? 'https://example.com',
            'potentialAction' => [
                '@type' => 'SearchAction',
                'target' => [
                    '@type' => 'EntryPoint',
                    'urlTemplate' => ($data['url'] ?? 'https://example.com') . '/search?q={search_term_string}',
                ],
                'query-input' => 'required name=search_term_string',
            ],
        ];
    }

    private function getProductSchema(array $data): array
    {
        $schema = [
            '@context' => 'https://schema.org',
            '@type' => 'Product',
            'name' => $data['name'],
            'description' => $data['description'] ?? '',
            'image' => $data['image'] ?? [],
            'sku' => $data['sku'] ?? '',
        ];

        if (isset($data['price'])) {
            $schema['offers'] = [
                '@type' => 'Offer',
                'price' => $data['price'],
                'priceCurrency' => $data['currency'] ?? 'USD',
                'availability' => 'https://schema.org/InStock',
                'url' => $data['url'] ?? '',
            ];
        }

        if (isset($data['rating'])) {
            $schema['aggregateRating'] = [
                '@type' => 'AggregateRating',
                'ratingValue' => $data['rating'],
                'reviewCount' => $data['reviewCount'] ?? 1,
            ];
        }

        return $schema;
    }

    private function getArticleSchema(array $data): array
    {
        return [
            '@context' => 'https://schema.org',
            '@type' => 'Article',
            'headline' => $data['title'],
            'description' => $data['description'] ?? '',
            'image' => $data['image'] ?? [],
            'author' => [
                '@type' => 'Person',
                'name' => $data['author'] ?? 'Admin',
            ],
            'publisher' => [
                '@type' => 'Organization',
                'name' => $data['publisher'] ?? 'TabulariumCMS',
                'logo' => [
                    '@type' => 'ImageObject',
                    'url' => $data['publisher_logo'] ?? '',
                ],
            ],
            'datePublished' => $data['published'] ?? date('c'),
            'dateModified' => $data['modified'] ?? date('c'),
        ];
    }

    private function getBreadcrumbSchema(array $items): array
    {
        $listItems = [];
        foreach ($items as $index => $item) {
            $listItems[] = [
                '@type' => 'ListItem',
                'position' => $index + 1,
                'name' => $item['name'],
                'item' => $item['url'] ?? null,
            ];
        }

        return [
            '@context' => 'https://schema.org',
            '@type' => 'BreadcrumbList',
            'itemListElement' => $listItems,
        ];
    }

    private function getFAQSchema(array $items): array
    {
        $questions = [];
        foreach ($items as $item) {
            $questions[] = [
                '@type' => 'Question',
                'name' => $item['question'],
                'acceptedAnswer' => [
                    '@type' => 'Answer',
                    'text' => $item['answer'],
                ],
            ];
        }

        return [
            '@context' => 'https://schema.org',
            '@type' => 'FAQPage',
            'mainEntity' => $questions,
        ];
    }

    private function sanitizeTitle(string $title): string
    {
        $title = strip_tags($title);
        return mb_strlen($title) > 60 ? mb_substr($title, 0, 57) . '...' : $title;
    }

    private function sanitizeDescription(string $description): string
    {
        $description = strip_tags($description);
        return mb_strlen($description) > 160 ? mb_substr($description, 0, 157) . '...' : $description;
    }

    public function generateCanonicalUrl(string $url): string
    {
        return rtrim($url, '/' );
    }

    public function generateSitemap(array $pages): string
    {
        $xml = '<?xml version="1.0" encoding="UTF-8"?>';
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';

        foreach ($pages as $page) {
            $xml .= '<url>';
            $xml .= '<loc>' . htmlspecialchars($page['url']) . '</loc>';
            $xml .= '<lastmod>' . ($page['lastmod'] ?? date('Y-m-d')) . '</lastmod>';
            $xml .= '<changefreq>' . ($page['changefreq'] ?? 'weekly') . '</changefreq>';
            $xml .= '<priority>' . ($page['priority'] ?? '0.5') . '</priority>';
            $xml .= '</url>';
        }

        $xml .= '</urlset>';
        return $xml;
    }

    public function generateRobotsTxt(array $config): string
    {
        $txt = "User-agent: *\n";
        $txt .= "Allow: /\n";
        
        foreach ($config['disallow'] ?? [] as $path) {
            $txt .= "Disallow: {$path}\n";
        }

        $txt .= "\nSitemap: " . ($config['sitemap'] ?? 'https://example.com/sitemap.xml');
        
        return $txt;
    }
}