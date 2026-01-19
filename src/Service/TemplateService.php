<?php

namespace App\Service;

use App\Entity\Template;
use App\Entity\Menu;
use App\Entity\Page;
use App\Entity\Post;
use App\Entity\Comment;
use App\Repository\TemplateRepository;
use App\Repository\MenuRepository;
use App\Repository\PageRepository;
use App\Repository\PostRepository;
use App\Repository\CommentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class TemplateService
{
    public function __construct(
        private TemplateRepository $templateRepository,
        private MenuRepository $menuRepository,
        private PageRepository $pageRepository,
        private PostRepository $postRepository,
        private CommentRepository $commentRepository,
        private EntityManagerInterface $entityManager,
        private UrlGeneratorInterface $urlGenerator,
        private TranslatorInterface $translator
    ) {
    }

    public function renderTemplate(string $identifier): ?string
    {
        $template = $this->templateRepository->findOneByIdentifier($identifier);
        if (!$template) {
            return null;
        }

        return $this->processBracketFunctions($template->getContent());
    }

    public function renderByPosition(string $position): string
    {
        $templates = $this->templateRepository->findByPosition($position);
        $content = '';

        foreach ($templates as $template) {
            $content .= $this->processBracketFunctions($template->getContent());
        }

        return $content;
    }

    public function processBracketFunctions(string $content): string
    {
        // Pattern to match [function_name param1="value1" param2="value2"]
        $pattern = '/\[([a-z_]+)\s+([^\]]+)\]/';

        // phpcs:ignore Security.BadFunctions.CallbackFunctions -- Safe inline closure for template processing
        return preg_replace_callback($pattern, function ($matches) {
            $functionName = $matches[1];
            $params = $this->parseParameters($matches[2]);

            return $this->executeBracketFunction($functionName, $params);
        }, $content);
    }

    private function parseParameters(string $paramString): array
    {
        $params = [];
        // Pattern to match param="value"
        $pattern = '/([a-z_]+)\s*=\s*"([^"]*)"/';

        if (preg_match_all($pattern, $paramString, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $match) {
                $params[$match[1]] = $match[2];
            }
        }

        return $params;
    }

    private function executeBracketFunction(string $functionName, array $params): string
    {
        try {
            return match ($functionName) {
                'menu' => $this->renderMenu($params),
                'page' => $this->renderPage($params),
                'post' => $this->renderPost($params),
                'posts' => $this->renderPosts($params),
                'comments' => $this->renderComments($params),
                'menu_items' => $this->renderMenuItems($params),
                'pages' => $this->renderPages($params),
                'site_title' => $this->renderSiteTitle($params),
                'site_description' => $this->renderSiteDescription($params),
                'current_year' => date('Y'),
                default => "[Unknown function: {$functionName}]"
            };
        } catch (\Exception $e) {
            return "[Error in {$functionName}: {$e->getMessage()}]";
        }
    }

    private function renderMenu(array $params): string
    {
        $menu = null;
        
        if (isset($params['id'])) {
            $menu = $this->menuRepository->find($params['id']);
        } elseif (isset($params['name'])) {
            $menu = $this->menuRepository->findOneBy(['identifier' => $params['name']]);
        }

        if (!$menu) {
            return '[Menu not found]';
        }

        $menuItems = $menu->getMenuItems()->filter(fn($item) => $item->isActive());
        
        $html = '<nav class="menu-' . $menu->getIdentifier() . '">';
        
        if (isset($params['class'])) {
            $html = '<nav class="' . $params['class'] . '">';
        }

        $html .= '<ul>';
        
        foreach ($menuItems as $item) {
            if (!$item->getParent()) {
                $html .= $this->renderMenuItem($item, $params);
            }
        }
        
        $html .= '</ul></nav>';

        return $html;
    }

    private function renderMenuItem($item, array $params): string
    {
        $url = $item->getEffectiveUrl();
        $html = '<li><a href="' . htmlspecialchars($url) . '"';
        
        if ($item->isOpenInNewTab()) {
            $html .= ' target="_blank"';
        }
        
        if ($item->getCssClass()) {
            $html .= ' class="' . htmlspecialchars($item->getCssClass()) . '"';
        }
        
        $html .= '>' . htmlspecialchars($item->getTitle()) . '</a>';
        
        // Render children
        $children = $item->getChildren()->filter(fn($child) => $child->isActive());
        if ($children->count() > 0) {
            $html .= '<ul>';
            foreach ($children as $child) {
                $html .= $this->renderMenuItem($child, $params);
            }
            $html .= '</ul>';
        }
        
        $html .= '</li>';
        
        return $html;
    }

    private function renderPage(array $params): string
    {
        $page = null;
        
        if (isset($params['id'])) {
            $page = $this->pageRepository->find($params['id']);
        } elseif (isset($params['slug'])) {
            $page = $this->pageRepository->findOneBy(['slug' => $params['slug']]);
        }

        if (!$page) {
            return '[Page not found]';
        }

        $content = $page->getContent();
        
        if (isset($params['length'])) {
            $content = substr($content, 0, (int)$params['length']) . '...';
        }
        
        if (!isset($params['raw']) || $params['raw'] !== 'true') {
            $content = '<div class="page-content">' . $content . '</div>';
        }

        return $content;
    }

    private function renderPost(array $params): string
    {
        $post = null;
        
        if (isset($params['id'])) {
            $post = $this->postRepository->find($params['id']);
        } elseif (isset($params['slug'])) {
            $post = $this->postRepository->findOneBy(['slug' => $params['slug']]);
        } elseif (isset($params['latest'])) {
            $post = $this->postRepository->findOneBy([], ['publishedAt' => 'DESC']);
        }

        if (!$post) {
            return '[Post not found]';
        }

        $title = $post->getTitle();
        $content = $post->getContent();
        
        if (isset($params['length'])) {
            $content = substr($content, 0, (int)$params['length']) . '...';
        }
        
        $html = '<article class="post-item">';
        $html .= '<h3>' . htmlspecialchars($title) . '</h3>';
        $html .= '<div class="post-content">' . $content . '</div>';
        
        if (isset($params['link']) && $params['link'] === 'true') {
            $url = $this->urlGenerator->generate('app_post', ['slug' => $post->getSlug()]);
            $html .= '<a href="' . $url . '" class="read-more">Read more</a>';
        }
        
        $html .= '</article>';

        return $html;
    }

    private function renderPosts(array $params): string
    {
        $limit = $params['count'] ?? 5;
        $posts = $this->postRepository->findBy(
            ['publishedAt' => new \DateTime()], // Only published posts
            ['publishedAt' => 'DESC'],
            $limit
        );

        $html = '<div class="posts-list">';
        
        foreach ($posts as $post) {
            $html .= $this->renderPost(['id' => $post->getId(), 'link' => 'true', 'length' => $params['length'] ?? 200]);
        }
        
        $html .= '</div>';

        return $html;
    }

    private function renderComments(array $params): string
    {
        $limit = $params['count'] ?? 5;
        $postId = $params['post_id'] ?? null;

        if (!$postId) {
            return '[post_id parameter required]';
        }

        $post = $this->postRepository->find($postId);
        if (!$post) {
            return '[Post not found]';
        }

        $comments = $this->commentRepository->findBy(
            ['post' => $post, 'isActive' => true],
            ['createdAt' => 'DESC'],
            $limit
        );

        $html = '<div class="comments-list">';
        
        foreach ($comments as $comment) {
            $html .= '<div class="comment-item">';
            $html .= '<strong>' . htmlspecialchars($comment->getAuthor()->getUsername()) . '</strong>';
            $html .= '<span class="comment-date">' . $comment->getCreatedAt()->format('M j, Y') . '</span>';
            $html .= '<p>' . htmlspecialchars($comment->getContent()) . '</p>';
            $html .= '</div>';
        }
        
        $html .= '</div>';

        return $html;
    }

    private function renderMenuItems(array $params): string
    {
        $menuId = $params['menu_id'] ?? null;
        
        if (!$menuId) {
            return '[menu_id parameter required]';
        }

        $menu = $this->menuRepository->find($menuId);
        if (!$menu) {
            return '[Menu not found]';
        }

        $menuItems = $menu->getMenuItems()->filter(fn($item) => $item->isActive());
        $html = '';
        
        foreach ($menuItems as $item) {
            if (!$item->getParent()) {
                $url = $item->getEffectiveUrl();
                $html .= '<a href="' . htmlspecialchars($url) . '"';
                $html .= ' class="menu-item ' . ($item->getCssClass() ?? '') . '"';
                if ($item->isOpenInNewTab()) {
                    $html .= ' target="_blank"';
                }
                $html .= '>' . htmlspecialchars($item->getTitle()) . '</a>';
            }
        }

        return $html;
    }

    private function renderPages(array $params): string
    {
        $limit = $params['count'] ?? 10;
        $pages = $this->pageRepository->findBy(
            ['isActive' => true],
            ['title' => 'ASC'],
            $limit
        );

        $html = '<ul class="pages-list">';
        
        foreach ($pages as $page) {
            $url = $this->urlGenerator->generate('app_page', ['slug' => $page->getSlug()]);
            $html .= '<li><a href="' . $url . '">' . htmlspecialchars($page->getTitle()) . '</a></li>';
        }
        
        $html .= '</ul>';

        return $html;
    }

    private function renderSiteTitle(array $params): string
    {
        // This would typically come from SiteSettings
        return 'Profundi Web Solutions, S.L.';
    }

    private function renderSiteDescription(array $params): string
    {
        // This would typically come from SiteSettings
        return 'Professional web development and digital solutions';
    }
}