<?php

namespace App\Service;

use App\Entity\Store;
use App\Entity\ProductStock;
use App\Entity\Article;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Psr\Log\LoggerInterface;

class PosSyncService
{
    public function __construct(
        private EntityManagerInterface $em,
        private HttpClientInterface $httpClient,
        private LoggerInterface $logger
    ) {}

    public function syncStore(Store $store): array
    {
        if (!($store->getPosSystemType())) {
            throw new \Exception("Store has no POS system configured");
        }

        return match($store->getPosSystemType()) {
            "agora" => $this->syncAgoraStore($store),
            "square" => $this->syncSquareStore($store),
            "shopify" => $this->syncShopifyStore($store),
            default => throw new \Exception("Unsupported POS system: " . $store->getPosSystemType()),
        };
    }

    private function syncAgoraStore(Store $store): array
    {
        $synced = ["stock" => 0, "prices" => 0];
        $errors = [];

        try {
            $stockData = $this->fetchAgoraStock($store);
            
            foreach ($stockData as $item) {
                try {
                    $stock = $this->updateStockFromAgora($store, $item);
                    if ($stock) {
                        $synced["stock"]++;
                    }
                } catch (\Exception $e) {
                    $errors[] = "Stock sync error: " . $e->getMessage();
                }
            }

            $priceData = $this->fetchAgoraPrices($store);
            
            foreach ($priceData as $item) {
                try {
                    $updated = $this->updatePriceFromAgora($store, $item);
                    if ($updated) {
                        $synced["prices"]++;
                    }
                } catch (\Exception $e) {
                    $errors[] = "Price sync error: " . $e->getMessage();
                }
            }

            $store->setLastSyncAt(new \DateTimeImmutable());
            $this->em->flush();

        } catch (\Exception $e) {
            $this->logger->error("Agora sync failed", [
                "store" => $store->getId(),
                "error" => $e->getMessage()
            ]);
            throw $e;
        }

        return ["synced" => $synced, "errors" => $errors];
    }

    private function fetchAgoraStock(Store $store): array
    {
        $response = $this->httpClient->request("GET", "https://api.agora-pos.com/v1/stores/" . $store->getPosSystemId() . "/inventory", [
            "headers" => [
                "Authorization" => "Bearer " . $this->getAgoraApiKey($store),
                "Accept" => "application/json"
            ]
        ]);

        return $response->toArray();
    }

    private function fetchAgoraPrices(Store $store): array
    {
        $response = $this->httpClient->request("GET", "https://api.agora-pos.com/v1/stores/" . $store->getPosSystemId() . "/prices", [
            "headers" => [
                "Authorization" => "Bearer " . $this->getAgoraApiKey($store),
                "Accept" => "application/json"
            ]
        ]);

        return $response->toArray();
    }

    private function updateStockFromAgora(Store $store, array $data): ?ProductStock
    {
        $posProductId = $data["product_id"] ?? null;
        if (!($posProductId)) {
            return null;
        }

        $stock = $this->em->getRepository(ProductStock::class)->findOneBy([
            "store" => $store,
            "posProductId" => $posProductId
        ]);

        if (!($stock)) {
            $article = $this->findArticleByPosSku($data["sku"] ?? null);
            if (!($article)) {
                return null;
            }

            $stock = new ProductStock();
            $stock->setStore($store);
            $stock->setArticle($article);
            $stock->setPosProductId($posProductId);
            $this->em->persist($stock);
        }

        $stock->setQuantity((int) ($data["quantity"] ?? 0));
        $stock->setLastSyncAt(new \DateTimeImmutable());

        return $stock;
    }

    private function updatePriceFromAgora(Store $store, array $data): bool
    {
        $posProductId = $data["product_id"] ?? null;
        if (!($posProductId)) {
            return false;
        }

        $stock = $this->em->getRepository(ProductStock::class)->findOneBy([
            "store" => $store,
            "posProductId" => $posProductId
        ]);

        if ($stock && isset($data["price"])) {
            $stock->setStorePrice((string) $data["price"]);
            return true;
        }

        return false;
    }

    private function syncSquareStore(Store $store): array
    {
        return ["synced" => ["stock" => 0, "prices" => 0], "errors" => ["Square integration not yet implemented"]];
    }

    private function syncShopifyStore(Store $store): array
    {
        return ["synced" => ["stock" => 0, "prices" => 0], "errors" => ["Shopify integration not yet implemented"]];
    }

    private function getAgoraApiKey(Store $store): string
    {
        return $_ENV["AGORA_API_KEY"] ?? "";
    }

    private function findArticleByPosSku(?string $sku): ?Article
    {
        if (!($sku)) {
            return null;
        }
        return $this->em->getRepository(Article::class)->findOneBy(["sku" => $sku]);
    }

    public function pushStockToPos(ProductStock $stock): bool
    {
        $store = $stock->getStore();
        
        if (!($store->getPosSystemType())) {
            return false;
        }

        return match($store->getPosSystemType()) {
            "agora" => $this->pushStockToAgora($stock),
            default => false,
        };
    }

    private function pushStockToAgora(ProductStock $stock): bool
    {
        try {
            $response = $this->httpClient->request("PUT", "https://api.agora-pos.com/v1/inventory/" . $stock->getPosProductId(), [
                "headers" => [
                    "Authorization" => "Bearer " . $this->getAgoraApiKey($stock->getStore()),
                    "Content-Type" => "application/json"
                ],
                "json" => [
                    "quantity" => $stock->getQuantity(),
                    "store_id" => $stock->getStore()->getPosSystemId()
                ]
            ]);

            return $response->getStatusCode() === 200;
        } catch (\Exception $e) {
            $this->logger->error("Failed to push stock to Agora", [
                "stock" => $stock->getId(),
                "error" => $e->getMessage()
            ]);
            return false;
        }
    }
}
