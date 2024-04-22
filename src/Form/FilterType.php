<?php

namespace App\Form;

use App\Entity\Filter;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class FilterType
 *
 * @package App\Form
 * @author Egidio Langellotti
 * @version 1.0
 *
 */
class FilterType extends AbstractType
{
    const CREATE = 'create';
    const EDIT = 'edit';

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('type', ChoiceType::class, [
                'label' => 'Tipo',
                'choices' => [
                    'Filtro' => 'F',
                    'Campione' => 'C',
                ],
            ])
            ->add('method', TextType::class, [
                'label' => 'Metodo',
            ])
            ->add('user', EntityType::class, [
                'class' => User::class,
                'choice_label' => function (User $user) {
                    return $user->getFullName();
                },
                'label' => 'Utente',
            ])
            ->add('abbreviation', null, [
                'label' => 'Abbreviazione',
            ])
            ->add('first_line', TextType::class, [
                'label' => 'Prima riga',
            ])
            ->add('second_line', TextType::class, [
                'label' => 'Seconda riga',
            ])
            ->add('filteredVolume', NumberType::class, [
                'label' => 'Volume filtrato',
            ])
            ->add('solidRelationship', NumberType::class, [
                'label' => 'Rapporto solido',
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Salva',
                'attr' => [
                    'class' => 'btn btn-success',
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Filter::class,
        ]);
    }
}
