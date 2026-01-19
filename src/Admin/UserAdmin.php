<?php

namespace App\Admin;

use App\Entity\User;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Show\ShowMapper;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

final class UserAdmin extends AbstractAdmin
{
    public function __construct(
        private UserPasswordHasherInterface $passwordHasher
    ) {
    }

    protected function configureFormFields(FormMapper $form): void
    {
        $isEdit = $this->getSubject() && $this->getSubject()->getId();

        $form
            ->with('User Information', ['class' => 'col-md-6'])
                ->add('email', EmailType::class)
                ->add('username', TextType::class)
                ->add('firstName', TextType::class, ['required' => false])
                ->add('lastName', TextType::class, ['required' => false])
            ->end()
            ->with('Security', ['class' => 'col-md-6'])
                ->add('roles', ChoiceType::class, [
                    'choices' => [
                        'User' => 'ROLE_USER',
                        'Admin' => 'ROLE_ADMIN',
                        'Super Admin' => 'ROLE_SUPER_ADMIN',
                    ],
                    'multiple' => true,
                    'expanded' => true,
                ])
                ->add('isVerified', CheckboxType::class, ['required' => false])
                ->add('isActive', CheckboxType::class, ['required' => false])
            ->end()
            ->with('Preferences', ['class' => 'col-md-6'])
                ->add('locale', ChoiceType::class, [
                    'choices' => [
                        'English' => 'en',
                        'German' => 'de',
                        'Spanish' => 'es',
                        'Catalan' => 'ca',
                    ]
                ])
                ->add('currency', ChoiceType::class, [
                    'choices' => [
                        'EUR' => 'EUR',
                        'USD' => 'USD',
                        'GBP' => 'GBP',
                        'JPY' => 'JPY',
                    ]
                ])
            ->end();

        // Password field only on create, or optionally on edit
        if (!$isEdit) {
            $form
                ->with('Security')
                    ->add('plainPassword', PasswordType::class, [
                        'mapped' => false,
                        'required' => true,
                        'label' => 'Password',
                    ])
                ->end();
        } else {
            $form
                ->with('Security')
                    ->add('plainPassword', PasswordType::class, [
                        'mapped' => false,
                        'required' => false,
                        'label' => 'New Password (leave empty to keep current)',
                    ])
                ->end();
        }
    }

    protected function configureListFields(ListMapper $list): void
    {
        $list
            ->addIdentifier('username')
            ->add('email')
            ->add('firstName')
            ->add('lastName')
            ->add('roles', 'array')
            ->add('isVerified', null, ['editable' => true])
            ->add('isActive', null, ['editable' => true])
            ->add('createdAt')
            ->add('lastLoginAt')
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
            ->add('username')
            ->add('email')
            ->add('firstName')
            ->add('lastName')
            ->add('isVerified')
            ->add('isActive')
            ->add('roles');
    }

    protected function configureShowFields(ShowMapper $show): void
    {
        $show
            ->add('id')
            ->add('email')
            ->add('username')
            ->add('firstName')
            ->add('lastName')
            ->add('roles')
            ->add('isVerified')
            ->add('isActive')
            ->add('locale')
            ->add('currency')
            ->add('createdAt')
            ->add('updatedAt')
            ->add('lastLoginAt');
    }

    public function prePersist(object $object): void
    {
        $this->hashPassword($object);
    }

    public function preUpdate(object $object): void
    {
        $this->hashPassword($object);
    }

    private function hashPassword(User $user): void
    {
        $plainPassword = $this->getForm()->get('plainPassword')->getData();

        if (!empty($plainPassword)) {
            $hashedPassword = $this->passwordHasher->hashPassword($user, $plainPassword);
            $user->setPassword($hashedPassword);
        }
    }

    public function toString(object $object): string
    {
        return $object instanceof User
            ? $object->getUsername() ?? $object->getEmail() ?? 'User'
            : 'User';
    }
}
