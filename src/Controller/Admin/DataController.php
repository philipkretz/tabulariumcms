<?php

namespace App\Controller\Admin;

use App\Service\DataExportService;
use App\Service\DataImportService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route("/admin/data")]
#[IsGranted("ROLE_SUPER_ADMIN")]
class DataController extends AbstractController
{
    public function __construct(
        private DataExportService $exportService,
        private DataImportService $importService,
        private EntityManagerInterface $entityManager
    ) {}

    #[Route("", name: "admin_data_management")]
    public function index(): Response
    {
        return $this->render("admin/data/index.html.twig");
    }

    #[Route("/export", name: "admin_data_export", methods: ["POST"])]
    public function export(Request $request): Response
    {
        $format = $request->request->get("format", "json");
        
        try {
            $filepath = $this->exportService->exportToFile($format);
            
            $response = new BinaryFileResponse($filepath);
            $response->setContentDisposition(
                ResponseHeaderBag::DISPOSITION_ATTACHMENT,
                // phpcs:ignore Security.BadFunctions.FilesystemFunctions -- Safe: filepath from exportService, basename sanitized with PathValidator
                \App\Utility\PathValidator::sanitizePath(basename($filepath))
            );
            $response->deleteFileAfterSend(true);
            
            return $response;
        } catch (\Exception $e) {
            $this->addFlash("error", "Export failed: " . $e->getMessage());
            return $this->redirectToRoute("admin_data_management");
        }
    }

    #[Route("/import", name: "admin_data_import", methods: ["POST"])]
    public function import(Request $request): Response
    {
        /** @var UploadedFile $file */
        $file = $request->files->get("import_file");
        
        if (!$file) {
            $this->addFlash("error", "Please select a file to import");
            return $this->redirectToRoute("admin_data_management");
        }
        
        if (!$file->isValid()) {
            $this->addFlash("error", "Invalid file upload");
            return $this->redirectToRoute("admin_data_management");
        }
        
        try {
            $result = $this->importService->importFromFile($file->getPathname());
            
            $importedCount = array_sum($result["imported"]);
            $errorCount = count($result["errors"]);
            
            if ($importedCount > 0) {
                $this->addFlash("success", sprintf("Successfully imported %d entities", $importedCount));
            }
            
            if ($errorCount > 0) {
                $this->addFlash("warning", sprintf("%d errors occurred during import", $errorCount));
            }
            
            return $this->redirectToRoute("admin_data_management");
            
        } catch (\Exception $e) {
            $this->addFlash("error", "Import failed: " . $e->getMessage());
            return $this->redirectToRoute("admin_data_management");
        }
    }

    #[Route("/import/preview", name: "admin_data_import_preview", methods: ["POST"])]
    public function previewImport(Request $request): Response
    {
        /** @var UploadedFile $file */
        $file = $request->files->get("preview_file");
        
        if (!$file || !$file->isValid()) {
            return $this->json(["error" => "Invalid file"], 400);
        }
        
        try {
            $preview = $this->importService->previewImport($file->getPathname());
            return $this->json($preview);
        } catch (\Exception $e) {
            return $this->json(["error" => $e->getMessage()], 500);
        }
    }
}
