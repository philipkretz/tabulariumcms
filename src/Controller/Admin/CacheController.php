<?php

namespace App\Controller\Admin;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Process\Process;

#[Route("/admin/cache")]
#[IsGranted("ROLE_ADMIN")]
class CacheController extends AbstractController
{
    public function __construct(private KernelInterface $kernel) {}

    #[Route("", name: "admin_cache_management")]
    public function index(): Response
    {
        $cacheDir = $this->kernel->getCacheDir();
        $cacheSize = $this->getCacheSize($cacheDir);
        
        return $this->render("admin/cache/index.html.twig", [
            "cache_dir" => $cacheDir,
            "cache_size" => $cacheSize,
            "cache_size_formatted" => $this->formatBytes($cacheSize),
        ]);
    }

    #[Route("/clear", name: "admin_cache_clear", methods: ["POST"])]
    public function clear(): JsonResponse
    {
        try {
            $cacheDir = $this->kernel->getCacheDir();

            // Method 1: Try using Symfony's cache clearer (most reliable in Docker)
            try {
                $this->clearCacheDirectly($cacheDir);
                return new JsonResponse([
                    "success" => true,
                    "message" => "Cache cleared successfully (direct method)",
                    "output" => "Cache directory cleared: " . $cacheDir
                ]);
            } catch (\Exception $e) {
                // If direct method fails, try console command
            }

            // Method 2: Try console command
            $consolePath = $this->kernel->getProjectDir() . "/bin/console";

            // phpcs:ignore Security.BadFunctions.FilesystemFunctions -- Internal project path
            if (!file_exists($consolePath)) {
                throw new \Exception("Console file not found at: " . $consolePath);
            }

            // phpcs:ignore Security.BadFunctions.FilesystemFunctions -- Internal project path
            if (!is_executable($consolePath)) {
                // phpcs:ignore Security.BadFunctions.FilesystemFunctions -- Internal project path
                chmod($consolePath, 0755);
            }

            $process = new Process([
                "php",
                $consolePath,
                "cache:clear",
                "--env=" . $this->kernel->getEnvironment(),
                "--no-interaction"
            ]);

            $process->setTimeout(300);
            $process->run();

            if (!$process->isSuccessful()) {
                $errorOutput = $process->getErrorOutput();
                $output = $process->getOutput();

                return new JsonResponse([
                    "success" => false,
                    "message" => "Process failed",
                    "error" => $errorOutput ?: $output,
                    "exit_code" => $process->getExitCode()
                ], 500);
            }

            return new JsonResponse([
                "success" => true,
                "message" => "Cache cleared successfully",
                "output" => $process->getOutput()
            ]);
        } catch (\Exception $e) {
            return new JsonResponse([
                "success" => false,
                "message" => "Error: " . $e->getMessage()
            ], 500);
        }
    }

    private function clearCacheDirectly(string $cacheDir): void
    {
        // phpcs:ignore Security.BadFunctions.FilesystemFunctions -- Internal cache directory
        if (!is_dir($cacheDir)) {
            throw new \Exception("Cache directory not found: " . $cacheDir);
        }

        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($cacheDir, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST
        );

        foreach ($iterator as $file) {
            if ($file->isDir()) {
                // phpcs:ignore Security.BadFunctions.FilesystemFunctions -- Internal cache directory
                @rmdir($file->getRealPath());
            } else {
                // phpcs:ignore Security.BadFunctions.FilesystemFunctions -- Internal cache directory
                @unlink($file->getRealPath());
            }
        }
    }

    #[Route("/warmup", name: "admin_cache_warmup", methods: ["POST"])]
    public function warmup(): JsonResponse
    {
        try {
            $consolePath = $this->kernel->getProjectDir() . "/bin/console";

            // phpcs:ignore Security.BadFunctions.FilesystemFunctions -- Internal project path
            if (!file_exists($consolePath)) {
                throw new \Exception("Console file not found at: " . $consolePath);
            }

            // phpcs:ignore Security.BadFunctions.FilesystemFunctions -- Internal project path
            if (!is_executable($consolePath)) {
                // phpcs:ignore Security.BadFunctions.FilesystemFunctions -- Internal project path
                chmod($consolePath, 0755);
            }

            $process = new Process([
                "php",
                $consolePath,
                "cache:warmup",
                "--env=" . $this->kernel->getEnvironment(),
                "--no-interaction"
            ]);

            $process->setTimeout(300);
            $process->run();

            if (!$process->isSuccessful()) {
                $errorOutput = $process->getErrorOutput();
                $output = $process->getOutput();

                return new JsonResponse([
                    "success" => false,
                    "message" => "Process failed",
                    "error" => $errorOutput ?: $output,
                    "exit_code" => $process->getExitCode()
                ], 500);
            }

            return new JsonResponse([
                "success" => true,
                "message" => "Cache warmed up successfully",
                "output" => $process->getOutput()
            ]);
        } catch (\Exception $e) {
            return new JsonResponse([
                "success" => false,
                "message" => "Error: " . $e->getMessage()
            ], 500);
        }
    }

    private function getCacheSize(string $directory): int
    {
        $size = 0;
        // phpcs:ignore Security.BadFunctions.FilesystemFunctions -- Internal cache directory
        if (!is_dir($directory)) return 0;
        foreach (new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($directory, \RecursiveDirectoryIterator::SKIP_DOTS)) as $file) {
            if ($file->isFile()) $size += $file->getSize();
        }
        return $size;
    }

    private function formatBytes(int $bytes): string
    {
        $units = ["B", "KB", "MB", "GB", "TB"];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= (1 << (10 * $pow));
        return round($bytes, 2) . " " . $units[$pow];
    }
}
