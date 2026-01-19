<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use App\Entity\Theme;
use App\Service\ThemeService;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

#[IsGranted('ROLE_ADMIN')]
class ThemeEditorController extends AbstractController
{
    private ThemeService $themeService;
    private Filesystem $filesystem;
    private ParameterBagInterface $parameterBag;

    public function __construct(
        ThemeService $themeService,
        Filesystem $filesystem,
        ParameterBagInterface $parameterBag
    ) {
        $this->themeService = $themeService;
        $this->filesystem = $filesystem;
        $this->parameterBag = $parameterBag;
    }

    #[Route('/admin/themes', name: 'admin_themes')]
    public function index(): Response
    {
        $themes = $this->themeService->getAllThemes();
        $activeTheme = $this->themeService->getActiveTheme();

        return $this->render('admin/themes/index.html.twig', [
            'themes' => $themes,
            'activeTheme' => $activeTheme,
        ]);
    }

    #[Route('/admin/themes/{id}/activate', name: 'admin_theme_activate')]
    public function activate(Theme $theme): Response
    {
        $this->themeService->setActiveTheme($theme);

        $this->addFlash('success', 'Theme activated successfully.');

        return $this->redirectToRoute('admin_themes');
    }

    #[Route('/admin/themes/new', name: 'admin_theme_new')]
    public function new(Request $request): Response
    {
        if ($request->isMethod('POST')) {
            $themeData = [
                'name' => $request->request->get('name'),
                'display_name' => $request->request->get('display_name'),
                'description' => $request->request->get('description'),
                'author' => $request->request->get('author'),
                'version' => $request->request->get('version'),
                'config' => [
                    'colors' => [
                        'primary' => $request->request->get('primary_color'),
                        'secondary' => $request->request->get('secondary_color'),
                        'accent' => $request->request->get('accent_color'),
                    ],
                    'fonts' => [
                        'heading' => $request->request->get('heading_font'),
                        'body' => $request->request->get('body_font'),
                    ],
                    'layout' => [
                        'sidebar_position' => $request->request->get('sidebar_position'),
                        'header_style' => $request->request->get('header_style'),
                    ]
                ]
            ];

            if ($request->files->get('thumbnail')) {
                $themeData['thumbnail'] = $request->files->get('thumbnail');
            }

            $theme = $this->themeService->createTheme($themeData);

            $this->addFlash('success', 'Theme created successfully.');

            return $this->redirectToRoute('admin_themes');
        }

        return $this->render('admin/themes/new.html.twig');
    }

    #[Route('/admin/themes/{id}/edit', name: 'admin_theme_edit')]
    public function edit(Theme $theme, Request $request): Response
    {
        if ($request->isMethod('POST')) {
            $themeData = [
                'display_name' => $request->request->get('display_name'),
                'description' => $request->request->get('description'),
                'author' => $request->request->get('author'),
                'version' => $request->request->get('version'),
                'config' => [
                    'colors' => [
                        'primary' => $request->request->get('primary_color'),
                        'secondary' => $request->request->get('secondary_color'),
                        'accent' => $request->request->get('accent_color'),
                    ],
                    'fonts' => [
                        'heading' => $request->request->get('heading_font'),
                        'body' => $request->request->get('body_font'),
                    ],
                    'layout' => [
                        'sidebar_position' => $request->request->get('sidebar_position'),
                        'header_style' => $request->request->get('header_style'),
                    ]
                ]
            ];

            $this->themeService->updateTheme($theme, $themeData);

            $this->addFlash('success', 'Theme updated successfully.');

            return $this->redirectToRoute('admin_themes');
        }

        return $this->render('admin/themes/edit.html.twig', [
            'theme' => $theme,
        ]);
    }

    #[Route('/admin/themes/{id}/editor', name: 'admin_theme_editor')]
    public function editor(Theme $theme, Request $request): Response
    {
        if ($request->isMethod('POST')) {
            $content = $request->request->get('content');
            $file = $request->request->get('file');
            
            $this->themeService->saveThemeFile($theme, $file, $content);
            
            if ($request->isXmlHttpRequest()) {
                return new JsonResponse(['success' => true]);
            }
        }

        return $this->render('admin/themes/editor.html.twig', [
            'theme' => $theme,
        ]);
    }

    #[Route('/admin/themes/{id}/files', name: 'admin_theme_files')]
    public function files(Theme $theme): Response
    {
        $themeDir = $theme->getDirectoryPath();
        $files = [];

        if ($this->filesystem->exists($themeDir)) {
            $iterator = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($themeDir),
                \RecursiveIteratorIterator::SELF_FIRST
            );

            foreach ($iterator as $fileInfo) {
                if ($fileInfo->isFile()) {
                    $relativePath = str_replace($themeDir . '/', '', $fileInfo->getPathname());
                    $files[] = [
                        'name' => $fileInfo->getFilename(),
                        'path' => $relativePath,
                        'size' => $fileInfo->getSize(),
                        'modified' => date('Y-m-d H:i:s', $fileInfo->getMTime()),
                    ];
                }
            }
        }

        return $this->render('admin/themes/files.html.twig', [
            'theme' => $theme,
            'files' => $files,
        ]);
    }

    #[Route('/admin/themes/{id}/files/{file}', name: 'admin_theme_file')]
    public function themeFile(Theme $theme, string $file): Response
    {
        // Validate and sanitize the file path
        $sanitizedFile = \App\Utility\PathValidator::validateAndSanitizePath($file);
        if ($sanitizedFile === false) {
            throw $this->createAccessDeniedException('Invalid file path');
        }

        $filePath = $this->themeService->getThemeFileContent($theme, $sanitizedFile);
        
        if (!$filePath) {
            throw $this->createNotFoundException('Theme file not found');
        }

        // Additional security check: ensure file is within theme directory
        $themeDir = $theme->getDirectoryPath();
        if (!\App\Utility\PathValidator::isWithinDirectory($sanitizedFile, $themeDir)) {
            throw $this->createAccessDeniedException('Access denied');
        }

        // phpcs:ignore Security.BadFunctions.FilesystemFunctions -- Path validated with PathValidator::isWithinDirectory
        $content = file_get_contents($filePath);

        return $this->render('admin/themes/file_content.html.twig', [
            'theme' => $theme,
            'file' => $sanitizedFile,
            'content' => $content,
        ]);
    }

    #[Route('/admin/themes/{id}/delete', name: 'admin_theme_delete')]
    public function delete(Theme $theme, Request $request): Response
    {
        if ($theme->getCategory() === 'default') {
            throw $this->createAccessDeniedException('Cannot delete default themes');
        }

        $this->themeService->deleteTheme($theme);

        $this->addFlash('success', 'Theme deleted successfully.');

        return $this->redirectToRoute('admin_themes');
    }

    #[Route('/admin/themes/{id}/download', name: 'admin_theme_download')]
    public function download(Theme $theme): Response
    {
        $themeDir = $theme->getDirectoryPath();
        $zipFile = sys_get_temp_dir() . '/' . $theme->getName() . '.zip';

        $zip = new \ZipArchive();
        $zip->open($zipFile, \ZipArchive::CREATE | \ZipArchive::OVERWRITE);

        if ($this->filesystem->exists($themeDir)) {
            $iterator = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($themeDir),
                \RecursiveIteratorIterator::SELF_FIRST
            );

            foreach ($iterator as $fileInfo) {
                if ($fileInfo->isFile()) {
                    $relativePath = str_replace($themeDir . '/', '', $fileInfo->getPathname());
                    $zip->addFile($fileInfo->getPathname(), $relativePath);
                }
            }
        }

        $zip->close();

        // phpcs:ignore Security.BadFunctions.FilesystemFunctions -- Using controlled temp directory
        $response = new Response(file_get_contents($zipFile));
        $response->headers->set('Content-Type', 'application/zip');
        $response->headers->set('Content-Disposition', 'attachment; filename="' . $theme->getName() . '.zip"');

        // phpcs:ignore Security.BadFunctions.FilesystemFunctions -- Using controlled temp directory
        unlink($zipFile);

        return $response;
    }
}