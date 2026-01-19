<?php

namespace App\Controller\Api;

use App\Entity\ApiKey;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

abstract class BaseApiController extends AbstractController
{
    public function __construct(
        protected EntityManagerInterface $em,
        protected SerializerInterface $serializer,
        protected ValidatorInterface $validator
    ) {}

    protected function checkPermission(Request $request, string $entity, string $action): void
    {
        /** @var ApiKey $apiKey */
        $apiKey = $request->attributes->get("api_key");
        
        if (!($apiKey)) {
            throw $this->createAccessDeniedException("No API key found");
        }

        if (!($apiKey->hasPermission($entity, $action))) {
            throw $this->createAccessDeniedException("Permission denied: " . $action . " on " . $entity);
        }
    }

    protected function jsonResponse(mixed $data, int $status = 200, array $groups = []): JsonResponse
    {
        $context = !empty($groups) ? ["groups" => $groups] : [];
        
        $json = $this->serializer->serialize($data, "json", $context);
        
        return new JsonResponse($json, $status, [], true);
    }

    protected function validateEntity(object $entity): array
    {
        $errors = $this->validator->validate($entity);
        
        $errorMessages = [];
        foreach ($errors as $error) {
            $errorMessages[$error->getPropertyPath()] = $error->getMessage();
        }
        
        return $errorMessages;
    }

    protected function handleException(\Exception $e): JsonResponse
    {
        return new JsonResponse([
            "error" => $e->getMessage()
        ], $e->getCode() >= 400 && $e->getCode() < 600 ? $e->getCode() : 500);
    }

    protected function paginate(Request $request, \Doctrine\ORM\QueryBuilder $qb): array
    {
        $page = max(1, (int) $request->query->get("page", 1));
        $limit = min(100, max(1, (int) $request->query->get("limit", 20)));
        $offset = ($page - 1) * $limit;

        $qb->setFirstResult($offset)->setMaxResults($limit);

        $query = $qb->getQuery();
        $items = $query->getResult();
        
        $countQb = clone $qb;
        $countQb->select("COUNT(e.id)");
        $total = (int) $countQb->getQuery()->getSingleScalarResult();

        return [
            "data" => $items,
            "pagination" => [
                "page" => $page,
                "limit" => $limit,
                "total" => $total,
                "pages" => (int) ceil($total / $limit)
            ]
        ];
    }
}
