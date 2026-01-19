<?php

namespace App\Controller\Admin;

use App\Entity\Theme;
use App\Repository\ThemeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Sonata\AdminBundle\Controller\CRUDController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\String\Slugger\SluggerInterface;

final class ThemeAdminController extends CRUDController
{
    public function __construct(
        private ThemeRepository $themeRepository,
        private EntityManagerInterface $entityManager,
        private SluggerInterface $slugger
    ) {
    }

    public function previewAction(): Response
    {
        $object = $this->admin->getSubject();

        if (!$object) {
            throw $this->createNotFoundException('Theme not found');
        }

        return $this->render('admin/theme/preview.html.twig', [
            'theme' => $object,
            'admin' => $this->admin,
            'base_template' => $this->getBaseTemplate(),
        ]);
    }

    public function downloadThemeAction(): Response
    {
        $object = $this->admin->getSubject();

        if (!$object instanceof Theme) {
            throw $this->createNotFoundException('Theme not found');
        }

        // Prepare theme data for export
        $exportData = [
            'name' => $object->getName(),
            'displayName' => $object->getDisplayName(),
            'description' => $object->getDescription(),
            'author' => $object->getAuthor(),
            'version' => $object->getVersion(),
            'category' => $object->getCategory(),
            'thumbnailPath' => $object->getThumbnailPath(),
            'config' => $object->getConfig(),
            'files' => $object->getFiles(),

            // Theme customization fields
            'primaryColor' => $object->getPrimaryColor(),
            'secondaryColor' => $object->getSecondaryColor(),
            'accentColor' => $object->getAccentColor(),
            'backgroundColor' => $object->getBackgroundColor(),
            'textColor' => $object->getTextColor(),
            'headingFont' => $object->getHeadingFont(),
            'bodyFont' => $object->getBodyFont(),
            'fontSize' => $object->getFontSize(),
            'sidebarPosition' => $object->getSidebarPosition(),
            'headerStyle' => $object->getHeaderStyle(),
            'containerWidth' => $object->getContainerWidth(),
            'customCss' => $object->getCustomCss(),

            // Export metadata
            'exportedAt' => (new \DateTimeImmutable())->format('Y-m-d H:i:s'),
            'exportVersion' => '1.0'
        ];

        $json = json_encode($exportData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        $filename = $this->slugger->slug($object->getName())->lower() . '-theme-export-' . date('Y-m-d') . '.json';

        return new Response($json, 200, [
            'Content-Type' => 'application/json',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"'
        ]);
    }

    public function copyAction(): RedirectResponse
    {
        $object = $this->admin->getSubject();

        if (!$object instanceof Theme) {
            throw $this->createNotFoundException('Theme not found');
        }

        try {
            // Create new theme as copy
            $copiedTheme = new Theme();

            // Generate unique name
            $baseName = $object->getName() . '-copy';
            $uniqueName = $baseName;
            $counter = 1;

            while ($this->themeRepository->findOneBy(['name' => $uniqueName])) {
                $uniqueName = $baseName . '-' . $counter;
                $counter++;
            }

            $copiedTheme->setName($uniqueName);
            $copiedTheme->setDisplayName($object->getDisplayName() . ' (Copy)');
            $copiedTheme->setDescription($object->getDescription());
            $copiedTheme->setAuthor($object->getAuthor());
            $copiedTheme->setVersion($object->getVersion());
            $copiedTheme->setCategory($object->getCategory());
            $copiedTheme->setThumbnailPath($object->getThumbnailPath());
            $copiedTheme->setConfig($object->getConfig());
            $copiedTheme->setFiles($object->getFiles());

            // Copy all customization fields
            $copiedTheme->setPrimaryColor($object->getPrimaryColor());
            $copiedTheme->setSecondaryColor($object->getSecondaryColor());
            $copiedTheme->setAccentColor($object->getAccentColor());
            $copiedTheme->setBackgroundColor($object->getBackgroundColor());
            $copiedTheme->setTextColor($object->getTextColor());
            $copiedTheme->setHeadingFont($object->getHeadingFont());
            $copiedTheme->setBodyFont($object->getBodyFont());
            $copiedTheme->setFontSize($object->getFontSize());
            $copiedTheme->setSidebarPosition($object->getSidebarPosition());
            $copiedTheme->setHeaderStyle($object->getHeaderStyle());
            $copiedTheme->setContainerWidth($object->getContainerWidth());
            $copiedTheme->setCustomCss($object->getCustomCss());

            // Copied themes are not active/default by default
            $copiedTheme->setActive(false);
            $copiedTheme->setDefault(false);

            $this->entityManager->persist($copiedTheme);
            $this->entityManager->flush();

            $this->addFlash(
                'sonata_flash_success',
                sprintf('Theme "%s" copied successfully as "%s"!',
                    $object->getDisplayName(),
                    $copiedTheme->getDisplayName()
                )
            );

        } catch (\Exception $e) {
            $this->addFlash(
                'sonata_flash_error',
                'Error copying theme: ' . $e->getMessage()
            );
        }

        return $this->redirectToList();
    }
}
