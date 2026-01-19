<?php
namespace App\Controller\Api;
use App\Entity\Store;
use App\Entity\ProductStock;
use App\Service\PosSyncService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route("/api/pos")]
class PosSyncApiController extends BaseApiController
{
    public function __construct(
        EntityManagerInterface $em,
        SerializerInterface $serializer,
        ValidatorInterface $validator,
        private PosSyncService $posSyncService
    ) {
        parent::__construct($em, $serializer, $validator);
    }

    #[Route("/sync/store/{id}", methods: ["POST"])]
    public function syncStore(Request $request, int $id): Response
    {
        try {
            $this->checkPermission($request, "Store", "update");
            $store = $this->em->getRepository(Store::class)->find($id);
            if (!$store) {
                return $this->jsonResponse(["error" => "Store not found"], 404);
            }
            $result = $this->posSyncService->syncStore($store);
            return $this->jsonResponse(["message" => "Sync completed", "result" => $result]);
        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }
}
