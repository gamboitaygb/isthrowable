<?php

namespace App\Form;

use App\Entity\Post;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PostType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title',TextType::class,[
                'attr'=> array('class' => 'form-control','placeholder'=>'Título')
            ])
            ->add('content',TextareaType::class,[
                'attr'=> array('class' => 'form-control','placeholder'=>'Contenido')
            ])
            ->add('photoPath',FileType::class,[
                'attr'=> array('class' => 'form-control'),
                'required'=>false,
                'label'=>false,
            ])
            ->add('active',CheckboxType::class,[
                'required'=>false,
                'attr'=>['checked'=>true]
            ])

            ->add('new',CheckboxType::class,[
                'required'=>false,
                'attr'=>['checked'=>true]
            ])
        ;
        if($options['actions']=='create'){
            $builder->add('Crear',SubmitType::class,array(
                'attr'=> array('class' => 'btn btn-outline-secondary')
            ));
        }else{
            $builder->add('Actualizar',SubmitType::class,array(
                'attr'=> array('class' => 'btn btn-outline-secondary')
            ));
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Post::class,
            'actions'=>'create'
        ]);
    }
}
