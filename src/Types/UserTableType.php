<?php

namespace App\Types;

use App\Entity\User;
use Omines\DataTablesBundle\Adapter\Doctrine\ORMAdapter;
use Omines\DataTablesBundle\Column\DateTimeColumn;
use Omines\DataTablesBundle\Column\TextColumn;
use Omines\DataTablesBundle\DataTable;
use Omines\DataTablesBundle\DataTableTypeInterface;
use Twig\Environment;

/**
 * Class FilterTableType
 *
 * @package App\Types
 * @author Egidio Langellotti
 * @version 1.0
 *
 */
class UserTableType implements DataTableTypeInterface
{

    private Environment $twig;

    public function __construct(Environment $twig)
    {
        $this->twig = $twig;
    }


    public function configure(DataTable $dataTable, array $options): void
    {
        $dataTable
            ->add('name', TextColumn::class, ['label' => 'Nome', 'render' => function($value, $context) {
                return $context->getName();
            }])
            ->add('surname', TextColumn::class, ['label' => 'Cognome', 'render' => function($value, $context) {
                return $context->getSurname();
            }])
            ->add('role', TextColumn::class, ['label' => 'Ruolo', 'render' => function($value, $context) {
                return implode(', ', $context->getRoles());
            }])
            ->add('creation_date', DateTimeColumn::class, ['label' => 'Data Creazione', 'render' => function($value, $context) {
                return $context->getCreationDate()->format('d/m/Y H:i:s');
            }])
            ->add('actions', TextColumn::class, ['label' => 'Azioni', 'render' => function($value, $context) {
                return $this->twig->render('pages/user/list_actions.html.twig', ['user' => $context]);
            }])
            ->createAdapter(ORMAdapter::class, [
                'entity' => User::class,
                'query' => function($qb) {
                    $qb
                        ->select('u')
                        ->from(User::class, 'u')
                    ;
                }
            ])
        ;
    }

}
