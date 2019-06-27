<?php

namespace App\Form;

use App\Entity\Register;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class RegisterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('user',UserType::class)
            ->add('person',PersonType::class)

        ;

        if ('user_register' === $options['register']) {

            $builder->add('Crear cuenta',SubmitType::class,array(
                'attr'=> array('class' => 'btn btn-outline-secondary')
            ));
        }else if('user_profile' === $options['register']){
            $builder
                ->add('Actualizar',SubmitType::class,array(
                    'attr'=> array('class' => 'btn btn-success')
                ));
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Register::class,
            'register'=>'user_register'
        ]);
    }
}
