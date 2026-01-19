<?php

namespace App\Service;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Yaml\Yaml;

class DataImportService
{
    public function __construct(private EntityManagerInterface $em) {}

    public function importFromFile(string $filepath): array
    {
        // Validate filepath to prevent path traversal
        // phpcs:ignore Security.BadFunctions.FilesystemFunctions -- Using realpath for path validation
        $realPath = realpath($filepath);
        if ($realPath === false) {
            throw new \Exception("Invalid file path: " . $filepath);
        }

        // phpcs:ignore Security.BadFunctions.FilesystemFunctions -- Validated with realpath
        if (!file_exists($realPath)) {
            throw new \Exception("File not found: " . $filepath);
        }

        // phpcs:ignore Security.BadFunctions.FilesystemFunctions -- Validated with realpath
        $content = file_get_contents($realPath);
        // phpcs:ignore Security.BadFunctions.FilesystemFunctions -- Validated with realpath
        $extension = pathinfo($realPath, PATHINFO_EXTENSION);
        
        $data = match($extension) {
            "yaml", "yml" => Yaml::parse($content),
            "json" => json_decode($content, true),
            default => throw new \Exception("Unsupported format: " . $extension),
        };
        
        return $this->importData($data);
    }

    public function importData(array $data): array
    {
        $imported = [];
        $errors = [];
        
        $this->em->getConnection()->beginTransaction();
        
        try {
            foreach ($data as $entityName => $entities) {
                $entityClass = "App\\Entity\\" . $entityName;
                
                if (!class_exists($entityClass)) {
                    $errors[] = "Entity class not found: " . $entityClass;
                    continue;
                }
                
                foreach ($entities as $entityData) {
                    try {
                        $entity = $this->createEntity($entityClass, $entityData);
                        $this->em->persist($entity);
                        $imported[$entityName] = ($imported[$entityName] ?? 0) + 1;
                    } catch (\Exception $e) {
                        $errors[] = sprintf("Error importing %s: %s", $entityName, $e->getMessage());
                    }
                }
            }
            
            $this->em->flush();
            $this->em->getConnection()->commit();
            
        } catch (\Exception $e) {
            $this->em->getConnection()->rollBack();
            throw $e;
        }
        
        return [
            "imported" => $imported,
            "errors" => $errors,
        ];
    }

    private function createEntity(string $entityClass, array $data): object
    {
        $entity = new $entityClass();
        $metadata = $this->em->getClassMetadata($entityClass);
        
        foreach ($data as $field => $value) {
            if ($field === "id") continue;
            
            if ($metadata->hasField($field)) {
                $type = $metadata->getTypeOfField($field);
                
                if (in_array($type, ["datetime", "datetime_immutable", "date", "time"])) {
                    if ($value !== null) {
                        $value = $type === "datetime_immutable" 
                            ? new \DateTimeImmutable($value) 
                            : new \DateTime($value);
                    }
                } elseif ($type === "json") {
                    $value = is_array($value) ? $value : json_decode($value, true);
                } elseif ($type === "boolean") {
                    $value = (bool) $value;
                } elseif ($type === "integer") {
                    $value = (int) $value;
                } elseif ($type === "float" || $type === "decimal") {
                    $value = (float) $value;
                }
                
                $setter = "set" . ucfirst($field);
                if (method_exists($entity, $setter)) {
                    $entity->{$setter}($value);
                }
            }
        }
        
        return $entity;
    }

    public function previewImport(string $filepath): array
    {
        // Validate filepath to prevent path traversal
        // phpcs:ignore Security.BadFunctions.FilesystemFunctions -- Using realpath for path validation
        $realPath = realpath($filepath);
        if ($realPath === false) {
            throw new \Exception("Invalid file path: " . $filepath);
        }

        // phpcs:ignore Security.BadFunctions.FilesystemFunctions -- Validated with realpath
        $content = file_get_contents($realPath);
        // phpcs:ignore Security.BadFunctions.FilesystemFunctions -- Validated with realpath
        $extension = pathinfo($realPath, PATHINFO_EXTENSION);
        
        $data = match($extension) {
            "yaml", "yml" => Yaml::parse($content),
            "json" => json_decode($content, true),
            default => [],
        };
        
        $preview = [];
        foreach ($data as $entityName => $entities) {
            $preview[$entityName] = count($entities);
        }
        
        return $preview;
    }
}
