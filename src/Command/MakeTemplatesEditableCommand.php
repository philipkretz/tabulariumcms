<?php

namespace App\Command;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:templates:make-editable',
    description: 'Make frontend templates editable in admin panel',
)]
class MakeTemplatesEditableCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $entityManager
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('all', null, InputOption::VALUE_NONE, 'Make all templates editable')
            ->addOption('template-key', null, InputOption::VALUE_REQUIRED, 'Make specific template editable by key');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        try {
            if ($input->getOption('all')) {
                // Make all templates editable
                $query = $this->entityManager->createQuery(
                    'UPDATE App\Entity\FrontendTemplate t SET t.isEditable = true WHERE t.isEditable = false'
                );
                $updated = $query->execute();

                $io->success(sprintf('Made %d template(s) editable', $updated));
            } elseif ($templateKey = $input->getOption('template-key')) {
                // Make specific template editable
                $query = $this->entityManager->createQuery(
                    'UPDATE App\Entity\FrontendTemplate t SET t.isEditable = true WHERE t.templateKey = :key'
                );
                $query->setParameter('key', $templateKey);
                $updated = $query->execute();

                if ($updated > 0) {
                    $io->success(sprintf('Made template "%s" editable', $templateKey));
                } else {
                    $io->warning(sprintf('Template "%s" not found or already editable', $templateKey));
                }
            } else {
                // List non-editable templates
                $templates = $this->entityManager->createQuery(
                    'SELECT t.id, t.name, t.templateKey, t.isEditable FROM App\Entity\FrontendTemplate t ORDER BY t.name'
                )->getResult();

                $io->title('Template Editable Status');
                $io->table(
                    ['ID', 'Name', 'Template Key', 'Editable'],
                    // phpcs:ignore Security.BadFunctions.CallbackFunctions -- Safe inline closure for data transformation
                    array_map(fn($t) => [
                        $t['id'],
                        $t['name'],
                        $t['templateKey'],
                        $t['isEditable'] ? 'Yes' : 'No'
                    ], $templates)
                );

                // phpcs:ignore Security.BadFunctions.CallbackFunctions -- Safe inline closure for filtering
                $nonEditableCount = count(array_filter($templates, fn($t) => !$t['isEditable']));

                if ($nonEditableCount > 0) {
                    $io->info(sprintf('Found %d non-editable template(s)', $nonEditableCount));
                    $io->info('Run with --all to make all templates editable');
                    $io->info('Run with --template-key=<key> to make a specific template editable');
                } else {
                    $io->success('All templates are already editable!');
                }
            }

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $io->error('Failed to update templates: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
