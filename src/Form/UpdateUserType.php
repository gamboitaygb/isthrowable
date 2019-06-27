<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;

class UpdateUserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('email',TextType::class,
                [
                    'attr'=> array('class' => 'form-control','placeholder'=>'Email')
                ])
            ->add('passwordClear',RepeatedType::class,array(
                'type' => PasswordType::class,
                'invalid_message' => 'Las contraseñas no coinciden.',
                'required' => false,
                'first_options'  => array('label' => false,'attr' => array('placeholder' => 'Contraseña','class' => 'form-control')),
                'second_options' => array('label' => false,'attr' => array('placeholder' => 'Repetir contraseña','class' => 'form-control')),
            ));
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
