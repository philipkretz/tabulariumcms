<?php

namespace App\EventSubscriber;

use App\Entity\Translation;
use App\Repository\LanguageRepository;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\ORM\Events;
use Doctrine\ORM\Event\PrePersistEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;

/**
 * NOTE: This subscriber is currently disabled because the Translation.locale field
 * is a computed property (getLocale() returns $language->getCode()).
 * The synchronization happens automatically through the Language relationship.
 *
 * If we need to support setting translations by locale code in the future,
 * we can re-enable this with proper checks to avoid calling non-existent setLocale().
 */

// Commented out to prevent memory issues when admin panel loads
// #[AsEntityListener(event: Events::prePersist, entity: Translation::class)]
// #[AsEntityListener(event: Events::preUpdate, entity: Translation::class)]
class TranslationSubscriber
{
    public function __construct(
        private LanguageRepository $languageRepository
    ) {
    }

    public function prePersist(Translation $translation, PrePersistEventArgs $event): void
    {
        // Disabled - see class comment
        // $this->syncLanguageAndLocale($translation);
    }

    public function preUpdate(Translation $translation, PreUpdateEventArgs $event): void
    {
        // Disabled - see class comment
        // $this->syncLanguageAndLocale($translation);
    }

    private function syncLanguageAndLocale(Translation $translation): void
    {
        // This logic is not needed because Translation.locale is computed from Translation.language.code
        // The getLocale() method automatically returns $this->language->getCode()

        /*
        $currentLocale = $translation->getLocale();
        $currentLanguage = $translation->getLanguage();

        // If language is set, ensure locale matches
        if ($currentLanguage) {
            $expectedLocale = $currentLanguage->getCode();
            if ($currentLocale !== $expectedLocale) {
                $translation->setLocale($expectedLocale);  // This method doesn't exist!
            }
            return;
        }

        // If only locale is set, try to find matching language
        if ($currentLocale && !$currentLanguage) {
            $language = $this->languageRepository->findOneBy(['code' => $currentLocale]);
            if ($language) {
                $translation->setLanguage($language);
            }
        }
        */
    }
}
