<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface; // Symfony 5 : ce n'est plus « Doctrine\Common\Persistence\ObjectManager »
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use App\Entity\Article;
use App\Form\ArticleType;
use App\Entity\Comment;
use App\Form\NewCommentType;


class BlogController extends AbstractController
{
    /**
     * @Route("/", name="blog")
     */
    public function index()
    {
        $repo = $this->getDoctrine()->getRepository(Article::class);
        $articles = $repo->findAll();
        return $this->render('blog/index.html.twig', [
            'controller_name' => 'BlogController',
            'articles' => $articles
        ]);
    }


    /**
     * @Route("/", name="home")
     */
    public function home()
    {
        return $this->render('blog/home.html.twig');
    }


    /**
    * @Route("/blog/new", name="blog_create")
    * @Route("/blog/{id}/edit", name="blog_edit")
    */
    public function form(Article $article = null, Request $request, EntityManagerInterface $manager) // Symfony 5 : Ce n'est plus « ObjectManager » mais « EntityManagerInterface »
    {
      if (!$article) {
        $article = new Article();
      }

      $form = $this->createForm(ArticleType::class, $article);

      $form->handleRequest($request);

      if ($form->isSubmitted() && $form->isValid()) {
        if (!$article->getId()) {
          $article->setCreatedAt(new \DateTime());
        }

        $manager->persist($article);
        $manager->flush();

        return $this-> redirectToRoute('blog_show', ['id' => $article->getId()]);
      }

      return $this->render('blog/create.html.twig', [
        'formArticle' => $form->createView(),
        'editMode' => $article->getId() !== null
      ]);
    }


    /**
     * @Route("/blog/{id}", name="blog_show")
     */
    public function show($id, Request $request, EntityManagerInterface $manager)
    {
        $repo = $this->getDoctrine()->getRepository(Article::class);
        $article = $repo->find($id);
        // return $this->render('blog/show.html.twig', ['article' => $article]);

        $comment = new Comment();
        $form = $this->createForm(NewCommentType::class, $comment);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
          $comment->setCreatedAt(new \DateTime());

          $comment->setArticle($article);

          $manager->persist($comment);
          $manager->flush();

          return $this-> redirectToRoute('blog_show', ['id' => $article->getId()]);
        }

        return $this->render('blog/show.html.twig', [
          'article' => $article,
          'formComment' => $form->createView(),
        ]);

    }

}
