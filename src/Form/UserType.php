<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class UserType
 *
 * @package App\Form
 * @author Egidio Langellotti
 * @version 1.0
 *
 */
class UserType extends AbstractType
{
    const CREATE = 'create';
    const EDIT = 'edit';

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('role', ChoiceType::class, [
                'label' => 'Ruolo',
                'choices' => [
                    'Utente' => 'ROLE_USER',
                    'Admin (Può gestire gli utenti)' => 'ROLE_ADMIN',
                ],
                'mapped' => false,
            ])
            ->add('password', PasswordType::class, [
                'label' => 'Password',
                'required' => true,
                'disabled' => $options['mode'] === UserType::EDIT,
            ])
            ->add('name', null, [
                'label' => 'Nome',
                'required' => true,
            ])
            ->add('surname', null, [
                'label' => 'Cognome',
                'required' => true,
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Salva',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
            'mode' => 'create',
        ]);
    }
}
