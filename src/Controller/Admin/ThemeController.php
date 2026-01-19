<?php

namespace App\Controller\Admin;

use App\Entity\Theme;
use App\Repository\ThemeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

#[Route('/admin/theme')]
class ThemeController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private ThemeRepository $themeRepository,
        private SluggerInterface $slugger
    ) {}

    #[Route('/export/{id}', name: 'admin_theme_export', methods: ['GET'])]
    public function export(int $id): Response
    {
        $theme = $this->themeRepository->find($id);

        if (!$theme) {
            $this->addFlash('error', 'Theme not found');
            return $this->redirectToRoute('admin_app_theme_list');
        }

        // Prepare theme data for export
        $exportData = [
            'name' => $theme->getName(),
            'displayName' => $theme->getDisplayName(),
            'description' => $theme->getDescription(),
            'author' => $theme->getAuthor(),
            'version' => $theme->getVersion(),
            'category' => $theme->getCategory(),
            'thumbnailPath' => $theme->getThumbnailPath(),
            'config' => $theme->getConfig(),
            'files' => $theme->getFiles(),

            // Theme customization fields
            'primaryColor' => $theme->getPrimaryColor(),
            'secondaryColor' => $theme->getSecondaryColor(),
            'accentColor' => $theme->getAccentColor(),
            'backgroundColor' => $theme->getBackgroundColor(),
            'textColor' => $theme->getTextColor(),
            'headingFont' => $theme->getHeadingFont(),
            'bodyFont' => $theme->getBodyFont(),
            'fontSize' => $theme->getFontSize(),
            'sidebarPosition' => $theme->getSidebarPosition(),
            'headerStyle' => $theme->getHeaderStyle(),
            'containerWidth' => $theme->getContainerWidth(),
            'customCss' => $theme->getCustomCss(),

            // Export metadata
            'exportedAt' => (new \DateTimeImmutable())->format('Y-m-d H:i:s'),
            'exportVersion' => '1.0'
        ];

        $json = json_encode($exportData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        $filename = $this->slugger->slug($theme->getName())->lower() . '-theme-export-' . date('Y-m-d') . '.json';

        return new Response($json, 200, [
            'Content-Type' => 'application/json',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"'
        ]);
    }

    #[Route('/import', name: 'admin_theme_import', methods: ['GET', 'POST'])]
    public function import(Request $request): Response
    {
        if ($request->isMethod('POST')) {
            /** @var UploadedFile $file */
            $file = $request->files->get('theme_file');

            if (!$file) {
                $this->addFlash('error', 'Please select a theme file to import');
                return $this->render('admin/theme/import.html.twig');
            }

            if ($file->getClientOriginalExtension() !== 'json') {
                $this->addFlash('error', 'Invalid file format. Please upload a JSON file.');
                return $this->render('admin/theme/import.html.twig');
            }

            try {
                // phpcs:ignore Security.BadFunctions.FilesystemFunctions -- UploadedFile::getPathname() is a controlled Symfony file path
                $jsonContent = file_get_contents($file->getPathname());
                $themeData = json_decode($jsonContent, true);

                if (!$themeData || !isset($themeData['name'])) {
                    $this->addFlash('error', 'Invalid theme file format');
                    return $this->render('admin/theme/import.html.twig');
                }

                // Check if theme with same name exists
                $existingTheme = $this->themeRepository->findOneBy(['name' => $themeData['name']]);
                if ($existingTheme) {
                    // Generate unique name
                    $themeData['name'] = $themeData['name'] . '-imported-' . uniqid();
                    $themeData['displayName'] = ($themeData['displayName'] ?? 'Theme') . ' (Imported)';
                }

                // Create new theme
                $theme = new Theme();
                $theme->setName($themeData['name']);
                $theme->setDisplayName($themeData['displayName'] ?? $themeData['name']);
                $theme->setDescription($themeData['description'] ?? '');
                $theme->setAuthor($themeData['author'] ?? 'Unknown');
                $theme->setVersion($themeData['version'] ?? '1.0.0');
                $theme->setCategory($themeData['category'] ?? 'user');
                $theme->setThumbnailPath($themeData['thumbnailPath'] ?? '');
                $theme->setConfig($themeData['config'] ?? []);
                $theme->setFiles($themeData['files'] ?? []);

                // Set theme customization
                if (isset($themeData['primaryColor'])) $theme->setPrimaryColor($themeData['primaryColor']);
                if (isset($themeData['secondaryColor'])) $theme->setSecondaryColor($themeData['secondaryColor']);
                if (isset($themeData['accentColor'])) $theme->setAccentColor($themeData['accentColor']);
                if (isset($themeData['backgroundColor'])) $theme->setBackgroundColor($themeData['backgroundColor']);
                if (isset($themeData['textColor'])) $theme->setTextColor($themeData['textColor']);
                if (isset($themeData['headingFont'])) $theme->setHeadingFont($themeData['headingFont']);
                if (isset($themeData['bodyFont'])) $theme->setBodyFont($themeData['bodyFont']);
                if (isset($themeData['fontSize'])) $theme->setFontSize($themeData['fontSize']);
                if (isset($themeData['sidebarPosition'])) $theme->setSidebarPosition($themeData['sidebarPosition']);
                if (isset($themeData['headerStyle'])) $theme->setHeaderStyle($themeData['headerStyle']);
                if (isset($themeData['containerWidth'])) $theme->setContainerWidth($themeData['containerWidth']);
                if (isset($themeData['customCss'])) $theme->setCustomCss($themeData['customCss']);

                // Imported themes are not active/default by default
                $theme->setActive(false);
                $theme->setDefault(false);

                $this->entityManager->persist($theme);
                $this->entityManager->flush();

                $this->addFlash('success', sprintf('Theme "%s" imported successfully!', $theme->getDisplayName()));
                return $this->redirectToRoute('admin_app_theme_list');

            } catch (\Exception $e) {
                $this->addFlash('error', 'Error importing theme: ' . $e->getMessage());
                return $this->render('admin/theme/import.html.twig');
            }
        }

        return $this->render('admin/theme/import.html.twig');
    }

    #[Route('/copy/{id}', name: 'admin_theme_copy', methods: ['POST'])]
    public function copy(int $id): Response
    {
        $originalTheme = $this->themeRepository->find($id);

        if (!$originalTheme) {
            $this->addFlash('error', 'Theme not found');
            return $this->redirectToRoute('admin_app_theme_list');
        }

        try {
            // Create new theme as copy
            $copiedTheme = new Theme();

            // Generate unique name
            $baseName = $originalTheme->getName() . '-copy';
            $uniqueName = $baseName;
            $counter = 1;

            while ($this->themeRepository->findOneBy(['name' => $uniqueName])) {
                $uniqueName = $baseName . '-' . $counter;
                $counter++;
            }

            $copiedTheme->setName($uniqueName);
            $copiedTheme->setDisplayName($originalTheme->getDisplayName() . ' (Copy)');
            $copiedTheme->setDescription($originalTheme->getDescription());
            $copiedTheme->setAuthor($originalTheme->getAuthor());
            $copiedTheme->setVersion($originalTheme->getVersion());
            $copiedTheme->setCategory($originalTheme->getCategory());
            $copiedTheme->setThumbnailPath($originalTheme->getThumbnailPath());
            $copiedTheme->setConfig($originalTheme->getConfig());
            $copiedTheme->setFiles($originalTheme->getFiles());

            // Copy all customization fields
            $copiedTheme->setPrimaryColor($originalTheme->getPrimaryColor());
            $copiedTheme->setSecondaryColor($originalTheme->getSecondaryColor());
            $copiedTheme->setAccentColor($originalTheme->getAccentColor());
            $copiedTheme->setBackgroundColor($originalTheme->getBackgroundColor());
            $copiedTheme->setTextColor($originalTheme->getTextColor());
            $copiedTheme->setHeadingFont($originalTheme->getHeadingFont());
            $copiedTheme->setBodyFont($originalTheme->getBodyFont());
            $copiedTheme->setFontSize($originalTheme->getFontSize());
            $copiedTheme->setSidebarPosition($originalTheme->getSidebarPosition());
            $copiedTheme->setHeaderStyle($originalTheme->getHeaderStyle());
            $copiedTheme->setContainerWidth($originalTheme->getContainerWidth());
            $copiedTheme->setCustomCss($originalTheme->getCustomCss());

            // Copied themes are not active/default by default
            $copiedTheme->setActive(false);
            $copiedTheme->setDefault(false);

            $this->entityManager->persist($copiedTheme);
            $this->entityManager->flush();

            $this->addFlash('success', sprintf('Theme "%s" copied successfully as "%s"!',
                $originalTheme->getDisplayName(),
                $copiedTheme->getDisplayName()
            ));

        } catch (\Exception $e) {
            $this->addFlash('error', 'Error copying theme: ' . $e->getMessage());
        }

        return $this->redirectToRoute('admin_app_theme_list');
    }
}
