<?php

namespace App\DataFixtures;

use App\Entity\Category;
use App\Repository\CategoryRepository;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Persistence\ObjectManager;

class CategoryFixtures extends Fixture implements FixtureGroupInterface
{
    public static function getGroups(): array
    {
        return ['article'];
    }
    public function __construct(
        private CategoryRepository $categoryRepository
    ) {
    }

    public function load(ObjectManager $manager): void
    {
        $categories = [
            [
                'name' => 'Electronics',
                'slug' => 'electronics',
                'description' => 'Computers, smartphones, tablets, and electronic accessories'
            ],
            [
                'name' => 'Clothing',
                'slug' => 'clothing',
                'description' => 'Fashion items, apparel, shoes, and accessories'
            ],
            [
                'name' => 'Books',
                'slug' => 'books',
                'description' => 'Fiction, non-fiction, educational books, and e-books'
            ],
            [
                'name' => 'Home & Garden',
                'slug' => 'home-garden',
                'description' => 'Furniture, home decor, gardening tools, and appliances'
            ],
            [
                'name' => 'Sports',
                'slug' => 'sports',
                'description' => 'Sports equipment, fitness gear, and outdoor activities'
            ],
            [
                'name' => 'Toys',
                'slug' => 'toys',
                'description' => 'Toys, games, puzzles, and educational play items'
            ],
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

                // Store reference for ArticleFixtures
                $this->addReference('category_' . $catData['slug'], $category);
            } else {
                // Store reference to existing category
                $this->addReference('category_' . $catData['slug'], $existingCategory);
            }
        }

        $manager->flush();
    }
}
