<?php

namespace App\Service;

use App\Entity\Page;
use App\Entity\Post;
use App\Entity\Article;
use App\Entity\User;
use App\Entity\Order;
use App\Entity\Category;
use App\Entity\Newsletter;
use App\Entity\EmailTemplate;
use App\Repository\PageRepository;
use App\Repository\PostRepository;
use App\Repository\ArticleRepository;
use App\Repository\UserRepository;
use App\Repository\OrderRepository;
use App\Repository\CategoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

/**
 * Service that provides available tools/actions for AI agents to manipulate the platform
 */
class AgentToolsService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private PageRepository $pageRepository,
        private PostRepository $postRepository,
        private ArticleRepository $articleRepository,
        private UserRepository $userRepository,
        private OrderRepository $orderRepository,
        private CategoryRepository $categoryRepository,
        private MailerInterface $mailer
    ) {
    }

    /**
     * Get list of all available tools with their definitions
     */
    public function getAvailableTools(): array
    {
        return [
            'content_create_page' => [
                'name' => 'Create Page',
                'description' => 'Create a new CMS page',
                'category' => 'Content Management',
                'parameters' => [
                    'title' => ['type' => 'string', 'required' => true],
                    'content' => ['type' => 'string', 'required' => true],
                    'slug' => ['type' => 'string', 'required' => false],
                    'isPublished' => ['type' => 'boolean', 'required' => false]
                ]
            ],
            'content_update_page' => [
                'name' => 'Update Page',
                'description' => 'Update an existing page',
                'category' => 'Content Management',
                'parameters' => [
                    'id' => ['type' => 'integer', 'required' => true],
                    'title' => ['type' => 'string', 'required' => false],
                    'content' => ['type' => 'string', 'required' => false],
                    'isPublished' => ['type' => 'boolean', 'required' => false]
                ]
            ],
            'content_delete_page' => [
                'name' => 'Delete Page',
                'description' => 'Delete a page',
                'category' => 'Content Management',
                'parameters' => [
                    'id' => ['type' => 'integer', 'required' => true]
                ]
            ],
            'content_list_pages' => [
                'name' => 'List Pages',
                'description' => 'Get list of pages with optional filters',
                'category' => 'Content Management',
                'parameters' => [
                    'limit' => ['type' => 'integer', 'required' => false],
                    'published' => ['type' => 'boolean', 'required' => false]
                ]
            ],
            'content_create_post' => [
                'name' => 'Create Post',
                'description' => 'Create a new blog post',
                'category' => 'Content Management',
                'parameters' => [
                    'title' => ['type' => 'string', 'required' => true],
                    'content' => ['type' => 'string', 'required' => true],
                    'categoryId' => ['type' => 'integer', 'required' => false]
                ]
            ],
            'content_update_post' => [
                'name' => 'Update Post',
                'description' => 'Update an existing blog post',
                'category' => 'Content Management',
                'parameters' => [
                    'id' => ['type' => 'integer', 'required' => true],
                    'title' => ['type' => 'string', 'required' => false],
                    'content' => ['type' => 'string', 'required' => false]
                ]
            ],
            'ecommerce_create_article' => [
                'name' => 'Create Product',
                'description' => 'Create a new product/article',
                'category' => 'E-Commerce',
                'parameters' => [
                    'name' => ['type' => 'string', 'required' => true],
                    'description' => ['type' => 'string', 'required' => true],
                    'price' => ['type' => 'number', 'required' => true],
                    'stock' => ['type' => 'integer', 'required' => false]
                ]
            ],
            'ecommerce_update_article' => [
                'name' => 'Update Product',
                'description' => 'Update product details',
                'category' => 'E-Commerce',
                'parameters' => [
                    'id' => ['type' => 'integer', 'required' => true],
                    'name' => ['type' => 'string', 'required' => false],
                    'price' => ['type' => 'number', 'required' => false],
                    'stock' => ['type' => 'integer', 'required' => false]
                ]
            ],
            'ecommerce_list_orders' => [
                'name' => 'List Orders',
                'description' => 'Get list of orders with optional filters',
                'category' => 'E-Commerce',
                'parameters' => [
                    'status' => ['type' => 'string', 'required' => false],
                    'limit' => ['type' => 'integer', 'required' => false]
                ]
            ],
            'ecommerce_update_order_status' => [
                'name' => 'Update Order Status',
                'description' => 'Change order status',
                'category' => 'E-Commerce',
                'parameters' => [
                    'id' => ['type' => 'integer', 'required' => true],
                    'status' => ['type' => 'string', 'required' => true]
                ]
            ],
            'communication_send_email' => [
                'name' => 'Send Email',
                'description' => 'Send an email to specified recipients',
                'category' => 'Communication',
                'parameters' => [
                    'to' => ['type' => 'string', 'required' => true],
                    'subject' => ['type' => 'string', 'required' => true],
                    'body' => ['type' => 'string', 'required' => true],
                    'from' => ['type' => 'string', 'required' => false]
                ]
            ],
            'data_query_database' => [
                'name' => 'Query Database',
                'description' => 'Execute a safe read-only database query',
                'category' => 'Data Analysis',
                'parameters' => [
                    'entity' => ['type' => 'string', 'required' => true],
                    'filters' => ['type' => 'object', 'required' => false],
                    'limit' => ['type' => 'integer', 'required' => false]
                ]
            ],
            'data_aggregate_stats' => [
                'name' => 'Get Statistics',
                'description' => 'Get aggregated statistics from database',
                'category' => 'Data Analysis',
                'parameters' => [
                    'entity' => ['type' => 'string', 'required' => true],
                    'metric' => ['type' => 'string', 'required' => true]
                ]
            ],
            'user_list_users' => [
                'name' => 'List Users',
                'description' => 'Get list of users',
                'category' => 'User Management',
                'parameters' => [
                    'role' => ['type' => 'string', 'required' => false],
                    'limit' => ['type' => 'integer', 'required' => false]
                ]
            ],
            'user_update_user' => [
                'name' => 'Update User',
                'description' => 'Update user details',
                'category' => 'User Management',
                'parameters' => [
                    'id' => ['type' => 'integer', 'required' => true],
                    'email' => ['type' => 'string', 'required' => false],
                    'roles' => ['type' => 'array', 'required' => false]
                ]
            ]
        ];
    }

    /**
     * Execute a tool action
     */
    public function executeTool(string $toolName, array $parameters): array
    {
        try {
            return match($toolName) {
                'content_create_page' => $this->createPage($parameters),
                'content_update_page' => $this->updatePage($parameters),
                'content_delete_page' => $this->deletePage($parameters),
                'content_list_pages' => $this->listPages($parameters),
                'content_create_post' => $this->createPost($parameters),
                'content_update_post' => $this->updatePost($parameters),
                'ecommerce_create_article' => $this->createArticle($parameters),
                'ecommerce_update_article' => $this->updateArticle($parameters),
                'ecommerce_list_orders' => $this->listOrders($parameters),
                'ecommerce_update_order_status' => $this->updateOrderStatus($parameters),
                'communication_send_email' => $this->sendEmail($parameters),
                'data_query_database' => $this->queryDatabase($parameters),
                'data_aggregate_stats' => $this->aggregateStats($parameters),
                'user_list_users' => $this->listUsers($parameters),
                'user_update_user' => $this->updateUser($parameters),
                default => ['success' => false, 'error' => 'Unknown tool: ' . $toolName]
            };
        } catch (\Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    // Content Management Tools

    private function createPage(array $params): array
    {
        $page = new Page();
        $page->setTitle($params['title']);
        $page->setContent($params['content']);
        $page->setSlug($params['slug'] ?? $this->generateSlug($params['title']));
        $page->setIsPublished($params['isPublished'] ?? false);
        $page->setCreatedAt(new \DateTimeImmutable());

        $this->entityManager->persist($page);
        $this->entityManager->flush();

        return ['success' => true, 'id' => $page->getId(), 'message' => 'Page created successfully'];
    }

    private function updatePage(array $params): array
    {
        $page = $this->pageRepository->find($params['id']);
        if (!$page) {
            return ['success' => false, 'error' => 'Page not found'];
        }

        if (isset($params['title'])) $page->setTitle($params['title']);
        if (isset($params['content'])) $page->setContent($params['content']);
        if (isset($params['isPublished'])) $page->setIsPublished($params['isPublished']);
        $page->setUpdatedAt(new \DateTimeImmutable());

        $this->entityManager->flush();

        return ['success' => true, 'message' => 'Page updated successfully'];
    }

    private function deletePage(array $params): array
    {
        $page = $this->pageRepository->find($params['id']);
        if (!$page) {
            return ['success' => false, 'error' => 'Page not found'];
        }

        $this->entityManager->remove($page);
        $this->entityManager->flush();

        return ['success' => true, 'message' => 'Page deleted successfully'];
    }

    private function listPages(array $params): array
    {
        $criteria = [];
        if (isset($params['published'])) {
            $criteria['isPublished'] = $params['published'];
        }

        $pages = $this->pageRepository->findBy($criteria, ['createdAt' => 'DESC'], $params['limit'] ?? 50);

        // phpcs:ignore Security.BadFunctions.CallbackFunctions -- Safe inline closure for data transformation
        $result = array_map(fn($page) => [
            'id' => $page->getId(),
            'title' => $page->getTitle(),
            'slug' => $page->getSlug(),
            'isPublished' => $page->isPublished(),
            'createdAt' => $page->getCreatedAt()?->format('Y-m-d H:i:s')
        ], $pages);

        return ['success' => true, 'data' => $result, 'count' => count($result)];
    }

    private function createPost(array $params): array
    {
        $post = new Post();
        $post->setTitle($params['title']);
        $post->setContent($params['content']);
        $post->setSlug($this->generateSlug($params['title']));
        $post->setPublishedAt(new \DateTimeImmutable());

        if (isset($params['categoryId'])) {
            $category = $this->categoryRepository->find($params['categoryId']);
            if ($category) {
                $post->setCategory($category);
            }
        }

        $this->entityManager->persist($post);
        $this->entityManager->flush();

        return ['success' => true, 'id' => $post->getId(), 'message' => 'Post created successfully'];
    }

    private function updatePost(array $params): array
    {
        $post = $this->postRepository->find($params['id']);
        if (!$post) {
            return ['success' => false, 'error' => 'Post not found'];
        }

        if (isset($params['title'])) $post->setTitle($params['title']);
        if (isset($params['content'])) $post->setContent($params['content']);

        $this->entityManager->flush();

        return ['success' => true, 'message' => 'Post updated successfully'];
    }

    // E-Commerce Tools

    private function createArticle(array $params): array
    {
        $article = new Article();
        $article->setName($params['name']);
        $article->setDescription($params['description']);
        $article->setPrice($params['price']);
        $article->setStock($params['stock'] ?? 0);
        $article->setCreatedAt(new \DateTimeImmutable());

        $this->entityManager->persist($article);
        $this->entityManager->flush();

        return ['success' => true, 'id' => $article->getId(), 'message' => 'Product created successfully'];
    }

    private function updateArticle(array $params): array
    {
        $article = $this->articleRepository->find($params['id']);
        if (!$article) {
            return ['success' => false, 'error' => 'Product not found'];
        }

        if (isset($params['name'])) $article->setName($params['name']);
        if (isset($params['price'])) $article->setPrice($params['price']);
        if (isset($params['stock'])) $article->setStock($params['stock']);

        $this->entityManager->flush();

        return ['success' => true, 'message' => 'Product updated successfully'];
    }

    private function listOrders(array $params): array
    {
        $criteria = [];
        if (isset($params['status'])) {
            $criteria['status'] = $params['status'];
        }

        $orders = $this->orderRepository->findBy($criteria, ['createdAt' => 'DESC'], $params['limit'] ?? 50);

        // phpcs:ignore Security.BadFunctions.CallbackFunctions -- Safe inline closure for data transformation
        $result = array_map(fn($order) => [
            'id' => $order->getId(),
            'orderNumber' => $order->getOrderNumber(),
            'status' => $order->getStatus(),
            'total' => $order->getTotal(),
            'createdAt' => $order->getCreatedAt()?->format('Y-m-d H:i:s')
        ], $orders);

        return ['success' => true, 'data' => $result, 'count' => count($result)];
    }

    private function updateOrderStatus(array $params): array
    {
        $order = $this->orderRepository->find($params['id']);
        if (!$order) {
            return ['success' => false, 'error' => 'Order not found'];
        }

        $order->setStatus($params['status']);
        $this->entityManager->flush();

        return ['success' => true, 'message' => 'Order status updated successfully'];
    }

    // Communication Tools

    private function sendEmail(array $params): array
    {
        $email = (new Email())
            ->from($params['from'] ?? 'noreply@tabulariumcms.com')
            ->to($params['to'])
            ->subject($params['subject'])
            ->html($params['body']);

        $this->mailer->send($email);

        return ['success' => true, 'message' => 'Email sent successfully'];
    }

    // Data Analysis Tools

    private function queryDatabase(array $params): array
    {
        $entityClass = 'App\\Entity\\' . $params['entity'];
        if (!class_exists($entityClass)) {
            return ['success' => false, 'error' => 'Invalid entity'];
        }

        $repository = $this->entityManager->getRepository($entityClass);
        $criteria = $params['filters'] ?? [];
        $results = $repository->findBy($criteria, [], $params['limit'] ?? 50);

        return ['success' => true, 'data' => $results, 'count' => count($results)];
    }

    private function aggregateStats(array $params): array
    {
        $entityClass = 'App\\Entity\\' . $params['entity'];
        if (!class_exists($entityClass)) {
            return ['success' => false, 'error' => 'Invalid entity'];
        }

        $repository = $this->entityManager->getRepository($entityClass);

        $stat = match($params['metric']) {
            'count' => $repository->count([]),
            default => 0
        };

        return ['success' => true, 'metric' => $params['metric'], 'value' => $stat];
    }

    // User Management Tools

    private function listUsers(array $params): array
    {
        $users = $this->userRepository->findBy([], ['id' => 'DESC'], $params['limit'] ?? 50);

        // phpcs:ignore Security.BadFunctions.CallbackFunctions -- Safe inline closure for data transformation
        $result = array_map(fn($user) => [
            'id' => $user->getId(),
            'email' => $user->getEmail(),
            'roles' => $user->getRoles()
        ], $users);

        return ['success' => true, 'data' => $result, 'count' => count($result)];
    }

    private function updateUser(array $params): array
    {
        $user = $this->userRepository->find($params['id']);
        if (!$user) {
            return ['success' => false, 'error' => 'User not found'];
        }

        if (isset($params['email'])) $user->setEmail($params['email']);
        if (isset($params['roles'])) $user->setRoles($params['roles']);

        $this->entityManager->flush();

        return ['success' => true, 'message' => 'User updated successfully'];
    }

    // Helper methods

    private function generateSlug(string $text): string
    {
        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $text), '-'));
        return $slug . '-' . substr(uniqid(), -6);
    }
}
