<?php

namespace App\Controller;

use App\Entity\Post;
use App\Form\PostType;
use App\Repository\PostRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Http\Attribute\IsGranted;

final class PostController extends AbstractController{
    #[Route('/posts', name: 'app_posts', methods:['GET'])]
    public function index(PostRepository $pr): Response
    {   
        return $this->render('post/index.html.twig', [
            'posts' => $pr->findBy([], ['id'=> 'DESC']),
        ]);
    }


    #[IsGranted('ROLE_ADMIN')]
    #[Route('/post/create', name: 'app_post_create', methods:['GET', 'POST'])]
    public function create(Request $request, EntityManagerInterface $em): Response
    {   
        $post = new Post();
        $form = $this->createForm(PostType::class, $post);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $post->setAuthor($this->getUser());
            $em->persist($post);
            $em->flush();

            $this->addFlash('success', 'Post created!');
            return $this->redirectToRoute('app_posts',[
                'id' => $post->getId(),
            ]);
        }

        return $this->render('post/new.html.twig', [
            'post' => $post,
            'postForm' => $form,
            'title' => 'Create a new post',
        ]);
    }

    // #[Route('/post/{id}', name: 'app_post', methods:['GET'])]
    // public function show(Post $post): Response
    // {   
    //     return $this->render('post/show.html.twig', [
    //         'post' => $post,
    //     ]);
    // }
    #[Route('/post/{id}', name: 'app_post', methods:['GET'])]
    public function show($id, PostRepository $pr): Response
    {   
        return $this->render('post/show.html.twig', [
            'post' => $pr->find($id),
        ]);
    }
    #[Route('/post/{id}/edit', name: 'app_post_edit', methods:['GET', 'POST'])]
    public function edit(Post $post): Response
    {   
        return $this->render('post/edit.html.twig', [
            'post' => $post,
        ]);
    }
    #[Route('/post/{id}/delete', name: 'app_post_delete', methods:['GET', 'POST'])]
    public function delete(EntityManagerInterface $em, Request $request, $id, PostRepository $pr): Response
    {   
        $post = $pr->find($id);
        $em->remove($post);
        $em->flush();

        $this->addFlash('success', 'Post deleted!');
        return $this->redirectToRoute('app_posts');
    }
}
