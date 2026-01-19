<?php

namespace App\Admin;

use App\Entity\Comment;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\AdminBundle\Form\Type\ModelType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

final class CommentAdmin extends AbstractAdmin
{
    protected function configureFormFields(FormMapper $form): void
    {
        $form
            ->with('Comment', ['class' => 'col-md-8'])
                ->add('content', TextareaType::class, [
                    'attr' => ['rows' => 10]
                ])
            ->end()
            ->with('Moderation', ['class' => 'col-md-4'])
                ->add('post', ModelType::class, [
                    'class' => 'App\Entity\Post',
                    'property' => 'title'
                ])
                ->add('author', ModelType::class, [
                    'class' => 'App\Entity\User',
                    'property' => 'username'
                ])
                ->add('status', ChoiceType::class, [
                    'choices' => [
                        'Pending' => 'pending',
                        'Approved' => 'approved',
                        'Spam' => 'spam',
                        'Rejected' => 'rejected',
                    ]
                ])
                ->add('approvedBy', ModelType::class, [
                    'class' => 'App\Entity\User',
                    'property' => 'username',
                    'required' => false,
                    'disabled' => true,
                ])
            ->end()
            ->with('Metadata', ['class' => 'col-md-12'])
                ->add('ipAddress', TextType::class, [
                    'disabled' => true,
                    'required' => false,
                ])
                ->add('userAgent', TextType::class, [
                    'disabled' => true,
                    'required' => false,
                ])
            ->end();
    }

    protected function configureListFields(ListMapper $list): void
    {
        $list
            ->add('post', null, [
                'route' => ['name' => 'show']
            ])
            ->add('author')
            ->add('content', null, [
                'template' => '@App/admin/comment/list_excerpt.html.twig'
            ])
            ->add('status', 'choice', [
                'choices' => [
                    'pending' => 'Pending',
                    'approved' => 'Approved',
                    'spam' => 'Spam',
                    'rejected' => 'Rejected',
                ],
                'editable' => true,
            ])
            ->add('createdAt')
            ->add('approvedAt')
            ->add(ListMapper::NAME_ACTIONS, null, [
                'actions' => [
                    'show' => [],
                    'edit' => [],
                    'delete' => [],
                ]
            ]);
    }

    protected function configureDatagridFilters(DatagridMapper $filter): void
    {
        $filter
            ->add('post')
            ->add('author')
            ->add('status')
            ->add('createdAt');
    }

    protected function configureShowFields(ShowMapper $show): void
    {
        $show
            ->add('id')
            ->add('post')
            ->add('author')
            ->add('content')
            ->add('status')
            ->add('ipAddress')
            ->add('userAgent')
            ->add('createdAt')
            ->add('approvedAt')
            ->add('approvedBy');
    }

    public function preUpdate(object $object): void
    {
        // Auto-set approvedBy and approvedAt when status changes to approved
        if ($object instanceof Comment && $object->getStatus() === 'approved' && !$object->getApprovedAt()) {
            $object->setApprovedAt(new \DateTimeImmutable());
            // Get current user from security token storage
            $user = $this->getConfigurationPool()->getContainer()->get('security.token_storage')->getToken()?->getUser();
            if ($user) {
                $object->setApprovedBy($user);
            }
        }
    }

    public function toString(object $object): string
    {
        return $object instanceof Comment
            ? 'Comment on ' . ($object->getPost()?->getTitle() ?? 'Post')
            : 'Comment';
    }
}
