<?php

namespace App\Form;

use App\Entity\Person;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\CountryType;

class UpdatePersonType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name',TextType::class,
                [
                    'attr'=> array('class' => 'form-control','placeholder'=>'Nombre')
                ])
            ->add('lastname',TextType::class,
                [
                    'attr'=> array('class' => 'form-control','placeholder'=>'Apellidos'),
                    'required' => false,
                ])
            ->add('description',TextareaType::class,
                [
                    'attr'=> array('class' => 'form-control','placeholder'=>'Descripción')
                    ,'required' => false,
                ])
            ->add('photoPath',FileType::class,array(
                'data_class' => null,
                'attr'=> array('class' => 'input-file','id'=>'my-file'),
                'required'=>false,
            ))
            ->add('country',CountryType::class,
                [
                    'attr'=>[
                        'class'=>'form-control'
                    ]
                ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Person::class,
        ]);
    }
}
