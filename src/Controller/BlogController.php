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


class BlogController extends AbstractController
{
    /**
     * @Route("/blog", name="blog")
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

// Fait avant de créer le "Form/ArticleType.php" :
      // $form = $this->createFormBuilder($article)
      //              ->add('title') // Symfony 5 comprends lui-même de quel type de champs il faut : "text", "textarea", "mail", ... ici c'est du "TextType" par défaut mais il faudrait le préciser si onveut pouvoir rajouter d'autre parametres après.
      //              // En effet, on peut aussi rajouter des options (sous forme de tableau) comme des attributs "attr" dans lequel on va aussi faire un tableau avec les différents attibuts et leurs valeurs
      //              // Par exemple : ->add('content', TextareaType::class, ['attr' => ['placeholder' => "Contenu de l'article"]])
      //              // ici c'est du "TextareaType" par défaut, mais on peut modifier en mettant : add('content', TextType::class) et il faudra rajouter : use Symfony\Component\Form\Extension\Core\Type\TextType; qui est pris dans la documentation, le champs de la forme sera donc de type "text"
      //              // Toutefoçs, on ne va pas surcharger le controller avec des paramètres de "vue"
      //              ->add('content')
      //              ->add('image')
      //              // ->add('save', SubmitType::class, ['label' => "Sauvegarder"]) // On n'est pas obligé de mettre le boutton ici si on veut pouvoir le changer en réutilisant le formulaire, alors on va le mettre directement dans la "vue" twig
      //              ->getForm();


      $form = $this->createForm(ArticleType::class, $article);

      $form->handleRequest($request);

      if ($form->isSubmitted() && $form->isValid()) { // if ($form->isSubmitted() && $form->isValid()) ne fonctionne pas et envoi une erreur
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
    public function show($id)
    {
        $repo = $this->getDoctrine()->getRepository(Article::class);
        $article = $repo->find($id);
        return $this->render('blog/show.html.twig', ['article' => $article]);
    }

}
