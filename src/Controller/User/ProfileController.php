<?php

namespace App\Controller\User;

use App\Entity\UserProfile;
use App\Entity\Media;
use App\Repository\MediaRepository;
use App\Repository\UserProfileRepository;
use App\Repository\SiteSettingsRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\String\Slugger\SluggerInterface;

#[Route("/user/profile")]
class ProfileController extends AbstractController
{
    #[Route("", name: "user_profile")]
    public function index(
        UserProfileRepository $profileRepository,
        SiteSettingsRepository $settingsRepository
    ): Response {
        $profilesEnabled = $settingsRepository->findOneBy(["settingKey" => "enable_user_profiles"]);
        if (!$profilesEnabled || !$profilesEnabled->getValue()) {
            throw $this->createNotFoundException("User profiles are disabled");
        }

        $this->denyAccessUnlessGranted("IS_AUTHENTICATED_FULLY");
        
        $profile = $profileRepository->findByUser($this->getUser());
        
        if (!$profile) {
            $profile = new UserProfile();
            $profile->setUser($this->getUser());
        }

        return $this->render("profile/index.html.twig", [
            "profile" => $profile,
        ]);
    }

    #[Route("/edit", name: "user_profile_edit")]
    public function edit(
        Request $request,
        UserProfileRepository $profileRepository,
        EntityManagerInterface $em,
        SiteSettingsRepository $settingsRepository
    ): Response {
        $profilesEnabled = $settingsRepository->findOneBy(["settingKey" => "enable_user_profiles"]);
        if (!$profilesEnabled || !$profilesEnabled->getValue()) {
            throw $this->createNotFoundException("User profiles are disabled");
        }

        $this->denyAccessUnlessGranted("IS_AUTHENTICATED_FULLY");
        
        $profile = $profileRepository->findByUser($this->getUser());
        
        if (!$profile) {
            $profile = new UserProfile();
            $profile->setUser($this->getUser());
        }

        if ($request->isMethod("POST")) {
            $profile->setBio($request->request->get("biography"));
            $profile->setIsPublic((bool) $request->request->get("isPublic"));
            $profile->setWebsite($request->request->get("website"));
            $profile->setSocialLinks([
                "twitter" => $request->request->get("twitter"),
                "facebook" => $request->request->get("facebook"),
                "instagram" => $request->request->get("instagram"),
                "linkedin" => $request->request->get("linkedin"),
            ]);

            if (!$profile->getId()) {
                $em->persist($profile);
            }

            $em->flush();

            $this->addFlash("success", "Profile updated successfully!");
            return $this->redirectToRoute("user_profile");
        }

        return $this->render("profile/edit.html.twig", [
            "profile" => $profile,
        ]);
    }

    #[Route("/media", name: "user_profile_media")]
    public function media(MediaRepository $mediaRepository): Response
    {
        $this->denyAccessUnlessGranted("IS_AUTHENTICATED_FULLY");
        
        $media = $mediaRepository->findBy(
            ["uploadedBy" => $this->getUser()],
            ["createdAt" => "DESC"]
        );

        return $this->render("profile/media.html.twig", [
            "media" => $media,
        ]);
    }

    #[Route("/media/upload", name: "user_profile_media_upload", methods: ["POST"])]
    public function uploadMedia(
        Request $request,
        EntityManagerInterface $em,
        SluggerInterface $slugger
    ): Response {
        $this->denyAccessUnlessGranted("IS_AUTHENTICATED_FULLY");

        $files = $request->files->get("files");
        if (!$files) {
            $files = [$request->files->get("file")];
        }

        $uploadedMedia = [];

        foreach ($files as $file) {
            if (!$file) {
                continue;
            }

            // Sanitize the client filename first to prevent path traversal
            $clientFilename = $file->getClientOriginalName();
            $sanitizedClientFilename = \App\Utility\PathValidator::sanitizePath($clientFilename);
            // phpcs:ignore Security.BadFunctions.FilesystemFunctions -- Sanitized input
            $originalFilename = pathinfo($sanitizedClientFilename, PATHINFO_FILENAME);
            $safeFilename = $slugger->slug($originalFilename);
            $newFilename = $safeFilename . "-" . uniqid() . "." . $file->guessExtension();

            try {
                $file->move(
                    $this->getParameter("kernel.project_dir") . "/public/uploads/media",
                    $newFilename
                );

                $media = new Media();
                $media->setFilename($newFilename);
                $media->setOriginalName($originalFilename);
                $media->setMimeType($file->getClientMimeType());
                $media->setSize($file->getSize());
                $media->setType(str_starts_with($file->getClientMimeType(), "image/") ? "image" : "file");
                $media->setUploadedBy($this->getUser());

                $em->persist($media);
                $uploadedMedia[] = $media;

            } catch (FileException $e) {
                // Handle error
            }
        }

        $em->flush();

        // phpcs:ignore Security.BadFunctions.CallbackFunctions -- Safe inline closure for data transformation
        return $this->json(array_map(function($media) {
            return [
                "id" => $media->getId(),
                "src" => "/uploads/media/" . $media->getFilename(),
                "type" => $media->getType(),
                "name" => $media->getOriginalName(),
            ];
        }, $uploadedMedia));
    }
}