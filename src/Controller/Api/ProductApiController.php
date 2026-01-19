<?php

namespace App\Controller\Api;

use App\Entity\Article;
use App\Entity\Category;
use App\Repository\ArticleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/products', priority: 10)]
class ProductApiController extends AbstractController
{
    #[Route('', name: 'api_products_list', methods: ['GET'])]
    public function list(Request $request, ArticleRepository $articleRepository): JsonResponse
    {
        $page = max(1, (int) $request->query->get('page', 1));
        $limit = max(1, min(50, (int) $request->query->get('limit', 12)));
        $offset = ($page - 1) * $limit;

        // Extract filter parameters
        $priceMin = $request->query->get('price_min');
        $priceMax = $request->query->get('price_max');
        $categories = $request->query->all('categories') ?? [];
        $type = $request->query->get('type');
        $inStock = $request->query->get('in_stock') === 'true' || $request->query->get('in_stock') === '1';
        $sort = $request->query->get('sort', 'newest');

        // Build base query for count
        $countQb = $articleRepository->createQueryBuilder('a')
            ->select('COUNT(a.id)')
            ->where('a.isActive = :active')
            ->setParameter('active', true);

        // Apply filters to count query
        $this->applyFilters($countQb, $priceMin, $priceMax, $categories, $type, $inStock);

        $totalCount = $countQb->getQuery()->getSingleScalarResult();

        // Build query for products with same filters
        $qb = $articleRepository->createQueryBuilder('a')
            ->where('a.isActive = :active')
            ->setParameter('active', true);

        // Apply filters to data query
        $this->applyFilters($qb, $priceMin, $priceMax, $categories, $type, $inStock);

        // Apply sorting
        switch ($sort) {
            case 'price_asc':
                $qb->orderBy('a.grossPrice', 'ASC');
                break;
            case 'price_desc':
                $qb->orderBy('a.grossPrice', 'DESC');
                break;
            case 'name_asc':
                $qb->orderBy('a.name', 'ASC');
                break;
            case 'newest':
            default:
                $qb->orderBy('a.createdAt', 'DESC');
                break;
        }

        // Apply pagination
        $qb->setFirstResult($offset)->setMaxResults($limit);

        $products = $qb->getQuery()->getResult();

        // phpcs:ignore Security.BadFunctions.CallbackFunctions -- Safe inline closure
        $productsData = array_map(function($product) use ($request) {
            return $this->formatProduct($product, $request->getLocale());
        }, $products);

        return $this->json([
            'success' => true,
            'data' => $productsData,
            'pagination' => [
                'page' => $page,
                'limit' => $limit,
                'total' => (int) $totalCount,
                'totalPages' => (int) ceil($totalCount / $limit),
                'hasMore' => $page < ceil($totalCount / $limit)
            ]
        ]);
    }

    private function applyFilters($qb, $priceMin, $priceMax, $categories, $type, $inStock): void
    {
        // Price range filter
        if ($priceMin !== null && $priceMin !== '') {
            $qb->andWhere('a.grossPrice >= :priceMin')
               ->setParameter('priceMin', (float) $priceMin);
        }

        if ($priceMax !== null && $priceMax !== '') {
            $qb->andWhere('a.grossPrice <= :priceMax')
               ->setParameter('priceMax', (float) $priceMax);
        }

        // Categories filter (array)
        if (!empty($categories)) {
            $qb->andWhere('a.category IN (:categories)')
               ->setParameter('categories', $categories);
        }

        // Type filter
        if ($type !== null && $type !== '') {
            $qb->andWhere('a.type = :type')
               ->setParameter('type', $type);
        }

        // Stock availability filter
        if ($inStock) {
            $qb->andWhere('(a.stock > 0 OR a.ignoreStock = true)');
        }
    }

    #[Route('/category/{slug}', name: 'api_products_by_category', methods: ['GET'])]
    public function byCategory(
        string $slug,
        Request $request,
        ArticleRepository $articleRepository,
        EntityManagerInterface $em
    ): JsonResponse {
        $category = $em->getRepository(\App\Entity\Category::class)->findOneBy([
            'slug' => $slug,
            'isActive' => true
        ]);

        if (!$category) {
            return $this->json(['success' => false, 'error' => 'Category not found'], 404);
        }

        $page = max(1, (int) $request->query->get('page', 1));
        $limit = max(1, min(50, (int) $request->query->get('limit', 12)));
        $offset = ($page - 1) * $limit;

        // Get total count for category
        $totalCount = $articleRepository->createQueryBuilder('a')
            ->select('COUNT(a.id)')
            ->where('a.category = :category')
            ->andWhere('a.isActive = :active')
            ->setParameter('category', $category)
            ->setParameter('active', true)
            ->getQuery()
            ->getSingleScalarResult();

        // Get products for current page
        $products = $articleRepository->createQueryBuilder('a')
            ->where('a.category = :category')
            ->andWhere('a.isActive = :active')
            ->setParameter('category', $category)
            ->setParameter('active', true)
            ->orderBy('a.createdAt', 'DESC')
            ->setFirstResult($offset)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();

        // phpcs:ignore Security.BadFunctions.CallbackFunctions -- Safe inline closure
        $productsData = array_map(function($product) use ($request) {
            return $this->formatProduct($product, $request->getLocale());
        }, $products);

        return $this->json([
            'success' => true,
            'data' => $productsData,
            'pagination' => [
                'page' => $page,
                'limit' => $limit,
                'total' => (int) $totalCount,
                'totalPages' => (int) ceil($totalCount / $limit),
                'hasMore' => $page < ceil($totalCount / $limit)
            ]
        ]);
    }

    #[Route('/categories', name: 'api_products_categories', methods: ['GET'])]
    public function categories(EntityManagerInterface $em): JsonResponse
    {
        // Get all active categories with product counts
        $categories = $em->getRepository(Category::class)
            ->createQueryBuilder('c')
            ->where('c.isActive = :active')
            ->setParameter('active', true)
            ->orderBy('c.name', 'ASC')
            ->getQuery()
            ->getResult();

        $categoriesData = [];
        foreach ($categories as $category) {
            $productCount = $em->getRepository(Article::class)
                ->createQueryBuilder('a')
                ->select('COUNT(a.id)')
                ->where('a.category = :category')
                ->andWhere('a.isActive = :active')
                ->setParameter('category', $category)
                ->setParameter('active', true)
                ->getQuery()
                ->getSingleScalarResult();

            $categoriesData[] = [
                'id' => $category->getId(),
                'name' => $category->getName(),
                'slug' => $category->getSlug(),
                'productCount' => (int) $productCount
            ];
        }

        return $this->json([
            'success' => true,
            'data' => $categoriesData
        ]);
    }

    #[Route('/types', name: 'api_products_types', methods: ['GET'])]
    public function types(): JsonResponse
    {
        // Product types from Article entity constants
        $types = [
            ['value' => Article::TYPE_PHYSICAL, 'label' => 'Physical Product'],
            ['value' => Article::TYPE_VIRTUAL, 'label' => 'Digital Product'],
            ['value' => Article::TYPE_BUNDLE, 'label' => 'Bundle'],
            ['value' => Article::TYPE_ROOM, 'label' => 'Room Booking'],
            ['value' => Article::TYPE_TIMESLOT, 'label' => 'Time Slot'],
            ['value' => Article::TYPE_TICKET, 'label' => 'Ticket']
        ];

        return $this->json([
            'success' => true,
            'data' => $types
        ]);
    }

    private function formatProduct($product, string $locale): array
    {
        return [
            'id' => $product->getId(),
            'name' => $product->getName(),
            'slug' => $product->getSlug(),
            'shortDescription' => $product->getShortDescription(),
            'grossPrice' => $product->getGrossPrice(),
            'taxRate' => $product->getTaxRate() ?? 19,
            'image' => $product->getMainImage()
                ? '/uploads/media/' . $product->getMainImage()->getFilename()
                : null,
            'imageAlt' => $product->getMainImage()
                ? ($product->getMainImage()->getAlt() ?? $product->getName())
                : $product->getName(),
            'stock' => $product->getStock(),
            'ignoreStock' => $product->getIgnoreStock(),
            'inStock' => $product->getIgnoreStock() || $product->getStock() > 0,
            'detailUrl' => $this->generateUrl('app_product_detail', ['slug' => $product->getSlug()]),
        ];
    }
}
