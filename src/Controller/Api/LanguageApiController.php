<?php

namespace App\Controller\Api;

use App\Repository\LanguageRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

#[Route("/api", name: "api_")]
class LanguageApiController extends AbstractController
{
    #[Route("/languages", name: "languages", methods: ["GET"])]
    public function getLanguages(LanguageRepository $languageRepository): JsonResponse
    {
        $languages = $languageRepository->findBy(
            ["isActive" => true],
            ["sortOrder" => "ASC", "code" => "ASC"]
        );

        // phpcs:ignore Security.BadFunctions.CallbackFunctions -- Safe inline closure
        $data = array_map(function($language) {
            return [
                "code" => $language->getCode(),
                "name" => $language->getName(),
                "nativeName" => $language->getNativeName(),
                "flagEmoji" => $language->getFlagEmoji(),
                "urlPath" => $language->getUrlPath(),
                "isDefault" => $language->isDefault(),
            ];
        }, $languages);

        return $this->json($data);
    }
}
