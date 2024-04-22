<?php

namespace App\Controller;

use App\Entity\Measurement;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Entity\Filter;


/**
 * Class HomeController
 *
 * @package App\Controller
 * @author Egidio Langellotti
 * @version 1.0
 *
 */
class HomeController extends AbstractController
{
    private EntityManagerInterface $entityManager;

    /**
     * HomeController constructor.
     *
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     *  Home page
     *
     * @return Response
     */
    #[Route('/home', name: 'app_home')]
    public function index(): Response
    {
        $countFilters = $this->entityManager->getRepository(Filter::class)->count();
        $countMeasurements = $this->entityManager->getRepository(Measurement::class)->count();

        return $this->render('pages/home.html.twig', [
            'countFilters' => $countFilters,
            'countMeasurements' => $countMeasurements,
        ]);
    }
}
