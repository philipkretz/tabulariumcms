<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class LanguageController extends AbstractController
{
    #[Route("/change-language/{locale}", name: "change_language")]
    public function changeLanguage(string $locale, Request $request): Response
    {
        // Set locale in session
        $request->getSession()->set("_locale", $locale);
        
        // Get the referer URL to redirect back
        $referer = $request->headers->get("referer");
        
        if ($referer) {
            return $this->redirect($referer);
        }
        
        return $this->redirectToRoute("sonata_admin_dashboard");
    }
}
