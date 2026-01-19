<?php

namespace App\Controller\Admin;

use App\Entity\SiteSettings;
use App\Repository\SiteSettingsRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/theme', name: 'admin_theme_')]
#[IsGranted('ROLE_ADMIN')]
class ThemeSettingsController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly SiteSettingsRepository $siteSettingsRepo
    ) {
    }

    #[Route('/settings', name: 'settings', methods: ['GET', 'POST'])]
    public function settings(Request $request): Response
    {
        $settings = $this->siteSettingsRepo->findOneBy([]) ?? new SiteSettings();

        if ($request->isMethod('POST')) {
            // Update logo settings
            $settings->setLogoPath($request->request->get('logo_path', 'tabulariumcms.png'));
            $settings->setSiteName($request->request->get('site_name', 'TabulariumCMS'));
            $settings->setLogoSizeMultiplier((int) $request->request->get('logo_size_multiplier', 3));

            // Update color settings
            $settings->setPrimaryColor($request->request->get('primary_color', '#d97706'));
            $settings->setSecondaryColor($request->request->get('secondary_color', '#b45309'));
            $settings->setAccentColor($request->request->get('accent_color', '#92400e'));
            $settings->setNavigationBgColor($request->request->get('navigation_bg_color', '#fef3c7'));
            $settings->setNavigationTextColor($request->request->get('navigation_text_color', '#92400e'));
            $settings->setButtonColor($request->request->get('button_color', '#d97706'));
            $settings->setButtonHoverColor($request->request->get('button_hover_color', '#b45309'));

            // Update breakpoints
            $settings->setBreakpointMobile((int) $request->request->get('breakpoint_mobile', 768));
            $settings->setBreakpointTablet((int) $request->request->get('breakpoint_tablet', 1024));
            $settings->setBreakpointDesktop((int) $request->request->get('breakpoint_desktop', 1280));
            $settings->setBreakpointXl((int) $request->request->get('breakpoint_xl', 1536));

            // Update container settings
            $settings->setContainerMaxWidth((int) $request->request->get('container_max_width', 1280));

            $this->em->persist($settings);
            $this->em->flush();

            $this->addFlash('success', 'Theme settings updated successfully!');

            return $this->redirectToRoute('admin_theme_settings');
        }

        return $this->render('admin/theme/settings.html.twig', [
            'settings' => $settings,
        ]);
    }

    #[Route('/preview', name: 'preview', methods: ['POST'])]
    public function preview(Request $request): Response
    {
        // Return a preview of the theme with the provided settings
        $previewSettings = [
            'logo_path' => $request->request->get('logo_path', 'tabulariumcms.png'),
            'site_name' => $request->request->get('site_name', 'TabulariumCMS'),
            'logo_size_multiplier' => (int) $request->request->get('logo_size_multiplier', 3),
            'primary_color' => $request->request->get('primary_color', '#d97706'),
            'secondary_color' => $request->request->get('secondary_color', '#b45309'),
            'accent_color' => $request->request->get('accent_color', '#92400e'),
            'navigation_bg_color' => $request->request->get('navigation_bg_color', '#fef3c7'),
            'navigation_text_color' => $request->request->get('navigation_text_color', '#92400e'),
            'button_color' => $request->request->get('button_color', '#d97706'),
            'button_hover_color' => $request->request->get('button_hover_color', '#b45309'),
            'breakpoint_mobile' => (int) $request->request->get('breakpoint_mobile', 768),
            'breakpoint_tablet' => (int) $request->request->get('breakpoint_tablet', 1024),
            'breakpoint_desktop' => (int) $request->request->get('breakpoint_desktop', 1280),
            'breakpoint_xl' => (int) $request->request->get('breakpoint_xl', 1536),
            'container_max_width' => (int) $request->request->get('container_max_width', 1280),
        ];

        return $this->json($previewSettings);
    }
}
