<?php

namespace App\Controller\Admin;

use App\Repository\EmailTemplateRepository;
use App\Repository\SiteSettingsRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin/email-settings')]
class EmailSettingsController extends AbstractController
{
    public function __construct(
        private EmailTemplateRepository $emailTemplateRepository,
        private SiteSettingsRepository $siteSettingsRepository,
        private EntityManagerInterface $entityManager
    ) {
    }

    #[Route('', name: 'admin_email_settings')]
    public function index(): Response
    {
        $emailTemplates = $this->emailTemplateRepository->findAll();
        $settings = $this->siteSettingsRepository->getSettings();

        return $this->render('admin/email_settings/index.html.twig', [
            'emailTemplates' => $emailTemplates,
            'settings' => $settings
        ]);
    }

    #[Route('/toggle-all', name: 'admin_email_settings_toggle_all', methods: ['POST'])]
    public function toggleAll(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $enabled = $data['enabled'] ?? false;

        $templates = $this->emailTemplateRepository->findAll();
        $count = 0;

        foreach ($templates as $template) {
            $template->setIsActive($enabled);
            $count++;
        }

        $this->entityManager->flush();

        return new JsonResponse([
            'success' => true,
            'message' => $enabled
                ? "Activated {$count} email templates"
                : "Deactivated {$count} email templates",
            'count' => $count,
            'enabled' => $enabled
        ]);
    }

    #[Route('/toggle-template/{id}', name: 'admin_email_settings_toggle_template', methods: ['POST'])]
    public function toggleTemplate(int $id): JsonResponse
    {
        $template = $this->emailTemplateRepository->find($id);

        if (!$template) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Template not found'
            ], Response::HTTP_NOT_FOUND);
        }

        $template->setIsActive(!$template->isActive());
        $this->entityManager->flush();

        return new JsonResponse([
            'success' => true,
            'message' => $template->isActive()
                ? "Template '{$template->getName()}' activated"
                : "Template '{$template->getName()}' deactivated",
            'template_id' => $template->getId(),
            'is_active' => $template->isActive()
        ]);
    }

    #[Route('/toggle-group', name: 'admin_email_settings_toggle_group', methods: ['POST'])]
    public function toggleGroup(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $group = $data['group'] ?? null;
        $enabled = $data['enabled'] ?? false;

        if (!$group) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Group is required'
            ], Response::HTTP_BAD_REQUEST);
        }

        // Get templates by group (you can define groups by slug prefix or add a group field)
        $templates = $this->emailTemplateRepository->findAll();
        $count = 0;

        foreach ($templates as $template) {
            // Group by slug prefix (e.g., "order-", "newsletter-", "user-")
            if (str_starts_with($template->getSlug(), $group . '-')) {
                $template->setIsActive($enabled);
                $count++;
            }
        }

        $this->entityManager->flush();

        return new JsonResponse([
            'success' => true,
            'message' => $enabled
                ? "Activated {$count} email templates in group '{$group}'"
                : "Deactivated {$count} email templates in group '{$group}'",
            'count' => $count,
            'group' => $group,
            'enabled' => $enabled
        ]);
    }

    #[Route('/statistics', name: 'admin_email_settings_statistics', methods: ['GET'])]
    public function statistics(): JsonResponse
    {
        $templates = $this->emailTemplateRepository->findAll();

        $stats = [
            'total' => count($templates),
            'active' => 0,
            'inactive' => 0,
            'groups' => []
        ];

        foreach ($templates as $template) {
            if ($template->isActive()) {
                $stats['active']++;
            } else {
                $stats['inactive']++;
            }

            // Group by prefix
            $parts = explode('-', $template->getSlug());
            $group = $parts[0] ?? 'other';

            if (!isset($stats['groups'][$group])) {
                $stats['groups'][$group] = ['total' => 0, 'active' => 0, 'inactive' => 0];
            }

            $stats['groups'][$group]['total']++;
            if ($template->isActive()) {
                $stats['groups'][$group]['active']++;
            } else {
                $stats['groups'][$group]['inactive']++;
            }
        }

        return new JsonResponse($stats);
    }
}
