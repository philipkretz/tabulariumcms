<?php

namespace App\DataFixtures;

use App\Entity\Article;
use App\Entity\Language;
use App\Repository\LanguageRepository;
use App\Repository\ArticleRepository;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class ArticleFixtures extends Fixture implements DependentFixtureInterface, FixtureGroupInterface
{
    public static function getGroups(): array
    {
        return ['article'];
    }
    public function __construct(
        private LanguageRepository $languageRepository,
        private ArticleRepository $articleRepository
    ) {
    }

    public function getDependencies(): array
    {
        return [
            CategoryFixtures::class,
            LanguageFixtures::class,
        ];
    }

    public function load(ObjectManager $manager): void
    {
        // Get default language
        $defaultLanguage = $this->languageRepository->findOneBy(['isDefault' => true])
            ?? $this->languageRepository->findOneBy(['code' => 'en']);

        // Define 30 diverse products
        $products = [
            // Electronics (5 products)
            [
                'name' => 'Wireless Bluetooth Headphones',
                'slug' => 'wireless-bluetooth-headphones',
                'shortDescription' => 'Premium noise-cancelling over-ear headphones with 30-hour battery life',
                'description' => 'Experience crystal-clear audio with active noise cancellation and superior comfort for all-day wear.',
                'netPrice' => '82.64',
                'taxRate' => '21.00',
                'stock' => 45,
                'sku' => 'ELEC-WBH-001',
                'type' => Article::TYPE_PHYSICAL,
                'category' => 'electronics',
                'isFeatured' => true,
            ],
            [
                'name' => 'Smartphone 5G Pro',
                'slug' => 'smartphone-5g-pro',
                'shortDescription' => 'Latest 5G smartphone with triple camera and 128GB storage',
                'description' => 'Cutting-edge mobile technology with AI-enhanced photography and lightning-fast performance.',
                'netPrice' => '413.22',
                'taxRate' => '21.00',
                'stock' => 28,
                'sku' => 'ELEC-SP5G-002',
                'type' => Article::TYPE_PHYSICAL,
                'category' => 'electronics',
                'isFeatured' => false,
            ],
            [
                'name' => 'Laptop Stand Aluminum',
                'slug' => 'laptop-stand-aluminum',
                'shortDescription' => 'Ergonomic adjustable laptop stand for better posture',
                'description' => 'Premium aluminum construction with 6 height levels for optimal viewing angle.',
                'netPrice' => '28.92',
                'taxRate' => '21.00',
                'stock' => 120,
                'sku' => 'ELEC-LSA-003',
                'type' => Article::TYPE_PHYSICAL,
                'category' => 'electronics',
                'isFeatured' => false,
            ],
            [
                'name' => 'USB-C Hub 7-in-1',
                'slug' => 'usb-c-hub-7-in-1',
                'shortDescription' => 'Multiport adapter with HDMI, USB 3.0, and card readers',
                'description' => 'Expand your connectivity with this versatile hub for modern laptops.',
                'netPrice' => '33.06',
                'taxRate' => '21.00',
                'stock' => 0,
                'sku' => 'ELEC-UCH-004',
                'type' => Article::TYPE_PHYSICAL,
                'category' => 'electronics',
                'isFeatured' => false,
            ],
            [
                'name' => 'Gaming Software Bundle',
                'slug' => 'gaming-software-bundle',
                'shortDescription' => 'Collection of 10 indie games delivered digitally',
                'description' => 'Instant access to award-winning indie titles. Download codes sent via email.',
                'netPrice' => '41.32',
                'taxRate' => '21.00',
                'stock' => 0,
                'ignoreStock' => true,
                'sku' => 'ELEC-GSB-005',
                'type' => Article::TYPE_VIRTUAL,
                'category' => 'electronics',
                'isFeatured' => false,
            ],

            // Clothing (5 products)
            [
                'name' => 'Organic Cotton T-Shirt',
                'slug' => 'organic-cotton-tshirt',
                'shortDescription' => 'Eco-friendly unisex t-shirt in multiple colors',
                'description' => '100% certified organic cotton with comfortable fit and sustainable production.',
                'netPrice' => '20.66',
                'taxRate' => '21.00',
                'stock' => 85,
                'sku' => 'CLO-OCT-006',
                'type' => Article::TYPE_PHYSICAL,
                'category' => 'clothing',
                'isFeatured' => false,
            ],
            [
                'name' => 'Winter Parka Jacket',
                'slug' => 'winter-parka-jacket',
                'shortDescription' => 'Waterproof insulated jacket for extreme cold',
                'description' => 'Premium down-filled parka with removable hood and multiple pockets.',
                'netPrice' => '123.97',
                'taxRate' => '21.00',
                'stock' => 15,
                'sku' => 'CLO-WPJ-007',
                'type' => Article::TYPE_PHYSICAL,
                'category' => 'clothing',
                'isFeatured' => true,
            ],
            [
                'name' => 'Running Shoes Pro',
                'slug' => 'running-shoes-pro',
                'shortDescription' => 'Lightweight cushioned sneakers for marathon runners',
                'description' => 'Advanced foam technology with breathable mesh upper for maximum performance.',
                'netPrice' => '99.17',
                'taxRate' => '21.00',
                'stock' => 42,
                'sku' => 'CLO-RSP-008',
                'type' => Article::TYPE_PHYSICAL,
                'category' => 'clothing',
                'isFeatured' => false,
            ],
            [
                'name' => 'Leather Wallet',
                'slug' => 'leather-wallet',
                'shortDescription' => 'Handcrafted genuine leather bifold wallet',
                'description' => 'Timeless design with RFID protection and multiple card slots.',
                'netPrice' => '37.19',
                'taxRate' => '21.00',
                'stock' => 67,
                'sku' => 'CLO-LW-009',
                'type' => Article::TYPE_PHYSICAL,
                'category' => 'clothing',
                'isFeatured' => false,
            ],
            [
                'name' => 'Wool Blend Scarf',
                'slug' => 'wool-blend-scarf',
                'shortDescription' => 'Soft cashmere-blend winter scarf',
                'description' => 'Luxurious warmth with elegant fringe detail. Available in classic colors.',
                'netPrice' => '28.92',
                'taxRate' => '21.00',
                'stock' => 5,
                'sku' => 'CLO-WBS-010',
                'type' => Article::TYPE_PHYSICAL,
                'category' => 'clothing',
                'isFeatured' => false,
            ],

            // Books (5 products)
            [
                'name' => 'The Art of Programming',
                'slug' => 'art-of-programming',
                'shortDescription' => 'Comprehensive guide to modern software development',
                'description' => 'Master fundamental programming concepts with practical examples and best practices.',
                'netPrice' => '33.06',
                'taxRate' => '21.00',
                'stock' => 58,
                'sku' => 'BOOK-AOP-011',
                'type' => Article::TYPE_PHYSICAL,
                'category' => 'books',
                'isFeatured' => false,
            ],
            [
                'name' => 'Fiction Novel: Moonlit Path',
                'slug' => 'fiction-moonlit-path',
                'shortDescription' => 'Bestselling mystery thriller by award-winning author',
                'description' => 'A gripping tale of suspense that will keep you reading until dawn.',
                'netPrice' => '16.53',
                'taxRate' => '21.00',
                'stock' => 92,
                'sku' => 'BOOK-FMP-012',
                'type' => Article::TYPE_PHYSICAL,
                'category' => 'books',
                'isFeatured' => false,
            ],
            [
                'name' => 'Cookbook: Mediterranean Delights',
                'slug' => 'cookbook-mediterranean',
                'shortDescription' => '150 authentic recipes from the Mediterranean region',
                'description' => 'Discover healthy and delicious dishes with beautiful full-color photography.',
                'netPrice' => '24.79',
                'taxRate' => '21.00',
                'stock' => 34,
                'sku' => 'BOOK-CMD-013',
                'type' => Article::TYPE_PHYSICAL,
                'category' => 'books',
                'isFeatured' => true,
            ],
            [
                'name' => 'E-Book: Digital Marketing 2026',
                'slug' => 'ebook-digital-marketing',
                'shortDescription' => 'Latest strategies for online business success',
                'description' => 'PDF download with actionable insights for social media, SEO, and content marketing.',
                'netPrice' => '12.40',
                'taxRate' => '21.00',
                'stock' => 0,
                'ignoreStock' => true,
                'sku' => 'BOOK-EDM-014',
                'type' => Article::TYPE_VIRTUAL,
                'category' => 'books',
                'isFeatured' => false,
            ],
            [
                'name' => 'Children\'s Book Bundle',
                'slug' => 'childrens-book-bundle',
                'shortDescription' => 'Set of 5 illustrated storybooks for ages 4-8',
                'description' => 'Curated collection of award-winning children\'s literature with vibrant illustrations.',
                'netPrice' => '41.32',
                'taxRate' => '21.00',
                'stock' => 23,
                'sku' => 'BOOK-CBB-015',
                'type' => Article::TYPE_BUNDLE,
                'category' => 'books',
                'isFeatured' => false,
            ],

            // Home & Garden (5 products)
            [
                'name' => 'Indoor Plant Set',
                'slug' => 'indoor-plant-set',
                'shortDescription' => '3 low-maintenance houseplants with decorative pots',
                'description' => 'Purify your air with these easy-care plants perfect for any room.',
                'netPrice' => '37.19',
                'taxRate' => '21.00',
                'stock' => 18,
                'sku' => 'HOME-IPS-016',
                'type' => Article::TYPE_PHYSICAL,
                'category' => 'home-garden',
                'isFeatured' => false,
            ],
            [
                'name' => 'Ceramic Dinnerware Set',
                'slug' => 'ceramic-dinnerware-set',
                'shortDescription' => '16-piece modern dinnerware for 4 people',
                'description' => 'Durable stoneware with elegant minimalist design. Dishwasher and microwave safe.',
                'netPrice' => '66.11',
                'taxRate' => '21.00',
                'stock' => 31,
                'sku' => 'HOME-CDS-017',
                'type' => Article::TYPE_PHYSICAL,
                'category' => 'home-garden',
                'isFeatured' => false,
            ],
            [
                'name' => 'Electric Lawn Mower',
                'slug' => 'electric-lawn-mower',
                'shortDescription' => 'Cordless battery-powered mower for medium lawns',
                'description' => 'Quiet eco-friendly operation with 45-minute runtime and adjustable cutting heights.',
                'netPrice' => '206.61',
                'taxRate' => '21.00',
                'stock' => 8,
                'sku' => 'HOME-ELM-018',
                'type' => Article::TYPE_PHYSICAL,
                'category' => 'home-garden',
                'isFeatured' => false,
            ],
            [
                'name' => 'Aromatherapy Diffuser',
                'slug' => 'aromatherapy-diffuser',
                'shortDescription' => 'Ultrasonic essential oil diffuser with LED lights',
                'description' => 'Create a relaxing ambiance with mist and color therapy. 500ml capacity.',
                'netPrice' => '28.92',
                'taxRate' => '21.00',
                'stock' => 72,
                'sku' => 'HOME-AD-019',
                'type' => Article::TYPE_PHYSICAL,
                'category' => 'home-garden',
                'isFeatured' => false,
            ],
            [
                'name' => 'Smart Home Starter Kit',
                'slug' => 'smart-home-starter-kit',
                'shortDescription' => 'Bundle: smart bulbs, plug, and hub controller',
                'description' => 'Voice-controlled home automation. Compatible with Alexa and Google Home.',
                'netPrice' => '82.64',
                'taxRate' => '21.00',
                'stock' => 0,
                'sku' => 'HOME-SHSK-020',
                'type' => Article::TYPE_BUNDLE,
                'category' => 'home-garden',
                'isFeatured' => true,
            ],

            // Sports (5 products)
            [
                'name' => 'Yoga Mat Premium',
                'slug' => 'yoga-mat-premium',
                'shortDescription' => 'Non-slip eco-friendly yoga mat with carrying strap',
                'description' => 'Extra thick cushioning for comfort during any workout. 100% recyclable materials.',
                'netPrice' => '33.06',
                'taxRate' => '21.00',
                'stock' => 64,
                'sku' => 'SPORT-YMP-021',
                'type' => Article::TYPE_PHYSICAL,
                'category' => 'sports',
                'isFeatured' => false,
            ],
            [
                'name' => 'Mountain Bike 29"',
                'slug' => 'mountain-bike-29',
                'shortDescription' => 'Full-suspension trail bike with 21-speed gears',
                'description' => 'Conquer any terrain with this durable aluminum frame bike. Disc brakes included.',
                'netPrice' => '371.90',
                'taxRate' => '21.00',
                'stock' => 6,
                'sku' => 'SPORT-MTB-022',
                'type' => Article::TYPE_PHYSICAL,
                'category' => 'sports',
                'isFeatured' => false,
            ],
            [
                'name' => 'Resistance Bands Set',
                'slug' => 'resistance-bands-set',
                'shortDescription' => '5 strength training bands with different resistance levels',
                'description' => 'Portable home gym equipment for strength and flexibility training.',
                'netPrice' => '16.53',
                'taxRate' => '21.00',
                'stock' => 103,
                'sku' => 'SPORT-RBS-023',
                'type' => Article::TYPE_PHYSICAL,
                'category' => 'sports',
                'isFeatured' => false,
            ],
            [
                'name' => 'Tennis Racket Pro',
                'slug' => 'tennis-racket-pro',
                'shortDescription' => 'Professional-grade graphite tennis racket',
                'description' => 'Tournament-approved with optimal sweet spot for power and control.',
                'netPrice' => '123.97',
                'taxRate' => '21.00',
                'stock' => 19,
                'sku' => 'SPORT-TRP-024',
                'type' => Article::TYPE_PHYSICAL,
                'category' => 'sports',
                'isFeatured' => false,
            ],
            [
                'name' => 'Online Fitness Classes',
                'slug' => 'online-fitness-classes',
                'shortDescription' => '3-month subscription to live-streamed workout sessions',
                'description' => 'Access to 100+ classes including yoga, HIIT, pilates, and strength training.',
                'netPrice' => '49.59',
                'taxRate' => '21.00',
                'stock' => 0,
                'ignoreStock' => true,
                'sku' => 'SPORT-OFC-025',
                'type' => Article::TYPE_VIRTUAL,
                'category' => 'sports',
                'isFeatured' => true,
            ],

            // Toys (5 products)
            [
                'name' => 'Building Blocks Set 500pc',
                'slug' => 'building-blocks-500',
                'shortDescription' => 'Colorful construction blocks for creative play',
                'description' => 'Compatible with major brands. Encourages STEM learning and imagination.',
                'netPrice' => '28.92',
                'taxRate' => '21.00',
                'stock' => 47,
                'sku' => 'TOY-BBS-026',
                'type' => Article::TYPE_PHYSICAL,
                'category' => 'toys',
                'isFeatured' => false,
            ],
            [
                'name' => 'Remote Control Drone',
                'slug' => 'rc-drone-camera',
                'shortDescription' => 'Beginner-friendly drone with HD camera',
                'description' => 'Easy to fly with automatic stabilization and 20-minute flight time.',
                'netPrice' => '82.64',
                'taxRate' => '21.00',
                'stock' => 12,
                'sku' => 'TOY-RCD-027',
                'type' => Article::TYPE_PHYSICAL,
                'category' => 'toys',
                'isFeatured' => false,
            ],
            [
                'name' => 'Educational Science Kit',
                'slug' => 'science-kit-kids',
                'shortDescription' => '50 hands-on experiments for ages 8-12',
                'description' => 'Learn chemistry, physics, and biology through fun activities. Safety-tested.',
                'netPrice' => '37.19',
                'taxRate' => '21.00',
                'stock' => 38,
                'sku' => 'TOY-ESK-028',
                'type' => Article::TYPE_PHYSICAL,
                'category' => 'toys',
                'isFeatured' => false,
            ],
            [
                'name' => 'Plush Teddy Bear Large',
                'slug' => 'plush-teddy-bear',
                'shortDescription' => 'Soft cuddly teddy bear 45cm tall',
                'description' => 'Hypoallergenic materials. Machine washable. Perfect gift for any age.',
                'netPrice' => '24.79',
                'taxRate' => '21.00',
                'stock' => 0,
                'sku' => 'TOY-PTB-029',
                'type' => Article::TYPE_PHYSICAL,
                'category' => 'toys',
                'isFeatured' => false,
            ],
            [
                'name' => 'Board Game Collection',
                'slug' => 'board-game-collection',
                'shortDescription' => 'Bundle of 3 family-friendly strategy games',
                'description' => 'Hours of entertainment for game nights. Ages 10+ with 2-6 players.',
                'netPrice' => '57.85',
                'taxRate' => '21.00',
                'stock' => 26,
                'sku' => 'TOY-BGC-030',
                'type' => Article::TYPE_BUNDLE,
                'category' => 'toys',
                'isFeatured' => false,
            ],
        ];

        // Featured count: 5 products
        // Stock distribution: 18 in stock (60%), 9 low stock (30%), 3 out of stock (10%)
        // Type distribution: 21 physical (70%), 6 virtual (20%), 3 bundle (10%)

        foreach ($products as $productData) {
            // Check if article already exists
            $existingArticle = $this->articleRepository->findOneBy(['slug' => $productData['slug']]);
            if ($existingArticle) {
                continue;
            }

            $article = new Article();
            $article->setName($productData['name']);
            $article->setSlug($productData['slug']);
            $article->setShortDescription($productData['shortDescription']);
            $article->setDescription($productData['description']);
            $article->setNetPrice($productData['netPrice']);
            $article->setTaxRate($productData['taxRate']);
            // Gross price is automatically calculated by setNetPrice() and setTaxRate()

            $article->setStock($productData['stock']);
            $article->setSku($productData['sku']);
            $article->setType($productData['type']);
            $article->setIsActive(true);
            $article->setIsFeatured($productData['isFeatured']);

            if (isset($productData['ignoreStock']) && $productData['ignoreStock']) {
                $article->setIgnoreStock(true);
            }

            // Set category from reference
            $categoryReference = 'category_' . $productData['category'];
            try {
                $article->setCategory($this->getReference($categoryReference, \App\Entity\Category::class));
            } catch (\Exception $e) {
                // Category reference not found, skip
            }

            // Set language
            if ($defaultLanguage) {
                $article->setLanguage($defaultLanguage);
            }

            $manager->persist($article);
        }

        $manager->flush();
    }
}
