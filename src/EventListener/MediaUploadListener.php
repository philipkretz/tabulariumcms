<?php

namespace App\EventListener;

use App\Entity\Media;
use Doctrine\ORM\Event\PrePersistEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\ORM\Events;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Bundle\SecurityBundle\Security;

#[AsEntityListener(event: Events::prePersist, entity: Media::class)]
#[AsEntityListener(event: Events::preUpdate, entity: Media::class)]
class MediaUploadListener
{
    private string $uploadDirectory;

    public function __construct(
        private RequestStack $requestStack,
        private SluggerInterface $slugger,
        private Security $security,
        string $projectDir
    ) {
        $this->uploadDirectory = $projectDir . '/public/uploads/media';

        // Create directory if it doesn't exist
        // phpcs:ignore Security.BadFunctions.FilesystemFunctions -- Internal controlled path
        if (!is_dir($this->uploadDirectory)) {
            // phpcs:ignore Security.BadFunctions.FilesystemFunctions -- Internal controlled path
            mkdir($this->uploadDirectory, 0777, true);
        }
    }

    public function prePersist(Media $media, PrePersistEventArgs $args): void
    {
        $this->handleFileUpload($media);
    }

    public function preUpdate(Media $media, PreUpdateEventArgs $args): void
    {
        $this->handleFileUpload($media);
    }

    private function handleFileUpload(Media $media): void
    {
        $request = $this->requestStack->getCurrentRequest();
        if (!$request) {
            return;
        }

        $file = $request->files->get('file');
        if (!$file instanceof UploadedFile) {
            return;
        }

        // Get original filename and create a safe filename
        // Sanitize the client filename first to prevent path traversal
        $clientFilename = $file->getClientOriginalName();
        $sanitizedClientFilename = \App\Utility\PathValidator::sanitizePath($clientFilename);
        // phpcs:ignore Security.BadFunctions.FilesystemFunctions -- Sanitized input
        $originalFilename = pathinfo($sanitizedClientFilename, PATHINFO_FILENAME);
        $safeFilename = $this->slugger->slug($originalFilename);
        $newFilename = $safeFilename . '-' . uniqid() . '.' . $file->guessExtension();

        // Move the file to the upload directory
        $file->move($this->uploadDirectory, $newFilename);

        // Set media properties
        $media->setFilename($newFilename);
        $media->setOriginalName($file->getClientOriginalName());
        $media->setMimeType($file->getClientMimeType());
        $media->setSize($file->getSize());

        // Auto-detect type based on MIME type
        $mimeType = $file->getClientMimeType();
        if (str_starts_with($mimeType, 'image/')) {
            $media->setType('image');
        } elseif (str_starts_with($mimeType, 'video/')) {
            $media->setType('video');
        } elseif (str_starts_with($mimeType, 'audio/')) {
            $media->setType('audio');
        } elseif (in_array($mimeType, ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'])) {
            $media->setType('document');
        } elseif (in_array($mimeType, ['application/zip', 'application/x-rar-compressed'])) {
            $media->setType('archive');
        }

        // Set uploaded by current user
        $user = $this->security->getUser();
        if ($user && !$media->getUploadedBy()) {
            $media->setUploadedBy($user);
        }
    }
}
