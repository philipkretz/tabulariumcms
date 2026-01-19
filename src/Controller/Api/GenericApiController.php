<?php

namespace App\Controller\Api;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route("/api")]
class GenericApiController extends BaseApiController
{
    private array $entityMap = [
        "users" => "App\\Entity\\User",
        "pages" => "App\\Entity\\Page",
        "posts" => "App\\Entity\\Post",
        "categories" => "App\\Entity\\Category",
        "comments" => "App\\Entity\\Comment",
        "menus" => "App\\Entity\\Menu",
        "menu-items" => "App\\Entity\\MenuItem",
        "media" => "App\\Entity\\Media",
        "templates" => "App\\Entity\\Template",
        "languages" => "App\\Entity\\Language",
        "translations" => "App\\Entity\\Translation",
        "themes" => "App\\Entity\\Theme",
        "settings" => "App\\Entity\\SiteSettings",
        "seo-urls" => "App\\Entity\\SeoUrl",
        "shipping-methods" => "App\\Entity\\ShippingMethod",
        "payment-methods" => "App\\Entity\\PaymentMethod",
        "vouchers" => "App\\Entity\\VoucherCode",
        "orders" => "App\\Entity\\Order",
        "order-items" => "App\\Entity\\OrderItem",
        "addresses" => "App\\Entity\\Address",
        "bookings" => "App\\Entity\\Booking",
        "profiles" => "App\\Entity\\UserProfile",
        "contact-forms" => "App\\Entity\\ContactForm",
        "bundles" => "App\\Entity\\BundleProduct",
        "stocks" => "App\\Entity\\ProductStock",
    ];

    #[Route("/{entity}", methods: ["GET"])]
    public function index(Request $request, string $entity): Response
    {
        try {
            $entityClass = $this->getEntityClass($entity);
            $this->checkPermission($request, class_basename($entityClass), "read");
            
            $qb = $this->em->getRepository($entityClass)->createQueryBuilder("e");
            $result = $this->paginate($request, $qb);
            
            return $this->jsonResponse($result);
        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    #[Route("/{entity}/{id}", requirements: ["id" => "\d+"], methods: ["GET"])]
    public function show(Request $request, string $entity, int $id): Response
    {
        try {
            $entityClass = $this->getEntityClass($entity);
            $this->checkPermission($request, class_basename($entityClass), "read");
            
            $object = $this->em->getRepository($entityClass)->find($id);
            
            if (!($object)) {
                return $this->jsonResponse(["error" => "Not found"], 404);
            }
            
            return $this->jsonResponse($object);
        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    #[Route("/{entity}", methods: ["POST"])]
    public function create(Request $request, string $entity): Response
    {
        try {
            $entityClass = $this->getEntityClass($entity);
            $this->checkPermission($request, class_basename($entityClass), "create");
            
            $data = json_decode($request->getContent(), true);
            $object = $this->serializer->deserialize(
                $request->getContent(),
                $entityClass,
                "json"
            );
            
            $errors = $this->validateEntity($object);
            if (!empty($errors)) {
                return $this->jsonResponse(["errors" => $errors], 400);
            }
            
            $this->em->persist($object);
            $this->em->flush();
            
            return $this->jsonResponse($object, 201);
        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    #[Route("/{entity}/{id}", methods: ["PUT", "PATCH"])]
    public function update(Request $request, string $entity, int $id): Response
    {
        try {
            $entityClass = $this->getEntityClass($entity);
            $this->checkPermission($request, class_basename($entityClass), "update");
            
            $object = $this->em->getRepository($entityClass)->find($id);
            
            if (!($object)) {
                return $this->jsonResponse(["error" => "Not found"], 404);
            }
            
            $this->serializer->deserialize(
                $request->getContent(),
                $entityClass,
                "json",
                ["object_to_populate" => $object]
            );
            
            $this->em->flush();
            
            return $this->jsonResponse($object);
        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    #[Route("/{entity}/{id}", methods: ["DELETE"])]
    public function delete(Request $request, string $entity, int $id): Response
    {
        try {
            $entityClass = $this->getEntityClass($entity);
            $this->checkPermission($request, class_basename($entityClass), "delete");
            
            $object = $this->em->getRepository($entityClass)->find($id);
            
            if (!($object)) {
                return $this->jsonResponse(["error" => "Not found"], 404);
            }
            
            $this->em->remove($object);
            $this->em->flush();
            
            return $this->jsonResponse(["message" => "Deleted successfully"]);
        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    private function getEntityClass(string $entity): string
    {
        if (!isset($this->entityMap[$entity])) {
            throw new \Exception("Unknown entity: " . $entity);
        }
        return $this->entityMap[$entity];
    }
}

function class_basename(string $class): string
{
    // Sanitize class name first
    $sanitized = \App\Utility\PathValidator::sanitizePath(str_replace("\\", "/", $class));
    // phpcs:ignore Security.BadFunctions.FilesystemFunctions -- Safe: input sanitized with PathValidator
    $basename = basename($sanitized);
    
    // Ensure it's a valid class name (alphanumeric and underscores)
    if (!preg_match('/^[A-Za-z_][A-Za-z0-9_]*$/', $basename)) {
        throw new \InvalidArgumentException('Invalid class name');
    }
    
    return $basename;
}
