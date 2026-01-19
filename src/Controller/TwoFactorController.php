<?php

namespace App\Controller;

use Scheb\TwoFactorBundle\Security\TwoFactor\Provider\Google\GoogleAuthenticatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\RoundBlockSizeMode;
use Endroid\QrCode\Writer\PngWriter;

#[Route('/account/2fa')]
class TwoFactorController extends AbstractController
{
    public function __construct(
        private GoogleAuthenticatorInterface $googleAuthenticator,
        private EntityManagerInterface $entityManager
    ) {
    }

    #[Route('', name: 'app_2fa_setup')]
    public function setup(): Response
    {
        $user = $this->getUser();
        
        if (!$user) {
            throw $this->createAccessDeniedException();
        }

        // Generate secret if not exists
        if (!$user->getGoogleAuthenticatorSecret()) {
            $secret = $this->googleAuthenticator->generateSecret();
            $user->setGoogleAuthenticatorSecret($secret);
            $this->entityManager->flush();
        }

        $qrContent = $this->googleAuthenticator->getQRContent($user);

        return $this->render('security/2fa_setup.html.twig', [
            'user' => $user,
            'qrContent' => $qrContent,
        ]);
    }

    #[Route('/enable', name: 'app_2fa_enable', methods: ['POST'])]
    public function enable(Request $request): Response
    {
        $user = $this->getUser();
        
        if (!$user) {
            throw $this->createAccessDeniedException();
        }

        $code = $request->request->get('code');
        
        if (!$code) {
            $this->addFlash('error', 'Please enter the verification code.');
            return $this->redirectToRoute('app_2fa_setup');
        }

        // Verify the code
        if ($this->googleAuthenticator->checkCode($user, $code)) {
            $user->setIsTwoFactorEnabled(true);
            $this->entityManager->flush();
            
            $this->addFlash('success', 'Two-factor authentication has been enabled successfully!');
        } else {
            $this->addFlash('error', 'Invalid verification code. Please try again.');
        }

        return $this->redirectToRoute('app_2fa_setup');
    }

    #[Route('/disable', name: 'app_2fa_disable', methods: ['POST'])]
    public function disable(): Response
    {
        $user = $this->getUser();
        
        if (!$user) {
            throw $this->createAccessDeniedException();
        }

        $user->setIsTwoFactorEnabled(false);
        $user->setGoogleAuthenticatorSecret(null);
        $this->entityManager->flush();

        $this->addFlash('success', 'Two-factor authentication has been disabled.');

        return $this->redirectToRoute('app_2fa_setup');
    }

    #[Route('/qr-code', name: 'app_2fa_qr_code')]
    public function qrCode(): Response
    {
        $user = $this->getUser();
        
        if (!$user || !$user->getGoogleAuthenticatorSecret()) {
            throw $this->createNotFoundException();
        }

        $qrContent = $this->googleAuthenticator->getQRContent($user);

        $result = Builder::create()
            ->writer(new PngWriter())
            ->writerOptions([])
            ->data($qrContent)
            ->encoding(new Encoding('UTF-8'))
            ->errorCorrectionLevel(ErrorCorrectionLevel::High)
            ->size(300)
            ->margin(10)
            ->roundBlockSizeMode(RoundBlockSizeMode::Margin)
            ->build();

        return new Response($result->getString(), 200, [
            'Content-Type' => 'image/png',
        ]);
    }
}
