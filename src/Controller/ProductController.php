<?php
namespace App\Controller;

use App\Entity\Article;
use App\Repository\ArticleRepository;
use App\Service\MenuService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ProductController extends AbstractController
{
    public function __construct(
        private MenuService $menuService
    ) {
    }

    #[Route('/products', name: 'app_products')]
    #[Route('/{_locale}/products', name: 'app_products_locale', requirements: ['_locale' => 'en|de|es|fr|ca|it|pt|nl|pl|ru|ja|zh|ar|sv|no|da|fi|cs|sk|hu|ro|el|tr|uk|ko|hi|th|vi'])]
    public function index(ArticleRepository $articleRepository, Request $request, ?string $_locale = null): Response
    {
        $locale = $_locale ?? 'en';
        $request->setLocale($locale);
        // Load initial products for first page (12 items)
        $products = $articleRepository->findBy(
            ['isActive' => true],
            ['createdAt' => 'DESC'],
            12
        );

        // Load header and footer menus
        $headerMenu = $this->menuService->getMenuByIdentifier('main-menu');
        $footerMenu = $this->menuService->getMenuByIdentifier('footer-menu');

        return $this->render('product/list.html.twig', [
            'products' => $products,
            'headerMenu' => $headerMenu,
            'footerMenu' => $footerMenu,
        ]);
    }

    #[Route('/category/{slug}', name: 'app_category')]
    #[Route('/{_locale}/category/{slug}', name: 'app_category_locale', requirements: ['_locale' => 'en|de|es|fr|ca|it|pt|nl|pl|ru|ja|zh|ar|sv|no|da|fi|cs|sk|hu|ro|el|tr|uk|ko|hi|th|vi'])]
    public function category(
        string $slug,
        ArticleRepository $articleRepository,
        EntityManagerInterface $em,
        Request $request,
        ?string $_locale = null
    ): Response {
        $locale = $_locale ?? 'en';
        $request->setLocale($locale);
        $category = $em->getRepository(\App\Entity\Category::class)->findOneBy([
            'slug' => $slug,
            'isActive' => true
        ]);

        if (!$category) {
            throw $this->createNotFoundException('Category not found');
        }

        $products = $articleRepository->createQueryBuilder('a')
            ->where('a.category = :category')
            ->andWhere('a.isActive = :active')
            ->setParameter('category', $category)
            ->setParameter('active', true)
            ->orderBy('a.createdAt', 'DESC')
            ->getQuery()
            ->getResult();

        return $this->render('product/category.html.twig', [
            'category' => $category,
            'products' => $products,
        ]);
    }

    #[Route('/products/{slug}', name: 'app_product_detail')]
    #[Route('/{_locale}/products/{slug}', name: 'app_product_detail_locale', requirements: ['_locale' => 'en|de|es|fr|ca|it|pt|nl|pl|ru|ja|zh|ar|sv|no|da|fi|cs|sk|hu|ro|el|tr|uk|ko|hi|th|vi'])]
    public function detail(
        string $slug,
        ArticleRepository $articleRepository,
        EntityManagerInterface $em,
        Request $request,
        ?string $_locale = null
    ): Response {
        $locale = $_locale ?? 'en';
        $request->setLocale($locale);
        $product = $articleRepository->findOneBy([
            'slug' => $slug,
            'isActive' => true
        ]);

        if (!$product) {
            throw $this->createNotFoundException('Product not found');
        }

        // Get related products from same category
        $relatedProducts = [];
        if ($product->getCategory()) {
            $relatedProducts = $articleRepository->createQueryBuilder('a')
                ->where('a.category = :category')
                ->andWhere('a.isActive = :active')
                ->andWhere('a.id != :currentId')
                ->setParameter('category', $product->getCategory())
                ->setParameter('active', true)
                ->setParameter('currentId', $product->getId())
                ->setMaxResults(4)
                ->getQuery()
                ->getResult();
        }

        return $this->render('product/detail.html.twig', [
            'product' => $product,
            'relatedProducts' => $relatedProducts,
        ]);
    }
}
