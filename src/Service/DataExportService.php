<?php

namespace App\Service;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Yaml\Yaml;

class DataExportService
{
    private array $entities = [
        "App\\Entity\\User",
        "App\\Entity\\Page",
        "App\\Entity\\Post",
        "App\\Entity\\Category",
        "App\\Entity\\Comment",
        "App\\Entity\\Menu",
        "App\\Entity\\MenuItem",
        "App\\Entity\\Media",
        "App\\Entity\\Template",
        "App\\Entity\\Language",
        "App\\Entity\\Translation",
        "App\\Entity\\Theme",
        "App\\Entity\\SiteSettings",
        "App\\Entity\\SeoUrl",
        "App\\Entity\\Article",
        "App\\Entity\\ShippingMethod",
        "App\\Entity\\PaymentMethod",
        "App\\Entity\\VoucherCode",
        "App\\Entity\\Seller",
        "App\\Entity\\Order",
        "App\\Entity\\OrderItem",
        "App\\Entity\\Address",
        "App\\Entity\\Booking",
        "App\\Entity\\UserProfile",
        "App\\Entity\\ContactForm",
        "App\\Entity\\ContactFormField",
        "App\\Entity\\ContactFormSubmission",
        "App\\Entity\\CookieBanner",
        "App\\Entity\\AiWorkflow",
        "App\\Entity\\AiWorkflowStep",
        "App\\Entity\\ApiKey",
        "App\\Entity\\ApiPermission",
    ];

    public function __construct(private EntityManagerInterface $em) {}

    public function exportAll(string $format = "json"): array
    {
        $data = [];
        
        foreach ($this->entities as $entityClass) {
            if (!class_exists($entityClass)) continue;
            
            $shortName = (new \ReflectionClass($entityClass))->getShortName();
            $repository = $this->em->getRepository($entityClass);
            $entities = $repository->findAll();
            
            $data[$shortName] = [];
            foreach ($entities as $entity) {
                $data[$shortName][] = $this->serializeEntity($entity);
            }
        }
        
        return $data;
    }

    public function exportEntity(string $entityClass): array
    {
        $repository = $this->em->getRepository($entityClass);
        $entities = $repository->findAll();
        
        $data = [];
        foreach ($entities as $entity) {
            $data[] = $this->serializeEntity($entity);
        }
        
        return $data;
    }

    public function serializeEntity(object $entity): array
    {
        $data = [];
        $metadata = $this->em->getClassMetadata(get_class($entity));
        
        foreach ($metadata->getFieldNames() as $field) {
            $value = $metadata->getFieldValue($entity, $field);
            
            if ($value instanceof \DateTimeInterface) {
                $value = $value->format("Y-m-d H:i:s");
            } elseif (is_resource($value)) {
                $value = stream_get_contents($value);
            }
            
            $data[$field] = $value;
        }
        
        return $data;
    }

    public function formatData(array $data, string $format): string
    {
        return match($format) {
            "yaml", "yml" => Yaml::dump($data, 10, 2),
            "json" => json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
            default => json_encode($data, JSON_PRETTY_PRINT),
        };
    }

    public function exportToFile(string $format = "json"): string
    {
        $data = $this->exportAll($format);
        $content = $this->formatData($data, $format);
        
        $filename = sprintf("platform_export_%s.%s", date("Y-m-d_His"), $format);
        $filepath = sys_get_temp_dir() . "/" . $filename;

        // phpcs:ignore Security.BadFunctions.FilesystemFunctions -- Using system temp directory with controlled filename
        file_put_contents($filepath, $content);
        
        return $filepath;
    }
}
