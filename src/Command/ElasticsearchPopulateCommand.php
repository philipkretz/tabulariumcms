<?php

namespace App\Command;

use FOS\ElasticaBundle\Persister\ObjectPersisterInterface;
use FOS\ElasticaBundle\Index\IndexManager;
use App\Repository\ArticleRepository;
use App\Repository\PostRepository;
use App\Repository\PageRepository;
use App\Repository\CategoryRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:elasticsearch:populate',
    description: 'Populate Elasticsearch indexes with all searchable entities'
)]
class ElasticsearchPopulateCommand extends Command
{
    public function __construct(
        private IndexManager $indexManager,
        private ObjectPersisterInterface $articlePersister,
        private ObjectPersisterInterface $postPersister,
        private ObjectPersisterInterface $pagePersister,
        private ObjectPersisterInterface $categoryPersister,
        private ArticleRepository $articleRepository,
        private PostRepository $postRepository,
        private PageRepository $pageRepository,
        private CategoryRepository $categoryRepository
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $io->title('Elasticsearch Index Population');

        try {
            // Reset all indexes
            $io->section('Step 1: Resetting Indexes');
            $this->resetIndexes($io);

            // Index Articles
            $io->section('Step 2: Indexing Articles/Products');
            $articleCount = $this->indexArticles($io);

            // Index Posts
            $io->section('Step 3: Indexing Blog Posts');
            $postCount = $this->indexPosts($io);

            // Index Pages
            $io->section('Step 4: Indexing CMS Pages');
            $pageCount = $this->indexPages($io);

            // Index Categories
            $io->section('Step 5: Indexing Categories');
            $categoryCount = $this->indexCategories($io);

            // Summary
            $io->success([
                'Elasticsearch indexes successfully populated!',
                sprintf('Articles indexed: %d', $articleCount),
                sprintf('Blog posts indexed: %d', $postCount),
                sprintf('Pages indexed: %d', $pageCount),
                sprintf('Categories indexed: %d', $categoryCount),
                sprintf('Total entities indexed: %d', $articleCount + $postCount + $pageCount + $categoryCount)
            ]);

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $io->error([
                'Error populating Elasticsearch indexes:',
                $e->getMessage()
            ]);

            return Command::FAILURE;
        }
    }

    private function resetIndexes(SymfonyStyle $io): void
    {
        $indexes = ['articles', 'posts', 'pages', 'categories'];

        foreach ($indexes as $indexName) {
            $io->writeln(sprintf('Resetting index: %s', $indexName));
            $index = $this->indexManager->getIndex($indexName);

            // Delete index if exists
            if ($index->exists()) {
                $index->delete();
            }

            // Create index
            $index->create();
        }

        $io->success('All indexes reset successfully');
    }

    private function indexArticles(SymfonyStyle $io): int
    {
        $articles = $this->articleRepository->findBy(['isActive' => true]);
        $count = count($articles);

        $io->writeln(sprintf('Found %d active articles to index', $count));

        if ($count > 0) {
            $io->progressStart($count);

            foreach ($articles as $article) {
                // Transform article for indexing
                $document = [
                    'id' => $article->getId(),
                    'name' => $article->getName(),
                    'slug' => $article->getSlug(),
                    'type' => $article->getType(),
                    'description' => $article->getDescription(),
                    'shortDescription' => $article->getShortDescription(),
                    'sku' => $article->getSku(),
                    'grossPrice' => (float) $article->getGrossPrice(),
                    'netPrice' => (float) $article->getNetPrice(),
                    'taxRate' => (float) $article->getTaxRate(),
                    'isActive' => $article->isActive(),
                    'isFeatured' => $article->isFeatured(),
                    'stock' => $article->getStock(),
                    'ignoreStock' => $article->getIgnoreStock(),
                    'inStock' => $article->getIgnoreStock() || $article->getStock() > 0,
                    'categoryId' => $article->getCategory()?->getId(),
                    'categoryName' => $article->getCategory()?->getName(),
                    'imageUrl' => $article->getMainImage() ? '/uploads/media/' . $article->getMainImage()->getFilename() : null,
                    'imageAlt' => $article->getMainImage()?->getAlt() ?? $article->getName(),
                    'detailUrl' => '/product/' . $article->getSlug(),
                    'createdAt' => $article->getCreatedAt(),
                    'updatedAt' => $article->getUpdatedAt(),
                ];

                $this->articlePersister->insertOne($article, $document);
                $io->progressAdvance();
            }

            $io->progressFinish();
        }

        $io->success(sprintf('Indexed %d articles', $count));

        return $count;
    }

    private function indexPosts(SymfonyStyle $io): int
    {
        $posts = $this->postRepository->findBy(['status' => 'published']);
        $count = count($posts);

        $io->writeln(sprintf('Found %d published posts to index', $count));

        if ($count > 0) {
            $io->progressStart($count);

            foreach ($posts as $post) {
                $document = [
                    'id' => $post->getId(),
                    'title' => $post->getTitle(),
                    'slug' => $post->getSlug(),
                    'excerpt' => $post->getExcerpt(),
                    'content' => $post->getContent(),
                    'status' => $post->getStatus(),
                    'metaTitle' => $post->getMetaTitle(),
                    'metaDescription' => $post->getMetaDescription(),
                    'authorId' => $post->getAuthor()?->getId(),
                    'authorName' => $post->getAuthor()?->getEmail(),
                    'featuredImageUrl' => $post->getFeaturedImage() ? '/uploads/media/' . $post->getFeaturedImage()->getFilename() : null,
                    'detailUrl' => '/blog/' . $post->getSlug(),
                    'publishedAt' => $post->getPublishedAt(),
                    'createdAt' => $post->getCreatedAt(),
                    'updatedAt' => $post->getUpdatedAt(),
                ];

                $this->postPersister->insertOne($post, $document);
                $io->progressAdvance();
            }

            $io->progressFinish();
        }

        $io->success(sprintf('Indexed %d blog posts', $count));

        return $count;
    }

    private function indexPages(SymfonyStyle $io): int
    {
        $pages = $this->pageRepository->findBy(['isPublished' => true]);
        $count = count($pages);

        $io->writeln(sprintf('Found %d published pages to index', $count));

        if ($count > 0) {
            $io->progressStart($count);

            foreach ($pages as $page) {
                $document = [
                    'id' => $page->getId(),
                    'title' => $page->getTitle(),
                    'slug' => $page->getSlug(),
                    'content' => $page->getContent(),
                    'metaTitle' => $page->getMetaTitle(),
                    'metaDescription' => $page->getMetaDescription(),
                    'isPublished' => $page->isPublished(),
                    'template' => $page->getTemplate(),
                    'categoryId' => $page->getCategory()?->getId(),
                    'categoryName' => $page->getCategory()?->getName(),
                    'detailUrl' => '/page/' . $page->getSlug(),
                    'createdAt' => $page->getCreatedAt(),
                    'updatedAt' => $page->getUpdatedAt(),
                ];

                $this->pagePersister->insertOne($page, $document);
                $io->progressAdvance();
            }

            $io->progressFinish();
        }

        $io->success(sprintf('Indexed %d pages', $count));

        return $count;
    }

    private function indexCategories(SymfonyStyle $io): int
    {
        $categories = $this->categoryRepository->findBy(['isActive' => true]);
        $count = count($categories);

        $io->writeln(sprintf('Found %d active categories to index', $count));

        if ($count > 0) {
            $io->progressStart($count);

            foreach ($categories as $category) {
                $document = [
                    'id' => $category->getId(),
                    'name' => $category->getName(),
                    'slug' => $category->getSlug(),
                    'description' => $category->getDescription(),
                    'isActive' => $category->isActive(),
                    'detailUrl' => '/category/' . $category->getSlug(),
                    'createdAt' => $category->getCreatedAt(),
                    'updatedAt' => $category->getUpdatedAt(),
                ];

                $this->categoryPersister->insertOne($category, $document);
                $io->progressAdvance();
            }

            $io->progressFinish();
        }

        $io->success(sprintf('Indexed %d categories', $count));

        return $count;
    }
}
