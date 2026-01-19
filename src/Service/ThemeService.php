<?php

namespace App\Service;

use App\Entity\Theme;
use App\Repository\ThemeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class ThemeService
{
    private ThemeRepository $themeRepository;
    private EntityManagerInterface $entityManager;
    private Filesystem $filesystem;
    private ParameterBagInterface $parameterBag;
    private string $themesDir;

    public function __construct(
        ThemeRepository $themeRepository,
        EntityManagerInterface $entityManager,
        Filesystem $filesystem,
        ParameterBagInterface $parameterBag
    ) {
        $this->themeRepository = $themeRepository;
        $this->entityManager = $entityManager;
        $this->filesystem = $filesystem;
        $this->parameterBag = $parameterBag;
        $this->themesDir = $this->parameterBag->get('kernel.project_dir') . '/themes';
    }

    public function getActiveTheme(): ?Theme
    {
        return $this->themeRepository->findActive();
    }

    public function setActiveTheme(Theme $theme): void
    {
        $this->themeRepository->setActive($theme);
    }

    public function getAllThemes(): array
    {
        return $this->themeRepository->findAll();
    }

    public function getActiveThemes(): array
    {
        return $this->themeRepository->findAllActive();
    }

    public function getUserThemes(): array
    {
        return $this->themeRepository->findUserThemes();
    }

    public function createTheme(array $data): Theme
    {
        $theme = new Theme();
        $theme->setName($data['name']);
        $theme->setDisplayName($data['display_name']);
        $theme->setDescription($data['description'] ?? null);
        $theme->setAuthor($data['author']);
        $theme->setVersion($data['version'] ?? '1.0.0');
        $theme->setCategory('user');
        $theme->setConfig($data['config'] ?? []);

        $themeDir = $this->themesDir . '/' . $theme->getName();
        $this->filesystem->mkdir($themeDir);

        $this->createThemeStructure($themeDir, $data);

        $this->themeRepository->save($theme, true);

        return $theme;
    }

    public function updateTheme(Theme $theme, array $data): void
    {
        $theme->setDisplayName($data['display_name']);
        $theme->setDescription($data['description'] ?? null);
        $theme->setAuthor($data['author']);
        $theme->setVersion($data['version'] ?? '1.0.0');
        $theme->setConfig($data['config'] ?? []);

        $this->themeRepository->save($theme, true);
    }

    public function deleteTheme(Theme $theme): void
    {
        $themeDir = $theme->getDirectoryPath();
        if ($this->filesystem->exists($themeDir)) {
            $this->filesystem->remove($themeDir);
        }

        $this->themeRepository->remove($theme, true);
    }

    private function createThemeStructure(string $baseDir, array $structure): void
    {
        foreach ($structure as $dir => $contents) {
            $fullDir = $baseDir . '/' . $dir;
            $this->filesystem->mkdir($fullDir);
        }
    }

    public function saveThemeFile(Theme $theme, string $path, string $content): void
    {
        $filePath = $theme->getDirectoryPath() . '/' . $path;
        $this->filesystem->dumpFile($filePath, $content);

        // Update theme files record
        $theme->setFile($path, [
            'path' => $path,
            'size' => strlen($content),
            'modified' => (new \DateTime())->format('c')
        ]);

        $this->themeRepository->save($theme, true);
    }

    public function getThemeFileContent(Theme $theme, string $path): ?string
    {
        // Validate and sanitize the path
        $sanitizedPath = \App\Utility\PathValidator::validateAndSanitizePath($path);
        if ($sanitizedPath === false) {
            return null;
        }

        $themeDir = $theme->getDirectoryPath();
        $filePath = $themeDir . '/' . $sanitizedPath;
        
        // Ensure the file is within the theme directory
        if (!\App\Utility\PathValidator::isWithinDirectory($sanitizedPath, $themeDir)) {
            return null;
        }
        
        // Ensure file exists and is actually a file
        // phpcs:ignore Security.BadFunctions.FilesystemFunctions -- Path validated with PathValidator::isWithinDirectory
        if (!$this->filesystem->exists($filePath) || !is_file($filePath)) {
            return null;
        }

        // phpcs:ignore Security.BadFunctions.FilesystemFunctions -- Path validated with PathValidator::isWithinDirectory
        return file_get_contents($filePath);
    }

    public function initializeDefaultThemes(): void
    {
        $defaultThemes = [
            'modern' => [
                'display_name' => 'Modern',
                'description' => 'Clean, modern design with responsive layout',
                'author' => 'TabulariumCMS',
                'version' => '1.0.0',
                'config' => [
                    'colors' => [
                        'primary' => '#3b82f6',
                        'secondary' => '#6b7280',
                        'accent' => '#10b981',
                    ],
                    'fonts' => [
                        'heading' => 'Inter',
                        'body' => 'Inter',
                    ],
                    'layout' => [
                        'sidebar_position' => 'left',
                        'header_style' => 'floating',
                    ]
                ]
            ],
            'classic' => [
                'display_name' => 'Classic',
                'description' => 'Traditional blog design with sidebar',
                'author' => 'TabulariumCMS',
                'version' => '1.0.0',
                'config' => [
                    'colors' => [
                        'primary' => '#2563eb',
                        'secondary' => '#64748b',
                        'accent' => '#dc2626',
                    ],
                    'fonts' => [
                        'heading' => 'Georgia',
                        'body' => 'Arial',
                    ],
                    'layout' => [
                        'sidebar_position' => 'right',
                        'header_style' => 'fixed',
                    ]
                ]
            ],
            'minimal' => [
                'display_name' => 'Minimal',
                'description' => 'Minimal design focused on content',
                'author' => 'TabulariumCMS',
                'version' => '1.0.0',
                'config' => [
                    'colors' => [
                        'primary' => '#000000',
                        'secondary' => '#666666',
                        'accent' => '#333333',
                    ],
                    'fonts' => [
                        'heading' => 'Helvetica',
                        'body' => 'Helvetica',
                    ],
                    'layout' => [
                        'sidebar_position' => 'none',
                        'header_style' => 'minimal',
                    ]
                ]
            ]
        ];

        foreach ($defaultThemes as $themeName => $themeData) {
            $existingTheme = $this->themeRepository->findOneBy(['name' => $themeName]);
            if (!$existingTheme) {
                $theme = $this->createTheme([
                    'name' => $themeName,
                    'display_name' => $themeData['display_name'],
                    'description' => $themeData['description'],
                    'author' => $themeData['author'],
                    'version' => $themeData['version'],
                    'config' => $themeData['config'],
                    'category' => 'default'
                ]);

                $this->themeRepository->save($theme, true);
                $this->createDefaultThemeFiles($theme);
            }
        }
    }

    private function createDefaultThemeFiles(Theme $theme): void
    {
        $themeDir = $theme->getDirectoryPath();
        
        $directories = ['templates', 'assets/css', 'assets/js', 'assets/images'];
        foreach ($directories as $dir) {
            $this->filesystem->mkdir($themeDir . '/' . $dir);
        }

        $baseTemplate = $this->generateBaseTemplate($theme);
        $this->filesystem->dumpFile($themeDir . '/templates/base.html.twig', $baseTemplate);

        $css = $this->generateThemeCSS($theme);
        $this->filesystem->dumpFile($themeDir . '/assets/css/theme.css', $css);

        $manifest = [
            'name' => $theme->getName(),
            'version' => $theme->getVersion(),
            'author' => $theme->getAuthor(),
            'description' => $theme->getDescription(),
            'config' => $theme->getConfig(),
        ];
        $this->filesystem->dumpFile($themeDir . '/manifest.json', json_encode($manifest, JSON_PRETTY_PRINT));
    }

    private function generateBaseTemplate(Theme $theme): string
    {
        return "<!DOCTYPE html>
<html>
<head>
    <meta charset=\"UTF-8\">
    <meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\">
    <title>{% block title %}" . $theme->getDisplayName() . "{% endblock %}</title>
    <link rel=\"stylesheet\" href=\"{{ asset('themes/" . $theme->getName() . "/assets/css/theme.css') }}\">
    {% block stylesheets %}{% endblock %}
</head>
<body class=\"theme-" . $theme->getName() . "\">
    {% block body %}{% endblock %}
    {% block javascripts %}{% endblock %}
</body>
</html>";
    }

    private function generateThemeCSS(Theme $theme): string
    {
        // Use theme customization fields first, fallback to config
        $config = $theme->getConfig();
        $primaryColor = $theme->getPrimaryColor() ?? $config['colors']['primary'] ?? '#3b82f6';
        $secondaryColor = $theme->getSecondaryColor() ?? $config['colors']['secondary'] ?? '#6b7280';
        $accentColor = $theme->getAccentColor() ?? $config['colors']['accent'] ?? '#10b981';
        $backgroundColor = $theme->getBackgroundColor() ?? '#ffffff';
        $textColor = $theme->getTextColor() ?? '#1f2937';
        $headingFont = $theme->getHeadingFont() ?? $config['fonts']['heading'] ?? 'Roboto, sans-serif';
        $bodyFont = $theme->getBodyFont() ?? $config['fonts']['body'] ?? 'Roboto, sans-serif';
        $fontSize = $theme->getFontSize() ?? '16px';
        $containerWidth = $theme->getContainerWidth() ?? '1140px';

        $css = "/* Theme: " . $theme->getDisplayName() . " */\n";
        $css .= ":root {\n";
        $css .= "    --primary-color: {$primaryColor};\n";
        $css .= "    --secondary-color: {$secondaryColor};\n";
        $css .= "    --accent-color: {$accentColor};\n";
        $css .= "    --background-color: {$backgroundColor};\n";
        $css .= "    --text-color: {$textColor};\n";
        $css .= "    --heading-font: {$headingFont};\n";
        $css .= "    --body-font: {$bodyFont};\n";
        $css .= "    --font-size: {$fontSize};\n";
        $css .= "    --container-width: {$containerWidth};\n";
        $css .= "}\n\n";

        $css .= "body {\n";
        $css .= "    font-family: var(--body-font);\n";
        $css .= "    font-size: var(--font-size);\n";
        $css .= "    color: var(--text-color);\n";
        $css .= "    background: var(--background-color);\n";
        $css .= "    margin: 0;\n";
        $css .= "    padding: 0;\n";
        $css .= "    line-height: 1.6;\n";
        $css .= "}\n\n";

        $css .= "h1, h2, h3, h4, h5, h6 {\n";
        $css .= "    font-family: var(--heading-font);\n";
        $css .= "    color: var(--text-color);\n";
        $css .= "}\n\n";

        $css .= ".container {\n";
        $css .= "    max-width: var(--container-width);\n";
        $css .= "    margin: 0 auto;\n";
        $css .= "    padding: 0 20px;\n";
        $css .= "}\n\n";

        $css .= ".btn-primary {\n";
        $css .= "    background-color: var(--primary-color);\n";
        $css .= "    border-color: var(--primary-color);\n";
        $css .= "    color: #fff;\n";
        $css .= "}\n\n";

        $css .= ".btn-secondary {\n";
        $css .= "    background-color: var(--secondary-color);\n";
        $css .= "    border-color: var(--secondary-color);\n";
        $css .= "    color: #fff;\n";
        $css .= "}\n\n";

        $css .= "a {\n";
        $css .= "    color: var(--accent-color);\n";
        $css .= "    text-decoration: none;\n";
        $css .= "}\n\n";

        $css .= "a:hover {\n";
        $css .= "    opacity: 0.8;\n";
        $css .= "}\n\n";

        // Header styles based on headerStyle
        if ($headerStyle = $theme->getHeaderStyle()) {
            $css .= "/* Header Style: {$headerStyle} */\n";
            if ($headerStyle === 'fixed') {
                $css .= ".site-header { position: fixed; top: 0; width: 100%; z-index: 1000; }\n";
                $css .= "body { padding-top: 80px; }\n";
            } elseif ($headerStyle === 'sticky') {
                $css .= ".site-header { position: sticky; top: 0; z-index: 1000; }\n";
            }
            $css .= "\n";
        }

        // Sidebar styles based on sidebarPosition
        if ($sidebarPosition = $theme->getSidebarPosition()) {
            $css .= "/* Sidebar Position: {$sidebarPosition} */\n";
            if ($sidebarPosition === 'left') {
                $css .= ".main-layout { display: grid; grid-template-columns: 250px 1fr; gap: 30px; }\n";
                $css .= ".sidebar { order: -1; }\n";
            } elseif ($sidebarPosition === 'right') {
                $css .= ".main-layout { display: grid; grid-template-columns: 1fr 250px; gap: 30px; }\n";
            } elseif ($sidebarPosition === 'none') {
                $css .= ".sidebar { display: none; }\n";
            }
            $css .= "\n";
        }

        // Add custom CSS if provided
        if ($customCss = $theme->getCustomCss()) {
            $css .= "/* Custom CSS */\n";
            $css .= $customCss . "\n";
        }

        return $css;
    }
}