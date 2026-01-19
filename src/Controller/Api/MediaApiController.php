<?php

namespace App\Controller\Api;

use App\Entity\Media;
use App\Repository\MediaRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\String\Slugger\SluggerInterface;

#[Route("/api/media", name: "api_media_")]
class MediaApiController extends AbstractController
{
    private string $uploadDirectory;

    public function __construct()
    {
        $this->uploadDirectory = $_ENV["KERNEL_PROJECT_DIR"] . "/public/uploads/media";
    }

    #[Route("", name: "list", methods: ["GET"])]
    public function list(MediaRepository $mediaRepository): JsonResponse
    {
        $media = $mediaRepository->findBy([], ["createdAt" => "DESC"], 100);

        // phpcs:ignore Security.BadFunctions.CallbackFunctions -- Safe inline closure
        $data = array_map(function($item) {
            return [
                "id" => $item->getId(),
                "src" => "/uploads/media/" . $item->getFilename(),
                "type" => $item->getType(),
                "name" => $item->getOriginalName(),
                "filename" => $item->getFilename(),
                "height" => 350,
                "width" => 250,
            ];
        }, $media);

        return $this->json($data);
    }

    #[Route("/upload", name: "upload", methods: ["POST"])]
    public function upload(
        Request $request,
        EntityManagerInterface $em,
        SluggerInterface $slugger
    ): JsonResponse {
        $file = $request->files->get("files");

        if (!$file instanceof UploadedFile) {
            return $this->json(["error" => "No file uploaded"], 400);
        }

        // Validate file
        $allowedMimeTypes = [
            "image/jpeg", "image/jpg", "image/png", "image/gif", "image/webp", "image/svg+xml",
            "video/mp4", "video/webm", "video/ogg",
            "audio/mpeg", "audio/ogg", "audio/wav",
            "application/pdf",
        ];

        if (!in_array($file->getMimeType(), $allowedMimeTypes)) {
            return $this->json(["error" => "File type not allowed"], 400);
        }

        // Create safe filename
        // Sanitize the client filename first to prevent path traversal
        $clientFilename = $file->getClientOriginalName();
        $sanitizedClientFilename = \App\Utility\PathValidator::sanitizePath($clientFilename);
        // phpcs:ignore Security.BadFunctions.FilesystemFunctions -- Sanitized input
        $originalFilename = pathinfo($sanitizedClientFilename, PATHINFO_FILENAME);
        $safeFilename = $slugger->slug($originalFilename);
        $newFilename = $safeFilename . "-" . uniqid() . "." . $file->guessExtension();

        // Move file
        try {
            $file->move($this->uploadDirectory, $newFilename);
        } catch (\Exception $e) {
            return $this->json(["error" => "Failed to upload file"], 500);
        }

        // Create media entity
        $media = new Media();
        $media->setFilename($newFilename);
        $media->setOriginalName($file->getClientOriginalName());
        $media->setMimeType($file->getMimeType());
        $media->setSize($file->getSize());

        // Auto-detect type
        $mimeType = $file->getMimeType();
        if (str_starts_with($mimeType, "image/")) {
            $media->setType("image");
        } elseif (str_starts_with($mimeType, "video/")) {
            $media->setType("video");
        } elseif (str_starts_with($mimeType, "audio/")) {
            $media->setType("audio");
        } else {
            $media->setType("document");
        }

        // Set uploader if user is logged in
        if ($this->getUser()) {
            $media->setUploadedBy($this->getUser());
        }

        $em->persist($media);
        $em->flush();

        return $this->json([
            "data" => [
                [
                    "id" => $media->getId(),
                    "src" => "/uploads/media/" . $newFilename,
                    "type" => $media->getType(),
                    "name" => $media->getOriginalName(),
                ]
            ]
        ]);
    }
}
