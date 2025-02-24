<?php

namespace App\Controller\Admin;

use App\Entity\User;
use App\Entity\Media;
use App\Form\MediaType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[IsGranted('ROLE_USER')]
class MediaController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager
    )
    {
    }

    #[Route('/admin/media', name: 'admin_media_index')]
    public function index(Request $request): Response
    {
        $page = $request->query->getInt('page', 1);

        $criteria = [];

        if (!$this->isGranted('ROLE_ADMIN')) {
            $criteria['user'] = $this->getUser();
        }

        $medias = $this->entityManager->getRepository(Media::class)->findBy(
            $criteria,
            ['id' => 'ASC'],
            25,
            25 * ($page - 1)
        );
        $total = $this->entityManager->getRepository(Media::class)->count([]);

        return $this->render('admin/media/index.html.twig', [
            'medias' => $medias,
            'total' => $total,
            'page' => $page
        ]);
    }

    #[Route('/admin/media/add', name: 'admin_media_add')]
    public function add(Request $request): Response
    {
        $media = new Media();
        $form = $this->createForm(MediaType::class, $media, ['is_admin' => $this->isGranted('ROLE_ADMIN')]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if (!$this->isGranted('ROLE_ADMIN')) {
                $user = $this->getUser();

                if ($user instanceof User) {
                    $media->setUser($user);
                }
            }
            
            $albumName = $media->getAlbum()->getName();
            $index = $this->entityManager->getRepository(Media::class)->countByAlbum($albumName);
            $mediaIndex = $index + 1;
            $filename = "{$albumName}_{$mediaIndex}.{$media->getFile()->guessExtension()}";
            $path = "uploads/{$filename}";
        
            while (file_exists($path)) {
                $mediaIndex++;
                $filename = "{$albumName}_{$mediaIndex}.{$media->getFile()->guessExtension()}";
                $path = "uploads/{$filename}";
            }

            $media->setPath($path);
            $media->getFile()->move('uploads/', $filename);
            $this->entityManager->persist($media);
            $this->entityManager->flush();
            $this->addFlash('success', 'Média bien ajouté');
            
            return $this->redirectToRoute('admin_media_index');
        }

        // Gérer les erreurs du formulaire
        $formErrors = [];
        foreach ($form->getErrors(true) as $error) {
            $field = $error->getOrigin()->getName();
            $message = $error->getMessage();
            $formErrors[$field] = $message;
        }

        return $this->render('admin/media/add.html.twig', [
            'form' => $form->createView(),
            'formErrors' => $formErrors // Passer les erreurs du formulaire au template
        ]);
    }

    #[Route('/admin/media/delete/{id}', name: 'admin_media_delete')]    
    public function delete(int $id): Response
    {
        $media = $this->entityManager->getRepository(Media::class)->find($id);

        $user = $this->getUser();

        if ($this->isGranted('ROLE_ADMIN') || ($media->getUser() === $user)) {
            $this->entityManager->remove($media);
            $this->entityManager->flush();

            unlink($media->getPath());    
        }

        return $this->redirectToRoute('admin_media_index');    
    }
}