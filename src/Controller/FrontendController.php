<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Entity\CookieBanner;
use App\Repository\PageRepository;
use App\Repository\PostRepository;
use App\Repository\CommentRepository;
use App\Repository\LanguageRepository;
use App\Repository\ArticleRepository;
use App\Repository\CustomerReviewRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class FrontendController extends AbstractController
{
    /**
     * Homepage routes - Priority 10 (highest) to prevent conflicts with catch-all routes
     */
    #[Route('/', name: 'app_homepage', priority: 10)]
    #[Route('/{_locale}', name: 'app_homepage_locale', priority: 10, requirements: ['_locale' => 'en|de|es|fr|ca|it|pt|nl|pl|ru|ja|zh|ar|sv|no|da|fi|cs|sk|hu|ro|el|tr|uk|ko|hi|th|vi'])]
    public function homepage(
        EntityManagerInterface $em,
        ArticleRepository $articleRepository,
        PageRepository $pageRepository,
        LanguageRepository $languageRepository,
        CustomerReviewRepository $customerReviewRepository,
        Request $request,
        ?string $_locale = null
    ): Response {
        // CRITICAL: Ensure locale is always a string to prevent array conversion errors
        $locale = $_locale ?? 'en';
        if (is_array($locale)) {
            $locale = 'en';
        }
        $request->setLocale($locale);

        // Check if there's a page marked as homepage
        $criteria = ['isHomePage' => true, 'isPublished' => true];

        // Get language from URL or default to English
        if ($locale && $locale !== 'en') {
            $language = $languageRepository->findOneBy(['code' => $locale, 'isActive' => true]);
            if ($language) {
                $criteria['language'] = $language;
            }
        } else {
            // Use default language (English)
            $defaultLanguage = $languageRepository->findOneBy(['isDefault' => true]);
            if ($defaultLanguage) {
                $criteria['language'] = $defaultLanguage;
            }
        }

        $homePage = $pageRepository->findOneBy($criteria);

        // Log homepage visit
        $em->persist(new \App\Entity\PageVisit($request->getUri()));
        $em->flush();

        // If a homepage is defined, render it
        if ($homePage) {
            // Determine which template to use based on page configuration
            $templateName = $homePage->getTemplate() ?? 'default';
            $templatePath = match($templateName) {
                'vue' => 'frontend/vue.html.twig',
                'default' => 'frontend/page.html.twig',
                default => "frontend/{$templateName}.html.twig"
            };

            return $this->render($templatePath, [
                'page' => $homePage,
            ]);
        }

        // Otherwise, render default homepage with products
        // Get active cookie banner
        $cookieBanner = $em->getRepository(CookieBanner::class)
            ->findOneBy(['isActive' => true], ['createdAt' => 'DESC']);

        // Get featured products
        $featuredProducts = $articleRepository->findBy(
            ['isActive' => true, 'isFeatured' => true],
            ['createdAt' => 'DESC'],
            6
        );

        // Get latest products
        $latestProducts = $articleRepository->findBy(
            ['isActive' => true],
            ['createdAt' => 'DESC'],
            12
        );

        // Get featured customer reviews (active, featured, sorted by sort order)
        $customerReviews = $customerReviewRepository->findBy(
            ['isActive' => true, 'isFeatured' => true],
            ['sortOrder' => 'DESC', 'createdAt' => 'DESC'],
            6
        );

        return $this->render('frontend/homepage.html.twig', [
            'cookie_banner' => $cookieBanner,
            'featured_products' => $featuredProducts,
            'latest_products' => $latestProducts,
            'customer_reviews' => $customerReviews,
        ]);
    }

    #[Route('/page/{slug}', name: 'app_page', priority: 5)]
    #[Route('/{_locale}/page/{slug}', name: 'app_page_locale', priority: 5, requirements: ['_locale' => 'en|de|es|fr|ca|it|pt|nl|pl|ru|ja|zh|ar|sv|no|da|fi|cs|sk|hu|ro|el|tr|uk|ko|hi|th|vi'])]
    public function page(
        string $slug,
        PageRepository $pageRepository,
        LanguageRepository $languageRepository,
        EntityManagerInterface $em,
        Request $request,
        ?string $_locale = null
    ): Response {
        // CRITICAL: Ensure locale is always a string to prevent array conversion errors
        $locale = $_locale ?? 'en';
        if (is_array($locale)) {
            $locale = 'en';
        }
        $request->setLocale($locale);

        $criteria = ['slug' => $slug, 'isPublished' => true];

        // Get language from URL or default to English
        if ($locale && $locale !== 'en') {
            $language = $languageRepository->findOneBy(['code' => $locale, 'isActive' => true]);
            if ($language) {
                $criteria['language'] = $language;
            }
        } else {
            // Use default language (English)
            $defaultLanguage = $languageRepository->findOneBy(['isDefault' => true]);
            if ($defaultLanguage) {
                $criteria['language'] = $defaultLanguage;
            }
        }

        $page = $pageRepository->findOneBy($criteria);

        if (!$page) {
            throw $this->createNotFoundException('Page not found');
        }

        // Log page visit
        $em->persist(new \App\Entity\PageVisit($request->getUri()));
        $em->flush();

        return $this->render('frontend/page.html.twig', [
            'page' => $page,
        ]);
    }

    #[Route('/blog/{slug}', name: 'app_post', priority: 5)]
    #[Route('/{_locale}/blog/{slug}', name: 'app_post_locale', priority: 5, requirements: ['_locale' => 'en|de|es|fr|ca|it|pt|nl|pl|ru|ja|zh|ar|sv|no|da|fi|cs|sk|hu|ro|el|tr|uk|ko|hi|th|vi'])]
    public function post(
        string $slug,
        PostRepository $postRepository,
        CommentRepository $commentRepository,
        LanguageRepository $languageRepository,
        Request $request,
        EntityManagerInterface $entityManager,
        ?string $_locale = null
    ): Response {
        // CRITICAL: Ensure locale is always a string to prevent array conversion errors
        $locale = $_locale ?? 'en';
        if (is_array($locale)) {
            $locale = 'en';
        }
        $request->setLocale($locale);

        $criteria = ['slug' => $slug, 'status' => 'published'];

        // Get language from URL or default to English
        if ($locale && $locale !== 'en') {
            $language = $languageRepository->findOneBy(['code' => $locale, 'isActive' => true]);
            if ($language) {
                $criteria['language'] = $language;
            }
        } else {
            // Use default language (English)
            $defaultLanguage = $languageRepository->findOneBy(['isDefault' => true]);
            if ($defaultLanguage) {
                $criteria['language'] = $defaultLanguage;
            }
        }

        $post = $postRepository->findOneBy($criteria);

        if (!$post) {
            throw $this->createNotFoundException('Post not found');
        }

        $comments = $commentRepository->findApprovedByPost($post);
        $commentSubmitted = false;
        $error = null;

        // Handle comment submission
        if ($request->isMethod('POST') && $this->getUser()) {
            $content = $request->request->get('comment_content');

            if (empty($content)) {
                $error = 'Comment cannot be empty.';
            } elseif (strlen($content) < 3) {
                $error = 'Comment must be at least 3 characters long.';
            } else {
                $comment = new Comment();
                $comment->setContent($content);
                $comment->setPost($post);
                $comment->setAuthor($this->getUser());
                $comment->setStatus('pending');
                $comment->setIpAddress($request->getClientIp());
                $comment->setUserAgent($request->headers->get('User-Agent'));

                $entityManager->persist($comment);
                $entityManager->flush();

                $commentSubmitted = true;
            }
        }

        // Log post visit
        $entityManager->persist(new \App\Entity\PageVisit($request->getUri()));
        $entityManager->flush();

        return $this->render('frontend/post.html.twig', [
            'post' => $post,
            'comments' => $comments,
            'comment_submitted' => $commentSubmitted,
            'error' => $error,
        ]);
    }

    /**
     * Catch-all route for pages - must be last to avoid conflicts with other routes
     */
    #[Route('/{slug}', name: 'app_page_slug', priority: -1, requirements: ['slug' => '[a-zA-Z0-9\-_]+'])]
    #[Route('/{_locale}/{slug}', name: 'app_page_slug_locale', priority: -1, requirements: ['_locale' => 'en|de|es|fr|ca|it|pt|nl|pl|ru|ja|zh|ar|sv|no|da|fi|cs|sk|hu|ro|el|tr|uk|ko|hi|th|vi', 'slug' => '[a-zA-Z0-9\-_]+'])]
    public function pageBySlug(
        string $slug,
        PageRepository $pageRepository,
        LanguageRepository $languageRepository,
        EntityManagerInterface $em,
        Request $request,
        ?string $_locale = null
    ): Response {
        // CRITICAL: Ensure locale is always a string to prevent array conversion errors
        $locale = $_locale ?? 'en';
        if (is_array($locale)) {
            $locale = 'en';
        }
        $request->setLocale($locale);

        $criteria = ['slug' => $slug, 'isPublished' => true];

        // Get language from URL or default to English
        if ($locale && $locale !== 'en') {
            $language = $languageRepository->findOneBy(['code' => $locale, 'isActive' => true]);
            if ($language) {
                $criteria['language'] = $language;
            }
        } else {
            // Use default language (English)
            $defaultLanguage = $languageRepository->findOneBy(['isDefault' => true]);
            if ($defaultLanguage) {
                $criteria['language'] = $defaultLanguage;
            }
        }

        $page = $pageRepository->findOneBy($criteria);

        if (!$page) {
            throw $this->createNotFoundException('Page not found');
        }

        // Log page visit
        $em->persist(new \App\Entity\PageVisit($request->getUri()));
        $em->flush();

        // Determine which template to use based on page configuration
        $templateName = $page->getTemplate() ?? 'default';
        $templatePath = match($templateName) {
            'vue' => 'frontend/vue.html.twig',
            'default' => 'frontend/page.html.twig',
            default => "frontend/{$templateName}.html.twig"
        };

        return $this->render($templatePath, [
            'page' => $page,
        ]);
    }
}
