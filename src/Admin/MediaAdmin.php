<?php

namespace App\Admin;

use App\Entity\Media;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\AdminBundle\Form\Type\ModelType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Validator\Constraints\File;

final class MediaAdmin extends AbstractAdmin
{
    protected function configureFormFields(FormMapper $form): void
    {
        $media = $this->getSubject();
        $isNew = !$media || !$media->getId();

        $form
            ->with('File Upload', ['class' => 'col-md-12'])
                ->add('file', FileType::class, [
                    'label' => 'Upload File',
                    'mapped' => false,
                    'required' => $isNew,
                    'constraints' => [
                        new File([
                            'maxSize' => '50M',
                            'mimeTypes' => [
                                'image/*',
                                'video/*',
                                'audio/*',
                                'application/pdf',
                                'application/msword',
                                'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                                'application/vnd.ms-excel',
                                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                                'application/zip',
                                'application/x-rar-compressed',
                            ],
                        ])
                    ],
                    'help' => 'Max file size: 50MB. Drag and drop or click to select.',
                    'attr' => [
                        'accept' => 'image/*,video/*,audio/*,.pdf,.doc,.docx,.xls,.xlsx,.zip,.rar',
                        'class' => 'file-upload-input',
                    ],
                ])
            ->end()
            ->with('File Information', ['class' => 'col-md-6'])
                ->add('originalName', TextType::class, [
                    'disabled' => true,
                    'required' => false,
                ])
                ->add('filename', TextType::class, [
                    'disabled' => true,
                    'required' => false,
                ])
                ->add('mimeType', TextType::class, [
                    'disabled' => true,
                    'required' => false,
                ])
                ->add('size', null, [
                    'disabled' => true,
                    'required' => false,
                ])
            ->end()
            ->with('Metadata', ['class' => 'col-md-6'])
                ->add('type', ChoiceType::class, [
                    'choices' => [
                        'Image' => 'image',
                        'Video' => 'video',
                        'Audio' => 'audio',
                        'Document' => 'document',
                        'Archive' => 'archive',
                    ]
                ])
                ->add('alt', TextType::class, ['required' => false])
                ->add('description', TextareaType::class, ['required' => false])
                ->add('uploadedBy', ModelType::class, [
                    'class' => 'App\Entity\User',
                    'property' => 'username',
                    'disabled' => true,
                    'required' => false,
                ])
            ->end();
    }

    protected function configureListFields(ListMapper $list): void
    {
        $list
            ->addIdentifier('originalName')
            ->add('filename')
            ->add('type')
            ->add('mimeType')
            ->add('size', null, [
                'template' => '@SonataAdmin/CRUD/list_file_size.html.twig',
            ])
            ->add('uploadedBy')
            ->add('createdAt')
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
            ->add('originalName')
            ->add('filename')
            ->add('type')
            ->add('mimeType')
            ->add('uploadedBy');
    }

    protected function configureShowFields(ShowMapper $show): void
    {
        $show
            ->add('id')
            ->add('filename')
            ->add('originalName')
            ->add('mimeType')
            ->add('size')
            ->add('type')
            ->add('alt')
            ->add('description')
            ->add('uploadedBy')
            ->add('createdAt');
    }

    public function toString(object $object): string
    {
        return $object instanceof Media
            ? $object->getOriginalName() ?? 'Media'
            : 'Media';
    }
}
