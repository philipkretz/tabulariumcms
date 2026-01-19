<?php

namespace App\Controller;

use App\Service\InstallationChecker;
use App\Service\SetupService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Process\Process;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/setup')]
class SetupController extends AbstractController
{
    public function __construct(
        private InstallationChecker $installationChecker,
        private SetupService $setupService,
        private string $projectDir
    ) {
    }

    #[Route('', name: 'setup_index', methods: ['GET'])]
    public function index(): Response
    {
        if ($this->installationChecker->isInstalled()) {
            return $this->redirectToRoute('app_homepage');
        }

        return $this->render('setup/index.html.twig');
    }

    #[Route('/check-requirements', name: 'setup_check_requirements', methods: ['GET'])]
    public function checkRequirements(): JsonResponse
    {
        if ($this->installationChecker->isInstalled()) {
            return $this->json(['error' => 'System is already installed'], 403);
        }

        $requirements = $this->setupService->checkRequirements();
        $allPassed = $this->setupService->areRequirementsMet();

        return $this->json([
            'success' => true,
            'requirements' => $requirements,
            'allPassed' => $allPassed,
        ]);
    }

    #[Route('/test-database', name: 'setup_test_database', methods: ['POST'])]
    public function testDatabase(Request $request): JsonResponse
    {
        if ($this->installationChecker->isInstalled()) {
            return $this->json(['error' => 'System is already installed'], 403);
        }

        $data = json_decode($request->getContent(), true);

        $host = $data['host'] ?? 'localhost';
        $port = (int) ($data['port'] ?? 3306);
        $database = $data['database'] ?? '';
        $username = $data['username'] ?? '';
        $password = $data['password'] ?? '';

        if (empty($database) || empty($username)) {
            return $this->json([
                'success' => false,
                'message' => 'Database name and username are required',
            ], 400);
        }

        $result = $this->setupService->testDatabaseConnection(
            $host,
            $port,
            $database,
            $username,
            $password
        );

        return $this->json($result);
    }

    #[Route('/save-database', name: 'setup_save_database', methods: ['POST'])]
    public function saveDatabase(Request $request): JsonResponse
    {
        if ($this->installationChecker->isInstalled()) {
            return $this->json(['error' => 'System is already installed'], 403);
        }

        $data = json_decode($request->getContent(), true);

        $host = $data['host'] ?? 'localhost';
        $port = (int) ($data['port'] ?? 3306);
        $database = $data['database'] ?? '';
        $username = $data['username'] ?? '';
        $password = $data['password'] ?? '';

        if (empty($database) || empty($username)) {
            return $this->json([
                'success' => false,
                'message' => 'Database name and username are required',
            ], 400);
        }

        // First test the connection
        $testResult = $this->setupService->testDatabaseConnection(
            $host,
            $port,
            $database,
            $username,
            $password
        );

        if (!$testResult['success']) {
            return $this->json($testResult, 400);
        }

        // Save configuration
        $result = $this->setupService->saveDatabaseConfig(
            $host,
            $port,
            $database,
            $username,
            $password
        );

        return $this->json($result);
    }

    #[Route('/run-migrations', name: 'setup_run_migrations', methods: ['POST'])]
    public function runMigrations(): StreamedResponse
    {
        if ($this->installationChecker->isInstalled()) {
            return new StreamedResponse(function () {
                echo "data: " . json_encode(['error' => 'System is already installed']) . "\n\n";
            }, 403, ['Content-Type' => 'text/event-stream']);
        }

        $response = new StreamedResponse(function () {
            // Disable output buffering
            if (ob_get_level()) {
                ob_end_flush();
            }

            $sendMessage = function (string $type, string $message, ?int $progress = null) {
                $data = [
                    'type' => $type,
                    'message' => $message,
                ];
                if ($progress !== null) {
                    $data['progress'] = $progress;
                }
                echo "data: " . json_encode($data) . "\n\n";
                flush();
            };

            $sendMessage('info', 'Starting migrations...', 0);

            // Clear cache first
            $sendMessage('info', 'Clearing cache...', 10);
            $cacheProcess = new Process(['php', 'bin/console', 'cache:clear', '--no-warmup']);
            $cacheProcess->setWorkingDirectory($this->projectDir);
            $cacheProcess->setTimeout(120);
            $cacheProcess->run();

            if (!$cacheProcess->isSuccessful()) {
                $sendMessage('warning', 'Cache clear had issues: ' . $cacheProcess->getErrorOutput(), 15);
            }

            // Run migrations
            $sendMessage('info', 'Running database migrations...', 20);

            $process = new Process([
                'php', 'bin/console', 'doctrine:migrations:migrate',
                '--no-interaction', '--allow-no-migration'
            ]);
            $process->setWorkingDirectory($this->projectDir);
            $process->setTimeout(300);

            $currentProgress = 20;
            $process->run(function ($type, $buffer) use ($sendMessage, &$currentProgress) {
                $lines = explode("\n", trim($buffer));
                foreach ($lines as $line) {
                    if (!empty(trim($line))) {
                        // Parse migration progress
                        if (str_contains($line, 'Migrating')) {
                            $currentProgress = min($currentProgress + 10, 80);
                            $sendMessage('info', $line, $currentProgress);
                        } elseif (str_contains($line, 'finished')) {
                            $currentProgress = min($currentProgress + 5, 85);
                            $sendMessage('success', $line, $currentProgress);
                        } elseif (str_contains($line, '[WARNING]') || str_contains($line, 'warning')) {
                            $sendMessage('warning', $line, $currentProgress);
                        } elseif (str_contains($line, '[ERROR]') || str_contains($line, 'error')) {
                            $sendMessage('error', $line, $currentProgress);
                        } else {
                            $sendMessage('info', $line, $currentProgress);
                        }
                    }
                }
            });

            if (!$process->isSuccessful()) {
                $sendMessage('error', 'Migration failed: ' . $process->getErrorOutput(), $currentProgress);
                $sendMessage('complete', 'Migration failed', $currentProgress);
                return;
            }

            // Warm up cache
            $sendMessage('info', 'Warming up cache...', 90);
            $warmupProcess = new Process(['php', 'bin/console', 'cache:warmup']);
            $warmupProcess->setWorkingDirectory($this->projectDir);
            $warmupProcess->setTimeout(120);
            $warmupProcess->run();

            $sendMessage('success', 'Migrations completed successfully!', 100);
            $sendMessage('complete', 'All migrations applied successfully', 100);
        });

        $response->headers->set('Content-Type', 'text/event-stream');
        $response->headers->set('Cache-Control', 'no-cache');
        $response->headers->set('Connection', 'keep-alive');
        $response->headers->set('X-Accel-Buffering', 'no');

        return $response;
    }

    #[Route('/create-admin', name: 'setup_create_admin', methods: ['POST'])]
    public function createAdmin(Request $request): JsonResponse
    {
        if ($this->installationChecker->isInstalled()) {
            return $this->json(['error' => 'System is already installed'], 403);
        }

        $data = json_decode($request->getContent(), true);

        $email = trim($data['email'] ?? '');
        $username = trim($data['username'] ?? '');
        $password = $data['password'] ?? '';
        $firstName = trim($data['firstName'] ?? '');
        $lastName = trim($data['lastName'] ?? '');

        // Validation
        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return $this->json([
                'success' => false,
                'message' => 'A valid email address is required',
            ], 400);
        }

        if (empty($username) || strlen($username) < 3) {
            return $this->json([
                'success' => false,
                'message' => 'Username must be at least 3 characters',
            ], 400);
        }

        if (empty($password) || strlen($password) < 8) {
            return $this->json([
                'success' => false,
                'message' => 'Password must be at least 8 characters',
            ], 400);
        }

        if (empty($firstName) || empty($lastName)) {
            return $this->json([
                'success' => false,
                'message' => 'First name and last name are required',
            ], 400);
        }

        $result = $this->setupService->createAdminUser(
            $email,
            $username,
            $password,
            $firstName,
            $lastName
        );

        return $this->json($result, $result['success'] ? 200 : 400);
    }

    #[Route('/save-settings', name: 'setup_save_settings', methods: ['POST'])]
    public function saveSettings(Request $request): JsonResponse
    {
        if ($this->installationChecker->isInstalled()) {
            return $this->json(['error' => 'System is already installed'], 403);
        }

        $data = json_decode($request->getContent(), true);

        $siteName = trim($data['siteName'] ?? 'TabulariumCMS');
        $defaultLanguage = $data['defaultLanguage'] ?? 'en';
        $primaryColor = $data['primaryColor'] ?? '#eab308';
        $secondaryColor = $data['secondaryColor'] ?? '#1f2937';

        // Validate colors
        if (!preg_match('/^#[0-9A-Fa-f]{6}$/', $primaryColor)) {
            $primaryColor = '#eab308';
        }
        if (!preg_match('/^#[0-9A-Fa-f]{6}$/', $secondaryColor)) {
            $secondaryColor = '#1f2937';
        }

        $result = $this->setupService->saveSiteSettings(
            $siteName,
            $defaultLanguage,
            $primaryColor,
            $secondaryColor
        );

        return $this->json($result, $result['success'] ? 200 : 400);
    }

    #[Route('/complete', name: 'setup_complete', methods: ['POST'])]
    public function complete(): JsonResponse
    {
        if ($this->installationChecker->isInstalled()) {
            return $this->json(['error' => 'System is already installed'], 403);
        }

        try {
            $this->installationChecker->markAsInstalled();
        } catch (\RuntimeException $e) {
            return $this->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 403);
        }

        return $this->json([
            'success' => true,
            'message' => 'Installation completed successfully!',
            'redirectUrl' => $this->generateUrl('admin_login'),
        ]);
    }
}
