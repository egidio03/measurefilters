<?php

namespace App\Types;

use App\Entity\Filter;
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
class FilterTableType implements DataTableTypeInterface
{

    private Environment $twig;

    public function __construct(Environment $twig)
    {
        $this->twig = $twig;
    }

    public function configure(DataTable $dataTable, array $options): void
    {
        $dataTable
            ->add('code', TextColumn::class, ['label' => 'Codice', 'render' => function($value, $context) {
                return $context->getCode();
            }])
            ->add('user', TextColumn::class, ['label' => 'Utente', 'render' => function($value, $context) {
                return $context->getUser()->getFullName();
            }])
            ->add('air_quality_index', TextColumn::class, ['label' => 'Qualità dell\'aria', 'render' => function($value, $context) {
                $quality = $context->getAirQualityIndex();

                /*
                 *  <= 50: Buono - colore blu
                 *  51 - 100: accettabile - colore verde
                 * 101 - 150: Mediocre - colore giallo
                 * 151 - 200: Scadente - colore rosso
                 *  >200: Pessimo - colore viola
                 */
                if ($quality <= 50) {
                    return  $quality . '<span class="badge badge-primary">Buono</span>';
                } elseif ($quality <= 100) {
                    return $quality . '<span class="badge badge-success">Accettabile</span>';
                } elseif ($quality <= 150) {
                    return $quality . '<span class="badge badge-warning">Mediocre</span>';
                } elseif ($quality <= 200) {
                    return $quality . '<span class="badge badge-danger">Scadente</span>';
                } else {
                    return '<span class="badge badge-dark">Pessimo</span>';
                }
            }])
            ->add('created_at', DateTimeColumn::class, ['label' => 'Data Creazione', 'render' => function($value, $context) {
                return $context->getCreatedAt()->format('d/m/Y H:i:s');
            }])
            ->add('deviation_white', TextColumn::class, ['label' => 'Deviazione Bianco', 'render' => function($value, $context) {
                return $context->getDeviationWhite();
            }])
            ->add('deviation_black', TextColumn::class, ['label' => 'Deviazione Nero', 'render' => function($value, $context) {
                return $context->getDeviationBlack();
            }])
            ->add('filtered_volume', TextColumn::class, ['label' => 'Volume Filtrato', 'render' => function($value, $context) {
                return $context->getFilteredVolume();
            }])
            ->add('solid_relationship', TextColumn::class, ['label' => 'Relazione Solidi', 'render' => function($value, $context) {
                return $context->getSolidRelationship();
            }])
            ->add('actions', TextColumn::class, ['label' => '', 'render' => function($value, $context) {
                return $this->twig->render('pages/filters/list_actions.html.twig', ['filter' => $context]);
            }])
            ->createAdapter(ORMAdapter::class, [
                'entity' => Filter::class,
                'query' => function($qb) {
                    $qb
                        ->select('f')
                        ->from(Filter::class, 'f');
                }
            ]);
    }
}
