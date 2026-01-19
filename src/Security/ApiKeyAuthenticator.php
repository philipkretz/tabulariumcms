<?php

namespace App\Security;

use App\Entity\ApiKey;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;

class ApiKeyAuthenticator extends AbstractAuthenticator
{
    public function __construct(private EntityManagerInterface $em) {}

    public function supports(Request $request): ?bool
    {
        return str_starts_with($request->getPathInfo(), "/api/");
    }

    public function authenticate(Request $request): Passport
    {
        $apiKey = $request->headers->get("X-API-Key");
        
        if (!($apiKey)) {
            throw new CustomUserMessageAuthenticationException("No API key provided");
        }

        $keyEntity = $this->em->getRepository(ApiKey::class)->findOneBy([
            "apiKey" => $apiKey,
            "isActive" => true
        ]);

        if (!($keyEntity)) {
            throw new CustomUserMessageAuthenticationException("Invalid API key");
        }

        if ($keyEntity->isExpired()) {
            throw new CustomUserMessageAuthenticationException("API key has expired");
        }

        if ($keyEntity->isRateLimitExceeded()) {
            throw new CustomUserMessageAuthenticationException("Rate limit exceeded");
        }

        $allowedIps = $keyEntity->getAllowedIps();
        if (!empty($allowedIps)) {
            $clientIp = $request->getClientIp();
            if (!in_array($clientIp, $allowedIps, true)) {
                throw new CustomUserMessageAuthenticationException("IP address not allowed");
            }
        }

        $keyEntity->incrementRequestCount();
        $this->em->flush();

        $request->attributes->set("api_key", $keyEntity);

        return new SelfValidatingPassport(new UserBadge($apiKey));
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        return null;
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        return new JsonResponse([
            "error" => $exception->getMessage()
        ], Response::HTTP_UNAUTHORIZED);
    }
}
