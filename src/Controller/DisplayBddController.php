<?php

namespace App\Controller;

use App\Entity\Citations;
use App\Form\CitationsType;
use App\Repository\CitationsRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/products', name: 'app_products_')]
final class DisplayBddController extends AbstractController
{
    private CitationsRepository $CitationsRepository;

    public function __construct(CitationsRepository $CitationsRepository)
    {
        $this->CitationsRepository = $CitationsRepository;
    }

    #[Route('/list', name: 'list', methods: ['GET'])]
    public function index(): Response
    {
        $products = $this->CitationsRepository->findAllByAsc();

        return $this->render('product/index.html.twig', [
            'controller_name' => 'ProductController',
            'products' => $products,
        ]);
    }

    #[Route('/show/{id}', name: 'show', methods: ['GET'])]
    public function show(int $id): Response
    {
        $product = $this->CitationsRepository->find($id);

        if (!$product) {
            throw $this->createNotFoundException('Produit introuvable.');
        }

        return $this->render('product/show.html.twig', [
            'product' => $product,
        ]);
    }

    #[Route('/new', name: 'new', methods: ['GET', 'POST'])]
    public function new(
        Request $request,
        EntityManagerInterface $entityManager
    ): Response {
        $product = new Citations();
        $form = $this->createForm(CitationsType::class, $product);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($product);
            $entityManager->flush();

            $this->addFlash('success', 'Le produit a été créé.');

            return $this->redirectToRoute(
                'app_products_show',
                ['id' => $product->getId()],
                Response::HTTP_SEE_OTHER
            );
        }

        return $this->render('product/form.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/delete/{id}', name: 'delete', methods: ['POST'])]
    public function delete(
        int $id,
        Request $request,
        EntityManagerInterface $entityManager
    ): Response {
        $product = $this->CitationsRepository->find($id);

        if (!$product) {
            throw $this->createNotFoundException('Produit introuvable.');
        }

        if (!$this->isCsrfTokenValid(
            'delete_product_' . $id,
            $request->request->get('_token')
        )) {
            throw $this->createAccessDeniedException('Jeton CSRF invalide.');
        }

        $entityManager->remove($product);
        $entityManager->flush();

        $this->addFlash('success', 'Le produit a été supprimé.');

        return $this->redirectToRoute(
            'app_products_list',
            [],
            Response::HTTP_SEE_OTHER
        );
    }
}
