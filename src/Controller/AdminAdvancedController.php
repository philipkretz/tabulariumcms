<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Service\ExportImportService;
use App\Service\SettingsService;
use App\Repository\SeoUrlRepository;
use App\Entity\SeoUrl;

class AdminAdvancedController extends AbstractController
{
    #[Route('/admin/seo-urls', name: 'admin_seo_urls')]
    public function seoUrls(SeoUrlRepository $seoUrlRepository): Response
    {
        $seoUrls = $seoUrlRepository->findAll();
        
        return $this->render('admin/seo_urls/index.html.twig', [
            'seoUrls' => $seoUrls,
        ]);
    }

    #[Route('/admin/seo-urls/new', name: 'admin_seo_url_new')]
    public function newSeoUrl(Request $request, SeoUrlRepository $seoUrlRepository): Response
    {
        $seoUrl = new SeoUrl();
        
        if ($request->isMethod('POST')) {
            $seoUrl->setUrl($request->request->get('url'));
            $seoUrl->setRoute($request->request->get('route'));
            $seoUrl->setParameters(json_decode($request->request->get('parameters', '[]'), true));
            $seoUrl->setLocale($request->request->get('locale', 'en'));
            $seoUrl->setPriority($request->request->get('priority', 0));
            $seoUrl->setActive($request->request->get('is_active', false));
            $seoUrl->setTitle($request->request->get('title'));
            $seoUrl->setDescription($request->request->get('description'));
            $seoUrl->setCanonicalUrl($request->request->get('canonical_url'));
            $seoUrl->setMetaTags(json_decode($request->request->get('meta_tags', '[]'), true));
            $seoUrl->setStatusCode($request->request->get('status_code', '301'));
            
            $seoUrlRepository->save($seoUrl, true);
            
            $this->addFlash('success', 'SEO URL created successfully.');
            
            return $this->redirectToRoute('admin_seo_urls');
        }
        
        return $this->render('admin/seo_urls/form.html.twig', [
            'seoUrl' => $seoUrl,
        ]);
    }

    #[Route('/admin/seo-urls/{id}/edit', name: 'admin_seo_url_edit')]
    public function editSeoUrl(SeoUrl $seoUrl, Request $request, SeoUrlRepository $seoUrlRepository): Response
    {
        if ($request->isMethod('POST')) {
            $seoUrl->setUrl($request->request->get('url'));
            $seoUrl->setRoute($request->request->get('route'));
            $seoUrl->setParameters(json_decode($request->request->get('parameters', '[]'), true));
            $seoUrl->setLocale($request->request->get('locale', 'en'));
            $seoUrl->setPriority($request->request->get('priority', 0));
            $seoUrl->setActive($request->request->get('is_active', false));
            $seoUrl->setTitle($request->request->get('title'));
            $seoUrl->setDescription($request->request->get('description'));
            $seoUrl->setCanonicalUrl($request->request->get('canonical_url'));
            $seoUrl->setMetaTags(json_decode($request->request->get('meta_tags', '[]'), true));
            $seoUrl->setStatusCode($request->request->get('status_code', '301'));
            
            $seoUrlRepository->save($seoUrl, true);
            
            $this->addFlash('success', 'SEO URL updated successfully.');
            
            return $this->redirectToRoute('admin_seo_urls');
        }
        
        return $this->render('admin/seo_urls/form.html.twig', [
            'seoUrl' => $seoUrl,
        ]);
    }

    #[Route('/admin/seo-urls/{id}/delete', name: 'admin_seo_url_delete')]
    public function deleteSeoUrl(SeoUrl $seoUrl, SeoUrlRepository $seoUrlRepository): Response
    {
        $seoUrlRepository->remove($seoUrl, true);
        
        $this->addFlash('success', 'SEO URL deleted successfully.');
        
        return $this->redirectToRoute('admin_seo_urls');
    }

    #[Route('/admin/settings', name: 'admin_settings')]
    public function settings(SettingsService $settingsService): Response
    {
        return $this->render('admin/settings/index.html.twig', [
            'paymentSettings' => $settingsService->getPaymentSettings(),
            'seoSettings' => $settingsService->getSeoSettings(),
            'blogSettings' => $settingsService->getBlogSettings(),
        ]);
    }

    #[Route('/admin/settings/save', name: 'admin_settings_save')]
    public function saveSettings(Request $request, SettingsService $settingsService): Response
    {
        $settings = $request->request->all();
        
        foreach ($settings as $key => $value) {
            $type = $this->getSettingType($key);
            $settingsService->set($key, $value, $type, 'global', $this->getSettingCategory($key));
        }
        
        $this->addFlash('success', 'Settings saved successfully.');
        
        return $this->redirectToRoute('admin_settings');
    }

    #[Route('/admin/export', name: 'admin_export')]
    public function export(): Response
    {
        return $this->render('admin/export_import/export.html.twig');
    }

    #[Route('/admin/export/execute', name: 'admin_export_execute')]
    public function executeExport(Request $request, ExportImportService $exportImportService): Response
    {
        $options = [
            'content' => $request->request->all('content_types') ?? ['posts', 'pages'],
            'include_translations' => $request->request->get('include_translations', false),
            'include_media_files' => $request->request->get('include_media_files', false),
        ];
        
        return $exportImportService->export($options);
    }

    #[Route('/admin/import', name: 'admin_import')]
    public function import(): Response
    {
        return $this->render('admin/export_import/import.html.twig');
    }

    #[Route('/admin/import/execute', name: 'admin_import_execute')]
    public function executeImport(Request $request, ExportImportService $exportImportService): Response
    {
        $options = [
            'overwrite' => $request->request->get('overwrite', false),
            'content' => $request->request->all('import_content_types') ?? [],
        ];
        
        $result = $exportImportService->import($request);
        
        if ($request->isXmlHttpRequest()) {
            return $result;
        }
        
        $this->addFlash('success', 'Import completed successfully.');
        
        return $this->redirectToRoute('admin_import');
    }

    private function getSettingType(string $key): string
    {
        $types = [
            'stripe_enabled' => 'boolean',
            'paypal_enabled' => 'boolean',
            'paypal_sandbox' => 'boolean',
            'auto_invoice' => 'boolean',
            'structured_data_enabled' => 'boolean',
            'enable_comments' => 'boolean',
            'comment_moderation' => 'boolean',
            'enable_rss' => 'boolean',
            'enable_categories' => 'boolean',
            'enable_tags' => 'boolean',
            'auto_generate_excerpt' => 'boolean',
            'posts_per_page' => 'integer',
            'rss_items' => 'integer',
            'payment_timeout' => 'integer',
            'excerpt_length' => 'integer',
            'max_upload_size' => 'integer',
            'image_quality' => 'integer',
            'auto_generate_thumbnails' => 'boolean',
            'allowed_file_types' => 'array',
            'site_keywords' => 'array',
            'bank_account_info' => 'array',
        ];
        
        return $types[$key] ?? 'string';
    }

    private function getSettingCategory(string $key): string
    {
        $categories = [
            'stripe_enabled' => 'payment',
            'stripe_public_key' => 'payment',
            'stripe_secret_key' => 'payment',
            'stripe_webhook_secret' => 'payment',
            'paypal_enabled' => 'payment',
            'paypal_client_id' => 'payment',
            'paypal_client_secret' => 'payment',
            'paypal_sandbox' => 'payment',
            'bank_transfer_enabled' => 'payment',
            'bank_account_info' => 'payment',
            'auto_invoice' => 'payment',
            'payment_timeout' => 'payment',
            'default_currency' => 'payment',
            'site_title' => 'general',
            'site_description' => 'general',
            'site_keywords' => 'seo',
            'default_locale' => 'general',
            'canonical_domain' => 'seo',
            'robots_txt' => 'seo',
            'google_analytics' => 'seo',
            'google_search_console' => 'seo',
            'structured_data_enabled' => 'seo',
            'posts_per_page' => 'blog',
            'enable_comments' => 'blog',
            'comment_moderation' => 'blog',
            'enable_rss' => 'blog',
            'rss_items' => 'blog',
            'enable_categories' => 'blog',
            'enable_tags' => 'blog',
            'auto_generate_excerpt' => 'blog',
            'excerpt_length' => 'blog',
            'max_upload_size' => 'media',
            'allowed_file_types' => 'media',
            'image_quality' => 'media',
            'auto_generate_thumbnails' => 'media',
            'timezone' => 'general',
        ];
        
        return $categories[$key] ?? 'general';
    }
}