<?php

namespace App\Service;

use App\Entity\User;
use App\Entity\UserProfile;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class SetupService
{
    private EntityManagerInterface $entityManager;
    private UserPasswordHasherInterface $passwordHasher;
    private SettingsService $settingsService;
    private string $projectDir;

    public function __construct(
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $passwordHasher,
        SettingsService $settingsService,
        string $projectDir
    ) {
        $this->entityManager = $entityManager;
        $this->passwordHasher = $passwordHasher;
        $this->settingsService = $settingsService;
        $this->projectDir = $projectDir;
    }

    public function checkRequirements(): array
    {
        $requirements = [];

        // PHP Version
        $phpVersion = PHP_VERSION;
        $requirements['php'] = [
            'name' => 'PHP Version',
            'required' => '8.4.0',
            'current' => $phpVersion,
            'passed' => version_compare($phpVersion, '8.4.0', '>='),
        ];

        // Required Extensions
        $requiredExtensions = ['pdo_mysql', 'intl', 'mbstring', 'xml', 'json', 'ctype', 'iconv'];
        foreach ($requiredExtensions as $ext) {
            $requirements['ext_' . $ext] = [
                'name' => "Extension: $ext",
                'required' => 'Installed',
                'current' => extension_loaded($ext) ? 'Installed' : 'Not installed',
                'passed' => extension_loaded($ext),
            ];
        }

        // Writable directories
        $writableDirs = [
            'var' => $this->projectDir . '/var',
            'var/cache' => $this->projectDir . '/var/cache',
            'var/log' => $this->projectDir . '/var/log',
            'public/uploads' => $this->projectDir . '/public/uploads',
        ];

        foreach ($writableDirs as $name => $path) {
            // phpcs:ignore Security.BadFunctions.FilesystemFunctions -- Using server-controlled project directory paths
            $isWritable = is_dir($path) ? is_writable($path) : is_writable(dirname($path));
            $requirements['dir_' . str_replace('/', '_', $name)] = [
                'name' => "Directory: $name",
                'required' => 'Writable',
                'current' => $isWritable ? 'Writable' : 'Not writable',
                'passed' => $isWritable,
            ];
        }

        return $requirements;
    }

    public function areRequirementsMet(): bool
    {
        $requirements = $this->checkRequirements();
        foreach ($requirements as $req) {
            if (!$req['passed']) {
                return false;
            }
        }
        return true;
    }

    public function testDatabaseConnection(
        string $host,
        int $port,
        string $database,
        string $username,
        string $password
    ): array {
        try {
            $dsn = "mysql:host=$host;port=$port;dbname=$database;charset=utf8mb4";
            $pdo = new \PDO($dsn, $username, $password, [
                \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
                \PDO::ATTR_TIMEOUT => 5,
            ]);

            // Test a simple query
            $pdo->query('SELECT 1');

            return [
                'success' => true,
                'message' => 'Database connection successful',
            ];
        } catch (\PDOException $e) {
            return [
                'success' => false,
                'message' => 'Connection failed: ' . $e->getMessage(),
            ];
        }
    }

    public function saveDatabaseConfig(
        string $host,
        int $port,
        string $database,
        string $username,
        string $password
    ): array {
        try {
            $envLocalPath = $this->projectDir . '/.env.local';

            // Build the DATABASE_URL
            $databaseUrl = sprintf(
                'mysql://%s:%s@%s:%d/%s?serverVersion=8.0&charset=utf8mb4',
                urlencode($username),
                urlencode($password),
                $host,
                $port,
                $database
            );

            // Read existing .env.local if it exists
            $existingContent = '';
            // phpcs:ignore Security.BadFunctions.FilesystemFunctions -- Using server-controlled project directory
            if (file_exists($envLocalPath)) {
                // phpcs:ignore Security.BadFunctions.FilesystemFunctions -- Using server-controlled project directory
                $existingContent = file_get_contents($envLocalPath);
            }

            // Remove any existing DATABASE_URL line
            $lines = explode("\n", $existingContent);
            // phpcs:ignore Security.BadFunctions.CallbackFunctions -- Safe inline closure for filtering
            $filteredLines = array_filter($lines, function ($line) {
                return !str_starts_with(trim($line), 'DATABASE_URL=');
            });

            // Add the new DATABASE_URL
            $filteredLines[] = "DATABASE_URL=\"$databaseUrl\"";

            // Write back to file
            // phpcs:ignore Security.BadFunctions.FilesystemFunctions -- Using server-controlled project directory
            file_put_contents($envLocalPath, implode("\n", $filteredLines));

            return [
                'success' => true,
                'message' => 'Database configuration saved to .env.local',
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to save configuration: ' . $e->getMessage(),
            ];
        }
    }

    public function createAdminUser(
        string $email,
        string $username,
        string $password,
        string $firstName,
        string $lastName
    ): array {
        try {
            // Check if email already exists
            $existingUser = $this->entityManager->getRepository(User::class)->findOneBy(['email' => $email]);
            if ($existingUser) {
                return [
                    'success' => false,
                    'message' => 'A user with this email already exists',
                ];
            }

            // Check if username already exists
            $existingUser = $this->entityManager->getRepository(User::class)->findOneBy(['username' => $username]);
            if ($existingUser) {
                return [
                    'success' => false,
                    'message' => 'A user with this username already exists',
                ];
            }

            // Create the admin user
            $user = new User();
            $user->setEmail($email);
            $user->setUsername($username);
            $user->setPassword($this->passwordHasher->hashPassword($user, $password));
            $user->setRoles(['ROLE_SUPER_ADMIN']);
            $user->setFirstName($firstName);
            $user->setLastName($lastName);
            $user->setIsVerified(true);
            $user->setIsActive(true);

            $this->entityManager->persist($user);
            $this->entityManager->flush();

            // Create user profile
            $profile = new UserProfile();
            $profile->setUser($user);
            $profile->setBiography("$firstName $lastName - Administrator");
            $this->entityManager->persist($profile);
            $this->entityManager->flush();

            return [
                'success' => true,
                'message' => 'Admin user created successfully',
                'userId' => $user->getId(),
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to create admin user: ' . $e->getMessage(),
            ];
        }
    }

    public function saveSiteSettings(
        string $siteName,
        string $defaultLanguage,
        string $primaryColor,
        string $secondaryColor
    ): array {
        try {
            $this->settingsService->set('site_title', $siteName, 'string', 'global', 'general');
            $this->settingsService->set('default_locale', $defaultLanguage, 'string', 'global', 'general');
            $this->settingsService->set('primary_color', $primaryColor, 'string', 'global', 'theme');
            $this->settingsService->set('secondary_color', $secondaryColor, 'string', 'global', 'theme');

            // Initialize other default settings
            $this->settingsService->initializeDefaultSettings();

            return [
                'success' => true,
                'message' => 'Site settings saved successfully',
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to save site settings: ' . $e->getMessage(),
            ];
        }
    }
}
