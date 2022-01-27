<?php

namespace App\Form;

use App\Entity\ItemCollection;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class CreateCollectionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('topic', ChoiceType::class, [
                'required' => true,
                'placeholder' => 'Please select a topic',
                'choices'  => [
                    'Coins' => 'Coins',
                    'Stamps' => 'Stamps',
                    'Weapons' => 'Weapons',
                    'Soldiers' => 'Soldiers',
                    'Bricks with brands' => 'Bricks with brands',
                    'Books' => 'Books',
                    'Alcohol' => 'Alcohol',
                    'Cars' => 'Cars',
                    'Lamps' => 'Lamps',
                ],
            ])
            ->add('name', TextType::class, [
                'constraints' => [
                    new NotBlank([
                        'message' => 'Please enter a name',
                    ]),
                ],
            ])
            ->add('description', TextareaType::class, [
                'constraints' => [
                    new NotBlank([
                        'message' => 'Please enter a description',
                    ]),
                    new Length([
                        'max' => 210,
                        'maxMessage' => 'Your description should be no more than {{ limit }} characters',
                    ]),
                ],
            ])
            ->add('picture', FileType::class, [
                'label' => 'Picture (JPEG, PNG file)',
                'mapped' => false,
                'required' => false,
                'constraints' => [
                    new File([
                        'maxSize' => '4096k',
                        'mimeTypes' => [
                            "image/jpeg", "image/png",
                        ],
                        'mimeTypesMessage' => 'Please upload a valid image',
                    ])
                ],
            ])
            ->add('save', SubmitType::class, [
                'label' => 'Save',
            ])
            ->add('delete', SubmitType::class, [
                'label' => 'Delete',

            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ItemCollection::class,
        ]);
    }
}
