<?php

namespace App\Form;

use App\Entity\Comments;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class CommentsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title',TextType::class,[
                'required'=>0,
                'attr'=> array('class' => 'form-control','placeholder'=>'Título')
            ])
            ->add('content',TextareaType::class,[
                'attr'=> array('class' => 'form-control','placeholder'=>'Cuéntanos lo que piensas','rows'=>'12')
            ])
            ->add('answere',HiddenType::class,[
                'data'=>false,
            ])
            ->add('Enviar',SubmitType::class,array(
                'attr'=> array('class' => 'btn btn-outline-secondary')
            ))
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Comments::class,
        ]);
    }
}
