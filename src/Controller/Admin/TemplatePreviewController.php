<?php

namespace App\Controller\Admin;

use App\Entity\Template;
use App\Repository\TemplateRepository;
use App\Service\TemplateService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/app/template')]
#[IsGranted('ROLE_ADMIN')]
class TemplatePreviewController extends AbstractController
{
    public function __construct(
        private TemplateRepository $templateRepository,
        private ?TemplateService $templateService = null
    ) {}

    #[Route('/{id}/preview', name: 'admin_template_preview', methods: ['GET'])]
    public function preview(int $id): Response
    {
        $template = $this->templateRepository->find($id);

        if (!$template) {
            throw $this->createNotFoundException('Template not found');
        }

        // Process bracket functions if TemplateService is available
        $processedContent = $template->getContent();
        if ($this->templateService) {
            try {
                $processedContent = $this->templateService->processBracketFunctions($processedContent);
            } catch (\Exception $e) {
                $processedContent = '<div class="alert alert-danger">Error processing template: ' . htmlspecialchars($e->getMessage()) . '</div>' . $processedContent;
            }
        }

        return $this->render('admin/template/preview.html.twig', [
            'template' => $template,
            'processedContent' => $processedContent,
        ]);
    }
}
