<?php

namespace App\Controller\Admin;

use App\Repository\ThemeRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin/theme')]
class ThemePreviewController extends AbstractController
{
    public function __construct(
        private ThemeRepository $themeRepository
    ) {
    }

    #[Route('/{id}/preview', name: 'admin_theme_preview')]
    public function preview(int $id): Response
    {
        $theme = $this->themeRepository->find($id);

        if (!$theme) {
            throw $this->createNotFoundException('Theme not found');
        }

        return $this->render('admin/theme/preview.html.twig', [
            'theme' => $theme
        ]);
    }

    #[Route('/{id}/preview-iframe', name: 'admin_theme_preview_iframe')]
    public function previewIframe(int $id): Response
    {
        $theme = $this->themeRepository->find($id);

        if (!$theme) {
            throw $this->createNotFoundException('Theme not found');
        }

        return $this->render('admin/theme/preview_iframe.html.twig', [
            'theme' => $theme,
            'previewMode' => true
        ]);
    }
}
