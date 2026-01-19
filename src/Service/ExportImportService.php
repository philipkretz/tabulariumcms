<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Uid\Uuid;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\PostRepository;
use App\Repository\PageRepository;
use App\Repository\UserRepository;
use App\Repository\MediaRepository;
use App\Repository\BookingRepository;
use App\Repository\SiteSettingsRepository;
use App\Repository\SeoUrlRepository;

class ExportImportService
{
    private EntityManagerInterface $entityManager;
    private PostRepository $postRepository;
    private PageRepository $pageRepository;
    private UserRepository $userRepository;
    private MediaRepository $mediaRepository;
    private BookingRepository $bookingRepository;
    private SiteSettingsRepository $settingsRepository;
    private SeoUrlRepository $seoUrlRepository;

    public function __construct(
        EntityManagerInterface $entityManager,
        PostRepository $postRepository,
        PageRepository $pageRepository,
        UserRepository $userRepository,
        MediaRepository $mediaRepository,
        BookingRepository $bookingRepository,
        SiteSettingsRepository $settingsRepository,
        SeoUrlRepository $seoUrlRepository
    ) {
        $this->entityManager = $entityManager;
        $this->postRepository = $postRepository;
        $this->pageRepository = $pageRepository;
        $this->userRepository = $userRepository;
        $this->mediaRepository = $mediaRepository;
        $this->bookingRepository = $bookingRepository;
        $this->settingsRepository = $settingsRepository;
        $this->seoUrlRepository = $seoUrlRepository;
    }

    public function export(array $options = []): Response
    {
        $exportData = [
            'metadata' => [
                'version' => '1.0',
                'exported_at' => (new \DateTime())->format('c'),
                'export_id' => Uuid::v4()->toRfc4122(),
                'export_options' => $options,
                'cms_version' => 'TabulariumCMS 1.0'
            ],
            'content' => []
        ];

        // Export content based on options
        if (in_array('posts', $options['content'] ?? ['posts'])) {
            $exportData['content']['posts'] = $this->exportPosts($options);
        }

        if (in_array('pages', $options['content'] ?? ['pages'])) {
            $exportData['content']['pages'] = $this->exportPages($options);
        }

        if (in_array('users', $options['content'] ?? [])) {
            $exportData['content']['users'] = $this->exportUsers($options);
        }

        if (in_array('media', $options['content'] ?? [])) {
            $exportData['content']['media'] = $this->exportMedia($options);
        }

        if (in_array('bookings', $options['content'] ?? [])) {
            $exportData['content']['bookings'] = $this->exportBookings($options);
        }

        if (in_array('settings', $options['content'] ?? ['settings'])) {
            $exportData['content']['settings'] = $this->exportSettings($options);
        }

        if (in_array('seo_urls', $options['content'] ?? [])) {
            $exportData['content']['seo_urls'] = $this->exportSeoUrls($options);
        }

        // Create export file
        $filename = 'tabulariumcms_export_' . date('Y-m-d_H-i-s') . '.json';
        $filepath = sys_get_temp_dir() . '/' . $filename;
        // phpcs:ignore Security.BadFunctions.FilesystemFunctions -- Controlled temp directory
        file_put_contents($filepath, json_encode($exportData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        return new BinaryFileResponse($filepath, 200, [
            'Content-Type' => 'application/json',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"'
        ]);
    }

    public function import(Request $request): JsonResponse
    {
        $file = $request->files->get('import_file');
        if (!$file) {
            return new JsonResponse(['error' => 'No file uploaded'], 400);
        }

        // phpcs:ignore Security.BadFunctions.FilesystemFunctions -- Symfony UploadedFile pathname
        $content = file_get_contents($file->getPathname());
        $data = json_decode($content, true);

        if (!$data || !isset($data['metadata'])) {
            return new JsonResponse(['error' => 'Invalid export file format'], 400);
        }

        $options = $request->request->all('import_options') ?? [];
        $results = [
            'imported' => [],
            'skipped' => [],
            'errors' => []
        ];

        // Import content based on options
        if (in_array('posts', $options['content'] ?? [])) {
            $result = $this->importPosts($data['content']['posts'] ?? [], $options);
            $results['imported']['posts'] = $result['imported'];
            $results['skipped']['posts'] = $result['skipped'];
            $results['errors'] = array_merge($results['errors'], $result['errors']);
        }

        if (in_array('pages', $options['content'] ?? [])) {
            $result = $this->importPages($data['content']['pages'] ?? [], $options);
            $results['imported']['pages'] = $result['imported'];
            $results['skipped']['pages'] = $result['skipped'];
            $results['errors'] = array_merge($results['errors'], $result['errors']);
        }

        if (in_array('users', $options['content'] ?? [])) {
            $result = $this->importUsers($data['content']['users'] ?? [], $options);
            $results['imported']['users'] = $result['imported'];
            $results['skipped']['users'] = $result['skipped'];
            $results['errors'] = array_merge($results['errors'], $result['errors']);
        }

        if (in_array('settings', $options['content'] ?? [])) {
            $result = $this->importSettings($data['content']['settings'] ?? [], $options);
            $results['imported']['settings'] = $result['imported'];
            $results['skipped']['settings'] = $result['skipped'];
            $results['errors'] = array_merge($results['errors'], $result['errors']);
        }

        if (in_array('seo_urls', $options['content'] ?? [])) {
            $result = $this->importSeoUrls($data['content']['seo_urls'] ?? [], $options);
            $results['imported']['seo_urls'] = $result['imported'];
            $results['skipped']['seo_urls'] = $result['skipped'];
            $results['errors'] = array_merge($results['errors'], $result['errors']);
        }

        return new JsonResponse($results);
    }

    private function exportPosts(array $options): array
    {
        $posts = $this->postRepository->findAll();
        $exported = [];

        foreach ($posts as $post) {
            $postData = [
                'id' => $post->getId(),
                'title' => $post->getTitle(),
                'slug' => $post->getSlug(),
                'content' => $post->getContent(),
                'excerpt' => $post->getExcerpt(),
                'status' => $post->getStatus(),
                'featured_image' => $post->getFeaturedImage(),
                'meta_title' => $post->getMetaTitle(),
                'meta_description' => $post->getMetaDescription(),
                'meta_keywords' => $post->getMetaKeywords(),
                'created_at' => $post->getCreatedAt()?->format('c'),
                'updated_at' => $post->getUpdatedAt()?->format('c'),
                'published_at' => $post->getPublishedAt()?->format('c'),
                'view_count' => $post->getViewCount(),
                'is_comment_enabled' => $post->isCommentEnabled(),
                'is_pinned' => $post->isPinned(),
                'author_email' => $post->getAuthor()?->getEmail(),
            ];

            if ($options['include_translations'] ?? false) {
                // Add translations logic here
                $postData['translations'] = [];
            }

            $exported[] = $postData;
        }

        return $exported;
    }

    private function exportPages(array $options): array
    {
        $pages = $this->pageRepository->findAll();
        $exported = [];

        foreach ($pages as $page) {
            $pageData = [
                'id' => $page->getId(),
                'title' => $page->getTitle(),
                'slug' => $page->getSlug(),
                'content' => $page->getContent(),
                'template' => $page->getTemplate(),
                'is_published' => $page->isPublished(),
                'is_home_page' => $page->isHomePage(),
                'sort_order' => $page->getSortOrder(),
                'meta_title' => $page->getMetaTitle(),
                'meta_description' => $page->getMetaDescription(),
                'meta_keywords' => $page->getMetaKeywords(),
                'created_at' => $page->getCreatedAt()?->format('c'),
                'updated_at' => $page->getUpdatedAt()?->format('c'),
                'author_email' => $page->getAuthor()?->getEmail(),
            ];
            $exported[] = $pageData;
        }

        return $exported;
    }

    private function exportUsers(array $options): array
    {
        $users = $this->userRepository->findAll();
        $exported = [];

        foreach ($users as $user) {
            // Export only basic info for privacy
            $userData = [
                'id' => $user->getId(),
                'email' => $user->getEmail(),
                'username' => $user->getUsername(),
                'first_name' => $user->getFirstName(),
                'last_name' => $user->getLastName(),
                'roles' => $user->getRoles(),
                'is_verified' => $user->isVerified(),
                'is_active' => $user->isActive(),
                'locale' => $user->getLocale(),
                'currency' => $user->getCurrency(),
                'created_at' => $user->getCreatedAt()?->format('c'),
                'last_login_at' => $user->getLastLoginAt()?->format('c'),
            ];
            $exported[] = $userData;
        }

        return $exported;
    }

    private function exportMedia(array $options): array
    {
        $media = $this->mediaRepository->findAll();
        $exported = [];

        foreach ($media as $item) {
            $mediaData = [
                'id' => $item->getId(),
                'filename' => $item->getFilename(),
                'original_name' => $item->getOriginalName(),
                'mime_type' => $item->getMimeType(),
                'size' => $item->getSize(),
                'alt' => $item->getAlt(),
                'description' => $item->getDescription(),
                'type' => $item->getType(),
                'created_at' => $item->getCreatedAt()?->format('c'),
            ];
            $exported[] = $mediaData;
        }

        return $exported;
    }

    private function exportBookings(array $options): array
    {
        $bookings = $this->bookingRepository->findAll();
        $exported = [];

        foreach ($bookings as $booking) {
            $bookingData = [
                'id' => $booking->getId(),
                'booking_type' => $booking->getBookingType(),
                'status' => $booking->getStatus(),
                'start_date' => $booking->getStartDate()?->format('c'),
                'end_date' => $booking->getEndDate()?->format('c'),
                'total_price' => $booking->getTotalPrice(),
                'deposit' => $booking->getDeposit(),
                'currency' => $booking->getCurrency(),
                'quantity' => $booking->getQuantity(),
                'details' => $booking->getDetails(),
                'notes' => $booking->getNotes(),
                'created_at' => $booking->getCreatedAt()?->format('c'),
                'updated_at' => $booking->getUpdatedAt()?->format('c'),
                'user_email' => $booking->getUser()?->getEmail(),
            ];
            $exported[] = $bookingData;
        }

        return $exported;
    }

    private function exportSettings(array $options): array
    {
        $settings = $this->settingsRepository->findAll();
        $exported = [];

        foreach ($settings as $setting) {
            $settingData = [
                'key' => $setting->getSettingKey(),
                'value' => $setting->getValue(),
                'type' => $setting->getSettingType(),
                'locale' => $setting->getLocale(),
                'category' => $setting->getCategory(),
                'description' => $setting->getDescription(),
                'is_public' => $setting->isPublic(),
                'created_at' => $setting->getCreatedAt()?->format('c'),
                'updated_at' => $setting->getUpdatedAt()?->format('c'),
            ];
            $exported[] = $settingData;
        }

        return $exported;
    }

    private function exportSeoUrls(array $options): array
    {
        $seoUrls = $this->seoUrlRepository->findAll();
        $exported = [];

        foreach ($seoUrls as $seoUrl) {
            $urlData = [
                'id' => $seoUrl->getId(),
                'url' => $seoUrl->getUrl(),
                'route' => $seoUrl->getRoute(),
                'parameters' => $seoUrl->getParameters(),
                'locale' => $seoUrl->getLocale(),
                'priority' => $seoUrl->getPriority(),
                'is_active' => $seoUrl->isActive(),
                'title' => $seoUrl->getTitle(),
                'description' => $seoUrl->getDescription(),
                'canonical_url' => $seoUrl->getCanonicalUrl(),
                'meta_tags' => $seoUrl->getMetaTags(),
                'status_code' => $seoUrl->getStatusCode(),
                'created_at' => $seoUrl->getCreatedAt()?->format('c'),
                'updated_at' => $seoUrl->getUpdatedAt()?->format('c'),
            ];
            $exported[] = $urlData;
        }

        return $exported;
    }

    private function importPosts(array $posts, array $options): array
    {
        $imported = 0;
        $skipped = 0;
        $errors = [];

        foreach ($posts as $postData) {
            try {
                // Check if post already exists
                $existingPost = $this->postRepository->findOneBy(['slug' => $postData['slug']]);
                
                if ($existingPost && !($options['overwrite'] ?? false)) {
                    $skipped++;
                    continue;
                }

                // Import post logic here
                $imported++;
            } catch (\Exception $e) {
                $errors[] = "Error importing post {$postData['title']}: " . $e->getMessage();
            }
        }

        if ($imported > 0) {
            $this->entityManager->flush();
        }

        return [
            'imported' => $imported,
            'skipped' => $skipped,
            'errors' => $errors
        ];
    }

    private function importPages(array $pages, array $options): array
    {
        $imported = 0;
        $skipped = 0;
        $errors = [];

        foreach ($pages as $pageData) {
            try {
                $existingPage = $this->pageRepository->findOneBy(['slug' => $pageData['slug']]);
                
                if ($existingPage && !($options['overwrite'] ?? false)) {
                    $skipped++;
                    continue;
                }

                // Import page logic here
                $imported++;
            } catch (\Exception $e) {
                $errors[] = "Error importing page {$pageData['title']}: " . $e->getMessage();
            }
        }

        if ($imported > 0) {
            $this->entityManager->flush();
        }

        return [
            'imported' => $imported,
            'skipped' => $skipped,
            'errors' => $errors
        ];
    }

    private function importUsers(array $users, array $options): array
    {
        $imported = 0;
        $skipped = 0;
        $errors = [];

        foreach ($users as $userData) {
            try {
                $existingUser = $this->userRepository->findOneBy(['email' => $userData['email']]);
                
                if ($existingUser && !($options['overwrite'] ?? false)) {
                    $skipped++;
                    continue;
                }

                // Import user logic here (without password)
                $imported++;
            } catch (\Exception $e) {
                $errors[] = "Error importing user {$userData['email']}: " . $e->getMessage();
            }
        }

        if ($imported > 0) {
            $this->entityManager->flush();
        }

        return [
            'imported' => $imported,
            'skipped' => $skipped,
            'errors' => $errors
        ];
    }

    private function importSettings(array $settings, array $options): array
    {
        $imported = 0;
        $skipped = 0;
        $errors = [];

        foreach ($settings as $settingData) {
            try {
                $existingSetting = $this->settingsRepository->findByKeyAndLocale(
                    $settingData['key'], 
                    $settingData['locale']
                );
                
                if ($existingSetting && !($options['overwrite'] ?? false)) {
                    $skipped++;
                    continue;
                }

                // Import setting logic here
                $imported++;
            } catch (\Exception $e) {
                $errors[] = "Error importing setting {$settingData['key']}: " . $e->getMessage();
            }
        }

        if ($imported > 0) {
            $this->entityManager->flush();
        }

        return [
            'imported' => $imported,
            'skipped' => $skipped,
            'errors' => $errors
        ];
    }

    private function importSeoUrls(array $seoUrls, array $options): array
    {
        $imported = 0;
        $skipped = 0;
        $errors = [];

        foreach ($seoUrls as $urlData) {
            try {
                $existingUrl = $this->seoUrlRepository->findByUrlAndLocale(
                    $urlData['url'], 
                    $urlData['locale']
                );
                
                if ($existingUrl && !($options['overwrite'] ?? false)) {
                    $skipped++;
                    continue;
                }

                // Import SEO URL logic here
                $imported++;
            } catch (\Exception $e) {
                $errors[] = "Error importing SEO URL {$urlData['url']}: " . $e->getMessage();
            }
        }

        if ($imported > 0) {
            $this->entityManager->flush();
        }

        return [
            'imported' => $imported,
            'skipped' => $skipped,
            'errors' => $errors
        ];
    }
}