<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\BirthdayType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Gregwar\CaptchaBundle\Type\CaptchaType;

class UserRegistrationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $builder
            ->add('email',EmailType::class)
            ->add('firstname')
            ->add('lastname')
            ->add('pseudo')
            ->add('birthday',BirthdayType::class);

        if(array_key_exists ( 'data', $options)  ){
            $builder->add('password', RepeatedType::class, array(
                'type' => PasswordType::class,
                'required' => true,
                'error_bubbling'=>true,
                'invalid_message' => 'Les mots de passe ne sont pas les même',
                'first_options' => array('label' => 'Mot de passe'),
                'second_options' => array('label' => 'Confirmer le mot de passe'),

            ));
            $builder->add('captcha', CaptchaType::class);//


        }


        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
