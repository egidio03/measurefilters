<?php

namespace App\Controller;

use App\Entity\Filter;
use App\Entity\Measurement;
use App\Form\FilterType;
use App\Types\FilterTableType;
use CodeInc\SpreadsheetResponse\SpreadsheetResponse;
use Doctrine\ORM\EntityManagerInterface;
use Omines\DataTablesBundle\Adapter\Doctrine\ORMAdapter;
use Omines\DataTablesBundle\Column\TextColumn;
use Omines\DataTablesBundle\DataTableFactory;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Lock\LockFactory;
use Symfony\Component\Lock\Store\FlockStore;
use Symfony\Component\Lock\Store\SemaphoreStore;
use Symfony\Component\Routing\Attribute\Route;


/**
 * Class FilterController
 *
 * @package App\Controller
 * @author Egidio Langellotti
 * @version 1.0
 *
 */
#[Route('/filter', name: 'filter_')]
class FilterController extends AbstractController
{


    private EntityManagerInterface $entityManager;

    /**
     * FilterController constructor.
     *
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * View filters page
     *
     * @param DataTableFactory $factory
     * @param Request $request
     * @return Response
     */
    #[Route("/", name: "index")]
    public function index(DataTableFactory $factory, Request $request): Response
    {
        //Creating a table from the FilterTableType
        $table = $factory->createFromType(FilterTableType::class)
            ->handleRequest($request);

        //Checking if the table is a callback
        if ($table->isCallback()) {
            return $table->getResponse();
        }

        //Rendering the page
        return $this->render('pages/filters/index.html.twig', [
            'datatable' => $table
        ]);
    }

    /**
     * Create a new filter
     *
     * @param Request $request
     * @return RedirectResponse|Response
     */
    #[Route("/create", name: "create")]
    public function create(Request $request): RedirectResponse|Response
    {
        return $this->form($request, new Filter(), FilterType::CREATE);
    }

    /**
     * Edit a filter
     *
     * @param Request $request
     * @param Filter $filter
     * @return RedirectResponse|Response
     */
    #[Route("/edit/{id}", name: "edit")]
    public function edit(Request $request, Filter $filter): RedirectResponse|Response
    {
        return $this->form($request, $filter, FilterType::EDIT);
    }

    /**
     * Delete a filter
     *
     * @param Request $request
     * @return JsonResponse
     */
    #[Route("/delete", name: "delete")]
    public function delete(Request $request): JsonResponse
    {
        //Checking if the ID is present in the request
        if (!$request->get('id')) {
            return $this->json(['error' => 'ID Mancante'], 404);
        }

        //Creating a lock factory
        $factory = new LockFactory(new FlockStore());

        //Creating a lock to prevent multiple delete requests
        $lock = $factory->createLock('filter_delete_' . $request->get('id'));

        //Acquiring the lock
        if ($lock->acquire()) {
            //Deleting the filter
            try {
                $filter = $this->entityManager->getRepository(Filter::class)->find($request->get('id'));

                $this->entityManager->remove($filter);
                $this->entityManager->flush();
                $lock->release();
            } catch (\Exception $e) {
                $lock->release();
                return $this->json(['error' => $e->getMessage()], 500);
            }
        }

        return $this->json(['success' => true]);
    }

    /**
     *  Form for creating or editing a filter
     *
     * @param Request $request
     * @param Filter $filter
     * @param $mode
     * @return RedirectResponse|Response
     */
    private function form(Request $request, Filter $filter, $mode): RedirectResponse|Response
    {
        //Creating the form
        $form = $this->createForm(FilterType::class, $filter);
        $form->handleRequest($request);

        //Checking if the form is submitted and valid
        if ($form->isSubmitted() && $form->isValid()) {
            //Persisting the filter
            try {
                $this->entityManager->persist($filter);
                $this->entityManager->flush();
            } catch (\Exception $e) {
                return $this->render('pages/filters/form.html.twig', [
                    'form' => $form->createView(),
                    'mode' => $mode,
                ]);
            }

            return $this->redirectToRoute('filter_index');
        }

        return $this->render('pages/filters/form.html.twig', [
            'form' => $form->createView(),
            'mode' => $mode,
        ]);
    }

    //
    //  MEASUREMENTS SECTION
    //
    #[Route("/measurements", name: "measurements")]
    public function measurements(Request $request): JsonResponse
    {
        //Checking if the filter ID is present in the request
        if (!$request->get("filter")) {
            return $this->json(['error' => 'Filter ID mancante'], 404);
        }

        //Finding the filter
        $filter = $this->entityManager->getRepository(Filter::class)->find($request->get("filter"));

        //Checking if the filter exists
        if (!$filter) {
            return $this->json(['error' => 'Filter non trovato'], 404);
        }

        $measurements = $filter->getMeasurements();
        $list = [];

        foreach ($measurements as $measurement) {
            $list[] = [
                'id' => $measurement->getId(),
                'type' => $measurement->getType() == "W" ? "White" : "Black",
                'value' => $measurement->getValue(),
                'temperature' => $measurement->getTemperature(),
                'humidity' => $measurement->getHumidity(),
                'date' => $measurement->getMeasuredAt()->format('Y-m-d H:i:s')
            ];
        }

        return $this->json($list);
    }

    /**
     *  Save a measurement
     *
     * @param Request $request
     * @return JsonResponse
     */
    #[Route("/measurements/save", name: "measurement_save")]
    public function measurementsSave(Request $request): JsonResponse
    {

        //Checking if the required parameters are present
        if (!$this->checkMeasurementFormRequirements($request)) {
            return $this->json(['error' => 'Parametri mancanti'], 400);
        }


        //Checking if the measurement is being edited
        if ($request->get("editing-id")) {
            $measurement = $this->entityManager->getRepository(Measurement::class)->find($request->get('editing-id'));
        } else {
            $measurement = new Measurement();
        }


        //Finding the filter
        $filter = $this->entityManager->getRepository(Filter::class)->find($request->get('filter'));

        //Checking if the filter exists
        if (!$filter) {
            return $this->json(['error' => 'Filtro non trovato'], 404);
        }

        //Setting the measurement data
        $measurement->setFilter($filter);
        $measurement->setType($request->get('type'));
        $measurement->setValue($request->get('value'));
        $measurement->setTemperature($request->get('temperature'));
        $measurement->setHumidity($request->get('humidity'));

        try {
            $measurement->setMeasuredAt(new \DateTimeImmutable($request->get('date')));
        } catch (\Exception $e) {
            return $this->json(['error' => 'Data non valida'], 400);
        }

        //Persisting the measurement
        try {
            $this->entityManager->persist($measurement);
            $this->entityManager->flush();

            //Calculating the average deviation for the filter
            $this->calculateAverageDeviation($filter);

            //Calculating the AirQuality Index
            /*
             * Legal limits
             *
             * PM10: 50 µg/m3 - 1
             * NO2: 200 µg/m3 - 2
             * O3: 120 µg/m3 - 3
             */
            $this->calculateAirQualityIndex($filter);

            $this->entityManager->persist($filter);
            $this->entityManager->flush();
        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], 500);
        }

        return $this->json(['success' => true]);
    }

    /**
     *  Get a measurement
     *
     * @param Request $request
     * @return JsonResponse
     */
    #[Route("/measurement/get", name: "measurement_get")]
    public function measurementGet(Request $request): JsonResponse
    {
        //Checking if the ID is present in the request
        if (!$request->get('id')) {
            return $this->json(['error' => 'ID mancante'], 404);
        }

        //Finding the measurement
        $measurement = $this->entityManager->getRepository(Measurement::class)->find($request->get('id'));

        //Checking if the measurement exists
        if (!$measurement) {
            return $this->json(['error' => 'Misurazione non trovata'], 404);
        }

        return $this->json([
            'filter' => $measurement->getFilter()->getId(),
            'id' => $measurement->getId(),
            'type' => $measurement->getType(),
            'value' => $measurement->getValue(),
            'temperature' => $measurement->getTemperature(),
            'humidity' => $measurement->getHumidity(),
            'date' => $measurement->getMeasuredAt()->format('Y-m-d H:i:s')
        ]);
    }

    /**
     *  Delete a measurement
     *
     * @param Request $request
     * @return JsonResponse
     */
    #[Route("/measurement/delete", name: "measurement_delete")]
    public function measurementDelete(Request $request): JsonResponse
    {
        //Checking if the ID is present in the request
        if (!$request->get('id')) {
            return $this->json(['error' => 'ID mancante'], 404);
        }

        //Finding the measurement
        $measurement = $this->entityManager->getRepository(Measurement::class)->find($request->get('id'));

        //Checking if the measurement exists
        if (!$measurement) {
            return $this->json(['error' => 'Misurazione non trovata'], 404);
        }

        //Deleting the measurement
        try {
            $this->entityManager->remove($measurement);
            $this->entityManager->flush();
        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], 500);
        }

        return $this->json(['success' => true]);
    }

    /**
     * Home Graph Data
     *
     * @return JsonResponse
     */
    #[Route("/measurement/last", name: "measurement_last_data")]
    public function measurementData(): JsonResponse
    {
        //Finding the last 20 measurements
        $measurements = $this->entityManager->getRepository(Measurement::class)->findBy([], ['measured_at' => 'DESC'], 20);

        $list = [];

        //Creating the list
        foreach ($measurements as $measurement) {
            if ($measurement->getMeasuredAt() == null) continue;

            $list[] = [
                'x' => $measurement->getMeasuredAt()->format('Y-m-d H:i:s'),
                'y' => $measurement->getValue()
            ];
        }

        return $this->json($list);
    }

    /**
     *  Export filters to Excel
     *
     * @return SpreadsheetResponse
     */
    #[Route(path: '/export', name: 'export')]
    public function exportFilters(): SpreadsheetResponse
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Add headers
        $sheet->setCellValue('B1', 'Tipo Filtro');
        $sheet->setCellValue('A1', 'Codice Filtro');
        $sheet->setCellValue('C1', 'Utente');
        $sheet->setCellValue('D1', 'Indice Qualità Aria');
        $sheet->setCellValue('E1', 'Deviazione Bianco');
        $sheet->setCellValue('F1', 'Deviazione Nero');
        $sheet->setCellValue('G1', 'Volume Filtrato');
        $sheet->setCellValue('H1', 'Relazione Solido');
        $sheet->setCellValue('I1', 'Data Creazione');

        $filters = $this->entityManager->getRepository(Filter::class)->findAll();

         // Add data from the database
        $row = 2;

        /** @var Filter $filter */
        foreach ($filters as $filter) {
            $sheet->setCellValue('A' . $row, $filter->getCode());
            $sheet->setCellValue('B' . $row, $filter->getType() == "F" ? "Filtro" : "Campione");
            $sheet->setCellValue('C' . $row, $filter->getUser()->getUsername());
            $sheet->setCellValue('D' . $row, $filter->getAirQualityIndex());
            $sheet->setCellValue('E' . $row, $filter->getDeviationWhite());
            $sheet->setCellValue('F' . $row, $filter->getDeviationBlack());
            $sheet->setCellValue('G' . $row, $filter->getFilteredVolume());
            $sheet->setCellValue('H' . $row, $filter->getSolidRelationship());
            $sheet->setCellValue('I' . $row, $filter->getCreatedAt()->format('Y-m-d H:i:s'));
            $row++;
        }

        return new SpreadsheetResponse($spreadsheet, 'filters.xlsx');
    }

    /**
     *  Calculate the Air Quality Index
     *
     * @param Filter $filter
     */
    private function calculateAirQualityIndex(Filter $filter): void
    {
        $ipm10 = ($filter->getDeviationBlack() / 50) * 100;
        $ino2 = ($filter->getDeviationWhite() / 200) * 100;
        $io3 = ($filter->getDeviationWhite() / 120) * 100;

        $filter->setAirQualityIndex(max($ipm10, $ino2, $io3));
    }

    /**
     *  Calculate the average deviation for a filter
     *
     * @param Filter $filter
     */
    private function calculateAverageDeviation(Filter $filter): void
    {
        $measurements = $filter->getMeasurements();

        $black = 0;
        $white = 0;
        $blackCount = 0;
        $whiteCount = 0;

        foreach ($measurements as $m) {
            if ($m->getType() == "B") {
                $black += $m->getValue();
                $blackCount++;
            } else {
                $white += $m->getValue();
                $whiteCount++;
            }
        }

        if ($blackCount != 0)
            $filter->setDeviationBlack($black / $blackCount);

        if ($whiteCount != 0)
            $filter->setDeviationWhite($white / $whiteCount);
    }

    /**
     *  Check if the measurement form requirements are met
     *
     * @param Request $request
     * @return bool
     */
    private function checkMeasurementFormRequirements(Request $request): bool
    {
        if (!$request->get("filter")) {
            return false;
        }

        if (!$request->get('type')) {
            return false;
        }

        if (!$request->get('value')) {
            return false;
        }

        if (!$request->get('temperature')) {
            return false;
        }

        if (!$request->get('humidity')) {
            return false;
        }

        if (!$request->get('date')) {
            return false;
        }

        return true;
    }
}
