<?php

namespace App\Controller;

use App\Entity\Language;
use App\Repository\LanguageRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api')]
class LanguageApiController extends AbstractController
{
    public function __construct(
        private LanguageRepository $languageRepository,
        private EntityManagerInterface $entityManager
    ) {}

    #[Route('/languages', name: 'api_languages', methods: ['GET'])]
    public function getActiveLanguages(): JsonResponse
    {
        $languages = $this->languageRepository->findBy(['isActive' => true], ['sortOrder' => 'ASC']);

        // phpcs:ignore Security.BadFunctions.CallbackFunctions -- Safe inline closure
        $languageData = array_map(function (Language $language) {
            return [
                'id' => $language->getId(),
                'code' => $language->getCode(),
                'name' => $language->getName(),
                'nativeName' => $language->getNativeName(),
                'flagEmoji' => $language->getFlagEmoji(),
                'urlPath' => $language->getUrlPath(),
                'isDefault' => $language->isDefault(),
                'isActive' => $language->isActive(),
                'sortOrder' => $language->getSortOrder()
            ];
        }, $languages);

        return new JsonResponse($languageData);
    }

    #[Route('/language/{code}/configure', name: 'api_configure_language', methods: ['POST'])]
    public function configureLanguage(string $code, Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        
        $language = $this->languageRepository->findOneBy(['code' => $code]);
        
        if (!$language) {
            return new JsonResponse(['error' => 'Language not found'], Response::HTTP_NOT_FOUND);
        }

        // Update language configuration
        if (isset($data['urlPath'])) {
            $language->setUrlPath($data['urlPath']);
        }
        
        if (isset($data['useSubdirectory'])) {
            $language->setUseSubdirectory($data['useSubdirectory']);
        }
        
        if (isset($data['flagEmoji'])) {
            $language->setFlagEmoji($data['flagEmoji']);
        }

        $this->entityManager->flush();

        return new JsonResponse([
            'success' => true,
            'language' => [
                'id' => $language->getId(),
                'code' => $language->getCode(),
                'urlPath' => $language->getUrlPath(),
                'fullUrl' => $this->getFullLanguageUrl($language)
            ]
        ]);
    }

    private function getFullLanguageUrl(Language $language): ?string
    {
        return $language->getUrlPath() ? '/' . ltrim($language->getUrlPath(), '/') : null;
    }
}