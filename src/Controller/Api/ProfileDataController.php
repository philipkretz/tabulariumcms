<?php

namespace App\Controller\Api;

use App\Entity\User;
use App\Service\ImageProcessor;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api/profile')]
#[IsGranted('ROLE_USER')]
class ProfileDataController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private ImageProcessor $imageProcessor
    ) {}

    /**
     * Get current user's profile data for editing
     */
    #[Route('/data', name: 'api_profile_data', methods: ['GET'])]
    public function getProfileData(): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();
        $profile = $user->getProfile();

        // Parse interests from comma-separated string
        $interests = [];
        if ($profile && $profile->getInterests()) {
            // phpcs:ignore Security.BadFunctions.CallbackFunctions -- Safe hardcoded callback 'trim'
            $interests = array_map('trim', explode(',', $profile->getInterests()));
        }

        // Get media items (you may need to adjust this based on your UserMedia structure)
        $mediaItems = [];
        if ($profile) {
            foreach ($profile->getMedia() as $media) {
                $mediaItems[] = [
                    'url' => '/uploads/profiles/' . $user->getId() . '/media/' . $media->getFilename(),
                    'type' => $media->getType() === 'video' ? 'video' : 'image',
                    'id' => $media->getId()
                ];
            }
        }

        return $this->json([
            'bio' => $profile?->getBio() ?? '',
            'tagline' => $profile?->getTagline() ?? '',
            'location' => $profile?->getLocation() ?? '',
            'website' => $profile?->getWebsite() ?? '',
            'interests' => $interests,
            'media' => $mediaItems
        ]);
    }

    /**
     * Upload media to profile gallery
     */
    #[Route('/upload-media', name: 'api_profile_upload_media', methods: ['POST'])]
    public function uploadMedia(Request $request): JsonResponse
    {
        try {
            $uploadedFile = $request->files->get('media');

            if (!$uploadedFile) {
                return $this->json(['success' => false, 'error' => 'No file uploaded'], 400);
            }

            // Validate file (max 20MB)
            $this->imageProcessor->validateImageFile($uploadedFile, 20 * 1024 * 1024);

            /** @var User $user */
            $user = $this->getUser();

            // Create user-specific media directory
            $uploadDir = $this->getParameter('kernel.project_dir') . '/public/uploads/profiles/' . $user->getId() . '/media';
            // phpcs:ignore Security.BadFunctions.FilesystemFunctions -- Path uses server-controlled project_dir and database user ID
            if (!is_dir($uploadDir)) {
                // phpcs:ignore Security.BadFunctions.FilesystemFunctions -- Path uses server-controlled project_dir and database user ID
                mkdir($uploadDir, 0755, true);
            }

            // Determine if it's a video or image
            $mimeType = $uploadedFile->getMimeType();
            $isVideo = str_starts_with($mimeType, 'video/');

            if (!$isVideo) {
                // Resize images to max 1920x1920
                $resizedImagePath = $this->imageProcessor->resizeImage($uploadedFile, 1920, 1920, 85);
                $filename = $this->imageProcessor->generateSafeFilename('media', $uploadedFile->guessExtension());
                $destination = $uploadDir . '/' . $filename;

                // phpcs:ignore Security.BadFunctions.FilesystemFunctions -- Paths are server-controlled and safely generated
                if (!rename($resizedImagePath, $destination)) {
                    return $this->json(['success' => false, 'error' => 'Failed to save media'], 500);
                }
            } else {
                // For videos, just move the file
                $filename = $this->imageProcessor->generateSafeFilename('video', $uploadedFile->guessExtension());
                $uploadedFile->move($uploadDir, $filename);
            }

            // Create UserMedia entity
            $profile = $user->getProfile();
            if (!$profile) {
                $profile = new \App\Entity\UserProfile();
                $profile->setUser($user);
                $this->entityManager->persist($profile);
            }

            $media = new \App\Entity\UserMedia();
            $media->setUser($user);
            $media->setFilename($filename);
            $media->setType($isVideo ? 'video' : 'image');
            $media->setUploadedAt(new \DateTimeImmutable());

            $this->entityManager->persist($media);
            $this->entityManager->flush();

            $mediaUrl = '/uploads/profiles/' . $user->getId() . '/media/' . $filename;

            return $this->json([
                'success' => true,
                'url' => $mediaUrl,
                'id' => $media->getId(),
                'message' => 'Media uploaded successfully'
            ]);

        } catch (\InvalidArgumentException $e) {
            return $this->json(['success' => false, 'error' => $e->getMessage()], 400);
        } catch (\Exception $e) {
            return $this->json(['success' => false, 'error' => 'Failed to upload media: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Delete media from profile gallery
     */
    #[Route('/delete-media/{id}', name: 'api_profile_delete_media', methods: ['DELETE'])]
    public function deleteMedia(int $id): JsonResponse
    {
        try {
            /** @var User $user */
            $user = $this->getUser();

            $media = $this->entityManager->getRepository(\App\Entity\UserMedia::class)->find($id);

            if (!$media || $media->getUser()->getId() !== $user->getId()) {
                return $this->json(['success' => false, 'error' => 'Media not found'], 404);
            }

            // Delete file from filesystem
            $filePath = $this->getParameter('kernel.project_dir') . '/public/uploads/profiles/' . $user->getId() . '/media/' . $media->getFilename();
            // phpcs:ignore Security.BadFunctions.FilesystemFunctions -- Path uses server-controlled project_dir, database user ID, and entity filename
            if (file_exists($filePath)) {
                // phpcs:ignore Security.BadFunctions.FilesystemFunctions -- Path uses server-controlled project_dir, database user ID, and entity filename
                unlink($filePath);
            }

            // Delete from database
            $this->entityManager->remove($media);
            $this->entityManager->flush();

            return $this->json(['success' => true, 'message' => 'Media deleted successfully']);

        } catch (\Exception $e) {
            return $this->json(['success' => false, 'error' => 'Failed to delete media'], 500);
        }
    }

    /**
     * Toggle media visibility (public/private)
     */
    #[Route('/toggle-media-visibility/{id}', name: 'api_profile_toggle_media_visibility', methods: ['POST'])]
    public function toggleMediaVisibility(int $id): JsonResponse
    {
        try {
            /** @var User $user */
            $user = $this->getUser();

            $media = $this->entityManager->getRepository(\App\Entity\UserMedia::class)->find($id);

            if (!$media || $media->getUser()->getId() !== $user->getId()) {
                return $this->json(['success' => false, 'error' => 'Media not found'], 404);
            }

            // Toggle visibility
            $media->setIsPublic(!$media->isPublic());
            $this->entityManager->flush();

            return $this->json([
                'success' => true,
                'isPublic' => $media->isPublic(),
                'message' => $media->isPublic() ? 'Media is now public' : 'Media is now hidden'
            ]);

        } catch (\Exception $e) {
            return $this->json(['success' => false, 'error' => 'Failed to toggle visibility'], 500);
        }
    }
}
