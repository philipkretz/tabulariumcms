<?php

namespace App\Controller\Api;

use App\Entity\Article;
use App\Entity\Category;
use App\Entity\ProductStock;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route("/api/articles")]
class ArticleApiController extends BaseApiController
{
    #[Route("", methods: ["GET"])]
    public function index(Request $request): Response
    {
        try {
            $this->checkPermission($request, "Article", "read");

            $qb = $this->em->getRepository(Article::class)->createQueryBuilder("e");

            if ($search = $request->query->get("search")) {
                $qb->andWhere("e.title LIKE :search OR e.sku LIKE :search")
                   ->setParameter("search", "%{$search}%");
            }

            if ($categoryId = $request->query->get("category_id")) {
                $qb->andWhere("e.category = :category")->setParameter("category", $categoryId);
            }

            $result = $this->paginate($request, $qb);

            return $this->jsonResponse($result);
        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    #[Route("/public", methods: ["GET"], name: "api_articles_public")]
    public function publicIndex(Request $request): Response
    {
        try {
            $qb = $this->em->getRepository(Article::class)->createQueryBuilder("a");

            // Join related entities to avoid N+1 queries
            $qb->leftJoin("a.mainImage", "mainImage")
               ->leftJoin("a.category", "category")
               ->addSelect("mainImage", "category");

            // Only active articles
            $qb->andWhere("a.isActive = :active")
               ->setParameter("active", true);

            // Search filter (name, SKU, description)
            if ($search = $request->query->get("search")) {
                $qb->andWhere("a.name LIKE :search OR a.sku LIKE :search OR a.shortDescription LIKE :search")
                   ->setParameter("search", "%{$search}%");
            }

            // Category filter
            if ($categoryId = $request->query->get("category")) {
                $qb->andWhere("a.category = :category")
                   ->setParameter("category", $categoryId);
            }

            // Type filter
            if ($type = $request->query->get("type")) {
                $qb->andWhere("a.type = :type")
                   ->setParameter("type", $type);
            }

            // Price range filters
            if ($priceMin = $request->query->get("price_min")) {
                $qb->andWhere("a.grossPrice >= :priceMin")
                   ->setParameter("priceMin", $priceMin);
            }

            if ($priceMax = $request->query->get("price_max")) {
                $qb->andWhere("a.grossPrice <= :priceMax")
                   ->setParameter("priceMax", $priceMax);
            }

            // In stock filter
            if ($request->query->get("in_stock") === "true" || $request->query->get("in_stock") === "1") {
                $qb->andWhere("a.stock > 0 OR a.ignoreStock = true");
            }

            // Sorting
            $sort = $request->query->get("sort", "newest");
            switch ($sort) {
                case "price_asc":
                    $qb->orderBy("a.grossPrice", "ASC");
                    break;
                case "price_desc":
                    $qb->orderBy("a.grossPrice", "DESC");
                    break;
                case "name_asc":
                    $qb->orderBy("a.name", "ASC");
                    break;
                case "featured":
                    $qb->orderBy("a.isFeatured", "DESC")
                       ->addOrderBy("a.createdAt", "DESC");
                    break;
                case "newest":
                default:
                    $qb->orderBy("a.createdAt", "DESC");
                    break;
            }

            // Pagination
            $page = max(1, (int) $request->query->get("page", 1));
            $limit = min(100, max(1, (int) $request->query->get("limit", 12)));
            $offset = ($page - 1) * $limit;

            $qb->setFirstResult($offset)->setMaxResults($limit);

            $query = $qb->getQuery();
            $articles = $query->getResult();

            // Get total count
            $countQb = $this->em->getRepository(Article::class)->createQueryBuilder("a");
            $countQb->select("COUNT(a.id)")
                    ->andWhere("a.isActive = :active")
                    ->setParameter("active", true);

            // Apply same filters to count query
            if ($search = $request->query->get("search")) {
                $countQb->andWhere("a.name LIKE :search OR a.sku LIKE :search OR a.shortDescription LIKE :search")
                        ->setParameter("search", "%{$search}%");
            }

            if ($categoryId = $request->query->get("category")) {
                $countQb->andWhere("a.category = :category")
                        ->setParameter("category", $categoryId);
            }

            if ($type = $request->query->get("type")) {
                $countQb->andWhere("a.type = :type")
                        ->setParameter("type", $type);
            }

            if ($priceMin = $request->query->get("price_min")) {
                $countQb->andWhere("a.grossPrice >= :priceMin")
                        ->setParameter("priceMin", $priceMin);
            }

            if ($priceMax = $request->query->get("price_max")) {
                $countQb->andWhere("a.grossPrice <= :priceMax")
                        ->setParameter("priceMax", $priceMax);
            }

            if ($request->query->get("in_stock") === "true" || $request->query->get("in_stock") === "1") {
                $countQb->andWhere("a.stock > 0 OR a.ignoreStock = true");
            }

            $total = (int) $countQb->getQuery()->getSingleScalarResult();

            // Format response
            $data = [];
            foreach ($articles as $article) {
                // Determine the effective request email
                $effectiveRequestEmail = $article->getRequestEmail();
                if (!$effectiveRequestEmail && $article->getSeller()) {
                    $effectiveRequestEmail = $article->getSeller()->getEmail();
                }
                if (!$effectiveRequestEmail) {
                    $effectiveRequestEmail = "info@example.com"; // Fallback - should be from site config
                }

                $articleData = [
                    "id" => $article->getId(),
                    "name" => $article->getName(),
                    "slug" => $article->getSlug(),
                    "shortDescription" => $article->getShortDescription(),
                    "grossPrice" => $article->getGrossPrice(),
                    "stock" => $article->getStock(),
                    "isFeatured" => $article->isFeatured(),
                    "type" => $article->getType(),
                    "isRequestOnly" => $article->isRequestOnly(),
                    "requestEmail" => $article->isRequestOnly() ? $effectiveRequestEmail : null,
                    "mainImage" => null,
                    "category" => null,
                ];

                if ($article->getMainImage()) {
                    $articleData["mainImage"] = [
                        "url" => "/uploads/media/" . $article->getMainImage()->getFilename(),
                        "alt" => $article->getMainImage()->getAlt() ?? $article->getName(),
                    ];
                }

                if ($article->getCategory()) {
                    $articleData["category"] = [
                        "id" => $article->getCategory()->getId(),
                        "name" => $article->getCategory()->getName(),
                        "slug" => $article->getCategory()->getSlug(),
                    ];
                }

                $data[] = $articleData;
            }

            return $this->json([
                "data" => $data,
                "pagination" => [
                    "page" => $page,
                    "limit" => $limit,
                    "total" => $total,
                    "pages" => (int) ceil($total / $limit)
                ]
            ]);
        } catch (\Exception $e) {
            return $this->json([
                "error" => $e->getMessage()
            ], 500);
        }
    }

    #[Route("/public/categories", methods: ["GET"], name: "api_articles_public_categories")]
    public function publicCategories(Request $request): Response
    {
        try {
            $categories = $this->em->getRepository(Category::class)
                ->createQueryBuilder("c")
                ->where("c.isActive = :active")
                ->setParameter("active", true)
                ->orderBy("c.name", "ASC")
                ->getQuery()
                ->getResult();

            $data = [];
            foreach ($categories as $category) {
                $data[] = [
                    "id" => $category->getId(),
                    "name" => $category->getName(),
                    "slug" => $category->getSlug(),
                ];
            }

            return $this->json($data);
        } catch (\Exception $e) {
            return $this->json([
                "error" => $e->getMessage()
            ], 500);
        }
    }

    #[Route("/public/types", methods: ["GET"], name: "api_articles_public_types")]
    public function publicTypes(Request $request): Response
    {
        try {
            $types = [
                ["value" => Article::TYPE_PHYSICAL, "label" => "Physical Product"],
                ["value" => Article::TYPE_VIRTUAL, "label" => "Digital Product"],
                ["value" => Article::TYPE_BUNDLE, "label" => "Bundle"],
                ["value" => Article::TYPE_ROOM, "label" => "Room Booking"],
                ["value" => Article::TYPE_TIMESLOT, "label" => "Time Slot"],
                ["value" => Article::TYPE_TICKET, "label" => "Ticket"],
            ];

            return $this->json($types);
        } catch (\Exception $e) {
            return $this->json([
                "error" => $e->getMessage()
            ], 500);
        }
    }

    #[Route("/{id}", methods: ["GET"])]
    public function show(Request $request, int $id): Response
    {
        try {
            $this->checkPermission($request, "Article", "read");

            $article = $this->em->getRepository(Article::class)->find($id);

            if (!($article)) {
                return new Response("Article not found", 404);
            }

            return $this->jsonResponse($article);
        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    #[Route("", methods: ["POST"])]
    public function create(Request $request): Response
    {
        try {
            $this->checkPermission($request, "Article", "create");

            $data = json_decode($request->getContent(), true);

            $article = new Article();
            $article->setTitle($data["title"] ?? "");
            $article->setSku($data["sku"] ?? "");
            $article->setPrice($data["price"] ?? "0");
            $article->setContent($data["content"] ?? "");
            $article->setIsActive($data["isActive"] ?? true);

            $errors = $this->validateEntity($article);
            if (!empty($errors)) {
                return $this->jsonResponse(["errors" => $errors], 400);
            }

            $this->em->persist($article);
            $this->em->flush();

            return $this->jsonResponse($article, 201);
        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    #[Route("/{id}", methods: ["PUT", "PATCH"])]
    public function update(Request $request, int $id): Response
    {
        try {
            $this->checkPermission($request, "Article", "update");

            $article = $this->em->getRepository(Article::class)->find($id);

            if (!($article)) {
                return new Response("Article not found", 404);
            }

            $data = json_decode($request->getContent(), true);

            if (isset($data["title"])) $article->setTitle($data["title"]);
            if (isset($data["price"])) $article->setPrice($data["price"]);
            if (isset($data["content"])) $article->setContent($data["content"]);
            if (isset($data["isActive"])) $article->setIsActive($data["isActive"]);

            $this->em->flush();

            return $this->jsonResponse($article);
        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    #[Route("/{id}", methods: ["DELETE"])]
    public function delete(Request $request, int $id): Response
    {
        try {
            $this->checkPermission($request, "Article", "delete");

            $article = $this->em->getRepository(Article::class)->find($id);

            if (!($article)) {
                return new Response("Article not found", 404);
            }

            $this->em->remove($article);
            $this->em->flush();

            return $this->jsonResponse(["message" => "Article deleted"]);
        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    #[Route("/{id}/stock", methods: ["GET"])]
    public function getStock(Request $request, int $id): Response
    {
        try {
            $this->checkPermission($request, "Article", "read");

            $article = $this->em->getRepository(Article::class)->find($id);

            if (!($article)) {
                return new Response("Article not found", 404);
            }

            $stocks = $this->em->getRepository(ProductStock::class)->findBy(["article" => $article]);

            return $this->jsonResponse($stocks);
        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }
}
