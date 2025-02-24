<?php

namespace App\Controller\Admin;

use App\Entity\User;
use App\Form\GuestType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[IsGranted('ROLE_ADMIN')]
class GuestController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private UserPasswordHasherInterface $userPasswordHasher
    )
    {
    }

    // public function getEntityManager(): EntityManagerInterface
    // {
    //     return $this->entityManager;
    // }

    // public function getPasswordHasher(): UserPasswordHasherInterface
    // {
    //     return $this->userPasswordHasher;
    // }
    
    #[Route('/admin/guest', name: 'admin_guest_index')]
    public function index(): Response
    {
        $guests = $this->entityManager->getRepository(User::class)->findBy([
            'admin' => false,
        ]);

        return $this->render('admin/guest/index.html.twig', ['guests' => $guests]);
    }

    #[Route('/admin/guest/add', name: 'admin_guest_add')]
    public function add(Request $request): Response
    {
        $guest = new User();
        $form = $this->createForm(GuestType::class, $guest);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $guest->setRoles([]);
            $guest->setAdmin(false);
            $guest->setRestricted(true);

            $plainPassword = $guest->getPassword();
            if ($plainPassword !== null) {
                $guest->setPassword($this->userPasswordHasher->hashPassword($guest, $plainPassword));
            }

            $this->entityManager->persist($guest);
            $this->entityManager->flush();

            return $this->redirectToRoute('admin_guest_index');
        }

        return $this->render('admin/guest/add.html.twig', ['form' => $form->createView()]);
    }

    #[Route('/admin/guest/block/{id}', name: 'admin_guest_block')]
    public function blockAccess(User $user): Response
    {
        $user->setRestricted(true);
        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return $this->redirectToRoute('admin_guest_index');
    }

    #[Route('/admin/guest/unblock/{id}', name: 'admin_guest_unblock')]
    public function unblockAccess(User $user): Response
    {
        $user->setRestricted(false);
        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return $this->redirectToRoute('admin_guest_index');
    }

    #[Route('/admin/guest/delete/{id}', name: 'admin_guest_delete')]
    public function delete(int $id): Response
    {
        $user = $this->entityManager->getRepository(User::class)->find($id);

        $this->entityManager->remove($user);
        $this->entityManager->flush();

        return $this->redirectToRoute('admin_guest_index');
    }
}