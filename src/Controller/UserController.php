<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use App\Types\UserTableType;
use Doctrine\ORM\EntityManagerInterface;
use Omines\DataTablesBundle\DataTableFactory;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Lock\LockFactory;
use Symfony\Component\Lock\Store\FlockStore;
use Symfony\Component\PasswordHasher\Hasher\PasswordHasherFactoryInterface;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Class UserController
 *
 * @package App\Controller
 * @author Egidio Langellotti
 * @version 1.0
 *
 */
#[Route("/user", name: "user_")]
class UserController extends AbstractController
{

    private EntityManagerInterface $em;
    private PasswordHasherFactoryInterface $passwordHasherFactory;

    /**
     *  UserController constructor.
     *
     * @param EntityManagerInterface $em
     * @param PasswordHasherFactoryInterface $passwordHasherFactory
     */
    public function __construct(EntityManagerInterface $em, PasswordHasherFactoryInterface $passwordHasherFactory)
    {
        $this->em = $em;
        $this->passwordHasherFactory = $passwordHasherFactory;
    }

    /**
     * User list
     *
     * @param DataTableFactory $factory
     * @param Request $request
     * @return Response
     */
    #[Route("/", name: "index")]
    public function index(DataTableFactory $factory, Request $request): Response
    {
        //Creating a datatable from type
        $table = $factory->createFromType(UserTableType::class)
        ->handleRequest($request);

        //Checking if the request is a callback
        if ($table->isCallback()) {
            return $table->getResponse();
        }

        return $this->render('pages/user/index.html.twig', [
            'datatable' => $table
        ]);
    }

    /**
     *  Create a new user
     *
     * @param Request $request
     * @return Response
     */
    #[Route("/create", name: "create")]
    public function create(Request $request): Response
    {
        return $this->form($request, new User(), UserType::CREATE);
    }

    /**
     *  Edit a user
     *
     * @param Request $request
     * @param User $user
     * @return Response
     */
    #[Route("/edit/{id}", name: "edit")]
    public function edit(Request $request, User $user): Response
    {
        return $this->form($request, $user, UserType::EDIT);
    }

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
        $lock = $factory->createLock('user_delete_' . $request->get('id'));

        //Acquiring the lock
        if ($lock->acquire()) {
            //Deleting the user
            try {
                $filter = $this->em->getRepository(User::class)->find($request->get('id'));

                $this->em->remove($filter);
                $this->em->flush();
                $lock->release();
            } catch (\Exception $e) {
                $lock->release();
                return $this->json(['error' => $e->getMessage()], 500);
            }
        }

        return $this->json(['success' => true]);
    }

    /**
     *  Create or edit a user
     *
     * @param Request $request
     * @param User $user
     * @param $mode
     * @return RedirectResponse|Response
     */
    private function form(Request $request, User $user, $mode): RedirectResponse|Response
    {
        $form = $this->createForm(UserType::class, $user, [
            'mode' => $mode,
        ]);

        if (sizeof($user->getRoles()) > 0)
            $form->get('role')->setData($user->getRoles()[0]);


        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $user->setRoles([$form->get('role')->getData()]);

            if ($mode == UserType::CREATE) {
                $password = $this->passwordHasherFactory->getPasswordHasher(User::class)->hash($form->get('password')->getData());
                $user->setPassword($password);
            }

            $user->setUsername($this->generateUsername($user->getFullName()));

            $this->em->persist($user);
            $this->em->flush();

            $this->addFlash('success', 'User saved successfully');
            return $this->redirectToRoute('user_index');
        }

        return $this->render('pages/user/form.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     *  Generate a username from the full name
     *
     * @param $fullName
     * @return string
     */
    private function generateUsername($fullName): string
    {
        //Splitting the full name into words
        $words = explode(' ', $fullName);

        $username = "";

        //Creating the username
        $i = 0;
        foreach ($words as $word) {
            $username .= preg_replace("/&([a-z])[a-z]+;/i", "$1", htmlentities($word));
            if ($i != (count($words) - 1)) $username .= ".";
            $i++;
        }

        //Lowercasing the username
        return strtolower($username);
    }
}
