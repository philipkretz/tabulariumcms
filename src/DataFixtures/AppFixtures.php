<?php

namespace App\DataFixtures;

use App\Entity\User;
use App\Entity\Page;
use App\Entity\Theme;
use App\Entity\Category;
use App\Repository\UserRepository;
use App\Repository\ThemeRepository;
use App\Repository\CategoryRepository;
use App\Repository\PageRepository;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    public function __construct(
        private UserPasswordHasherInterface $passwordHasher,
        private UserRepository $userRepository,
        private ThemeRepository $themeRepository,
        private CategoryRepository $categoryRepository,
        private PageRepository $pageRepository
    ) {
    }

    public function load(ObjectManager $manager): void
    {
        // Create admin user if it doesn't exist
        $admin = $this->userRepository->findOneBy(['email' => 'info@profundi.eu']);

        if (!$admin) {
            $admin = new User();
            $admin->setEmail('info@profundi.eu');
            $admin->setUsername('admin');
            $admin->setFirstName('Admin');
            $admin->setLastName('User');
            $admin->setRoles(['ROLE_ADMIN', 'ROLE_SUPER_ADMIN']);
            $admin->setIsVerified(true);
            $admin->setIsActive(true);
            $admin->setLocale('en');
            $admin->setCurrency('EUR');

            $hashedPassword = $this->passwordHasher->hashPassword($admin, 'admin123');
            $admin->setPassword($hashedPassword);

            $manager->persist($admin);
        }

        // Create default theme if it doesn't exist
        $theme = $this->themeRepository->findOneBy(['name' => 'default']);

        if (!$theme) {
            $theme = new Theme();
            $theme->setName('default');
            $theme->setDisplayName('Default Theme');
            $theme->setDescription('The default theme for TabulariumCMS');
            $theme->setAuthor('TabulariumCMS Team');
            $theme->setVersion('1.0.0');
            $theme->setCategory('default');
            $theme->setThumbnailPath('/images/themes/default.png');
            $theme->setActive(true);
            $theme->setDefault(true);
            $theme->setConfig([
                'colors' => [
                    'primary' => '#3b82f6',
                    'secondary' => '#64748b',
                ],
                'fonts' => [
                    'body' => 'Inter, sans-serif',
                    'heading' => 'Inter, sans-serif',
                ],
            ]);
            $theme->setFiles([]);

            $manager->persist($theme);
        }

        // Create categories
        $categories = [
            ['name' => 'Technology', 'slug' => 'technology', 'description' => 'Latest tech news and tutorials'],
            ['name' => 'Business', 'slug' => 'business', 'description' => 'Business insights and strategies'],
            ['name' => 'Lifestyle', 'slug' => 'lifestyle', 'description' => 'Lifestyle tips and trends'],
            ['name' => 'Travel', 'slug' => 'travel', 'description' => 'Travel guides and adventures'],
            ['name' => 'Food', 'slug' => 'food', 'description' => 'Recipes and culinary experiences'],
        ];

        foreach ($categories as $catData) {
            $existingCategory = $this->categoryRepository->findOneBy(['slug' => $catData['slug']]);
            if (!$existingCategory) {
                $category = new Category();
                $category->setName($catData['name']);
                $category->setSlug($catData['slug']);
                $category->setDescription($catData['description']);
                $category->setIsActive(true);
                $manager->persist($category);
            }
        }

        // Create homepage if it doesn't exist
        $homepage = $this->pageRepository->findOneBy(['slug' => 'home']);

        if (!$homepage) {
            // Get the admin user (whether it was just created or already existed)
            if (!$admin) {
                $admin = $this->userRepository->findOneBy(['email' => 'info@profundi.eu']);
            }

            $homepage = new Page();
            $homepage->setTitle('Welcome to TabulariumCMS');
            $homepage->setSlug('home');
            $homepage->setContent('<h1>Welcome to TabulariumCMS</h1><p>This is your new content management system. Get started by logging into the <a href="/admin">admin panel</a>.</p>');
            $homepage->setTemplate('default');
            $homepage->setMetaTitle('Welcome to TabulariumCMS');
            $homepage->setMetaDescription('A comprehensive web content management system built with Symfony 8, Vue.js, and Tailwind CSS.');
            $homepage->setPublished(true);
            $homepage->setHomePage(false);
            $homepage->setSortOrder(0);
            $homepage->setAuthor($admin);

            $manager->persist($homepage);
        }

        $manager->flush();
    }
}
