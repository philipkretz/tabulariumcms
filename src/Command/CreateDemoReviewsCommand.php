<?php

namespace App\Command;

use App\Entity\CustomerReview;
use App\Repository\ArticleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:create-demo-reviews',
    description: 'Creates demo customer reviews with 5-star ratings',
)]
class CreateDemoReviewsCommand extends Command
{
    private array $demoReviews = [
        [
            'name' => 'Sarah Johnson',
            'title' => 'CEO',
            'location' => 'New York, USA',
            'text' => 'Outstanding service and quality! The team exceeded all our expectations. Highly recommend to anyone looking for top-tier products.',
            'image' => 'https://i.pravatar.cc/150?img=1'
        ],
        [
            'name' => 'Michael Chen',
            'title' => 'Marketing Director',
            'location' => 'San Francisco, CA',
            'text' => 'Best purchase I\'ve made this year. The quality is exceptional and the customer support is second to none. Will definitely order again!',
            'image' => 'https://i.pravatar.cc/150?img=12'
        ],
        [
            'name' => 'Emma Thompson',
            'title' => 'Freelance Designer',
            'location' => 'London, UK',
            'text' => 'I\'m absolutely thrilled with my purchase! The attention to detail is remarkable. This has transformed my workflow completely.',
            'image' => 'https://i.pravatar.cc/150?img=5'
        ],
        [
            'name' => 'James Rodriguez',
            'title' => 'Product Manager',
            'location' => 'Barcelona, Spain',
            'text' => 'Incredible value for money. The product quality is outstanding and delivery was faster than expected. Five stars all the way!',
            'image' => 'https://i.pravatar.cc/150?img=33'
        ],
        [
            'name' => 'Lisa Anderson',
            'title' => 'Business Owner',
            'location' => 'Sydney, Australia',
            'text' => 'Simply amazing! This has been a game-changer for our business. The team is professional and the product exceeded expectations.',
            'image' => 'https://i.pravatar.cc/150?img=9'
        ],
        [
            'name' => 'David Kim',
            'title' => 'Software Engineer',
            'location' => 'Seoul, South Korea',
            'text' => 'Perfect in every way. From ordering to delivery, everything was seamless. The quality speaks for itself. Highly recommended!',
            'image' => 'https://i.pravatar.cc/150?img=15'
        ],
        [
            'name' => 'Maria Garcia',
            'title' => 'Fashion Consultant',
            'location' => 'Madrid, Spain',
            'text' => 'Exceeded all my expectations! The craftsmanship is beautiful and the service was impeccable. Will be recommending to all my clients.',
            'image' => 'https://i.pravatar.cc/150?img=10'
        ],
        [
            'name' => 'Robert Taylor',
            'title' => 'Entrepreneur',
            'location' => 'Toronto, Canada',
            'text' => 'Fantastic experience from start to finish! The quality is superb and the attention to customer satisfaction is unmatched.',
            'image' => 'https://i.pravatar.cc/150?img=51'
        ],
        [
            'name' => 'Sophie Martin',
            'title' => 'Interior Designer',
            'location' => 'Paris, France',
            'text' => 'Absolutely love it! The elegance and quality are exactly what I was looking for. This company truly understands excellence.',
            'image' => 'https://i.pravatar.cc/150?img=31'
        ],
        [
            'name' => 'Alexander Schmidt',
            'title' => 'Architect',
            'location' => 'Berlin, Germany',
            'text' => 'Remarkable quality and service! Every detail has been carefully considered. This is what premium products should be like.',
            'image' => 'https://i.pravatar.cc/150?img=14'
        ],
        [
            'name' => 'Jennifer Lee',
            'title' => 'Startup Founder',
            'location' => 'Singapore',
            'text' => 'Best decision ever! The team was incredibly helpful and the product quality is outstanding. Can\'t imagine going anywhere else.',
            'image' => 'https://i.pravatar.cc/150?img=25'
        ],
        [
            'name' => 'Thomas Wilson',
            'title' => 'Creative Director',
            'location' => 'Amsterdam, Netherlands',
            'text' => 'Exceptional in every aspect! From the quality to the customer service, everything was perfect. Highly recommend to everyone!',
            'image' => 'https://i.pravatar.cc/150?img=52'
        ],
    ];

    public function __construct(
        private EntityManagerInterface $entityManager,
        private ArticleRepository $articleRepository
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('count', 'c', InputOption::VALUE_OPTIONAL, 'Number of reviews to create', 12)
            ->addOption('clear', null, InputOption::VALUE_NONE, 'Clear existing reviews before creating new ones')
            ->setHelp('This command creates demo customer reviews with 5-star ratings for testing purposes.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $count = min((int)$input->getOption('count'), count($this->demoReviews));
        $clear = $input->getOption('clear');

        // Clear existing reviews if requested
        if ($clear) {
            $io->note('Clearing existing customer reviews...');
            $qb = $this->entityManager->createQueryBuilder();
            $deleted = $qb->delete(CustomerReview::class, 'r')
                ->getQuery()
                ->execute();
            $io->success("Deleted {$deleted} existing reviews.");
        }

        $io->title('Creating Demo Customer Reviews');
        $io->note("Creating {$count} 5-star customer reviews...");

        // Get some random products for linking (optional)
        $products = $this->articleRepository->findBy(['isActive' => true], null, 10);

        $createdCount = 0;
        for ($i = 0; $i < $count; $i++) {
            $reviewData = $this->demoReviews[$i];

            $review = new CustomerReview();
            $review->setCustomerName($reviewData['name']);
            $review->setCustomerTitle($reviewData['title']);
            $review->setCustomerLocation($reviewData['location']);
            $review->setReviewText($reviewData['text']);
            $review->setCustomerImage($reviewData['image']);
            $review->setRating(5); // Always 5 stars
            $review->setIsActive(true);
            $review->setIsVerified(true);
            $review->setIsFeatured($i < 5); // First 5 are featured
            $review->setSortOrder(100 - $i); // Decreasing sort order

            // Randomly link some reviews to products
            if (!empty($products) && rand(0, 1)) {
                $randomProduct = $products[array_rand($products)];
                $review->setProduct($randomProduct);
            }

            $this->entityManager->persist($review);
            $createdCount++;

            $io->text("✓ Created review from {$reviewData['name']}");
        }

        $this->entityManager->flush();

        $io->success("Successfully created {$createdCount} demo customer reviews!");
        $io->info('All reviews have 5-star ratings and are marked as active.');
        $io->note('You can view them in the admin panel under ECommerce > Customer Reviews');

        return Command::SUCCESS;
    }
}
