<?php

namespace App\Controller;

use App\Entity\Music;
use App\Form\MusicType;
use App\Repository\MusicRepository;
use App\Service\FileUploader;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/music')]
class MusicController extends AbstractController
{
    #[Route('/', name: 'app_music_index', methods: ['GET'])]
    public function index(MusicRepository $musicRepository): Response
    {
        return $this->render('music/index.html.twig', [
            'music' => $musicRepository->findAll(),
        ]);
    }

    #[IsGranted('ROLE_ADMIN')]
    #[Route('/new', name: 'app_music_new', methods: ['GET', 'POST'])]
    public function new(Request $request, MusicRepository $musicRepository, FileUploader $fileUploader): Response
    {
        $music = new Music();
        $form = $this->createForm(MusicType::class, $music);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $coverFile = $form->get('cover')->getData();
            if ($coverFile) {
                $coverFileName = $fileUploader->upload($coverFile);
                $music->setCover($coverFileName);
            }
            $musicRepository->save($music, true);

            return $this->redirectToRoute('app_music_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('music/new.html.twig', [
            'music' => $music,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_music_show', methods: ['GET'])]
    public function show(Music $music): Response
    {
        return $this->render('music/show.html.twig', [
            'music' => $music,
        ]);
    }

    #[IsGranted('ROLE_ADMIN')]
    #[Route('/{id}/edit', name: 'app_music_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Music $music, MusicRepository $musicRepository, FileUploader $fileUploader): Response
    {
        $form = $this->createForm(MusicType::class, $music);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $coverFile = $form->get('cover')->getData();
            if ($coverFile) {
                $coverFileName = $fileUploader->upload($coverFile);
                $music->setCover($coverFileName);
            }
            $musicRepository->save($music, true);

            return $this->redirectToRoute('app_music_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('music/edit.html.twig', [
            'music' => $music,
            'form' => $form,
        ]);
    }

    #[IsGranted('ROLE_ADMIN')]
    #[Route('/{id}', name: 'app_music_delete', methods: ['POST'])]
    public function delete(Request $request, Music $music, MusicRepository $musicRepository): Response
    {
        if ($this->isCsrfTokenValid('delete'.$music->getId(), $request->request->get('_token'))) {
            $musicRepository->remove($music, true);
        }

        return $this->redirectToRoute('app_music_index', [], Response::HTTP_SEE_OTHER);
    }
}
