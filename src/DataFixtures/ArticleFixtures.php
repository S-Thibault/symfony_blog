<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use App\Entity\Article;
use App\Entity\Category;
use App\Entity\Comment;

class ArticleFixtures extends Fixture
{
    public function load(ObjectManager $manager)
    {
        $faker = \Faker\Factory::create('fr_FR');

        // Création de 3 catégories fictives
        for ($i = 1; $i <= 3; $i++) {
            $category = new Category();
            $category->setTitle($faker->sentence())
                     ->setDescription($faker->paragraph());

            $manager->persist($category);

            // Création de 4 à 6 articles fictifs par catéghorie
            for ($j = 1; $j <= mt_rand(4, 6); $j++) {
                $article = new Article();

                $content = '<p>' . join($faker->paragraphs(5), '</p><p>') . '</p>'; // Faker fait des paragraphes sous forme de tableaux donc il faut les 'éclater'

                $article->setTitle($faker->sentence())
                        ->setContent($content)
                        ->setImage($faker->imageUrl())
                        ->setCreatedAt($faker->dateTimeBetween('-6 months'))
                        ->setCategory($category);

                $manager->persist($article);

                // Création de 2 à 10 commentaires fictifs par article
                for ($k = 1; $k <= mt_rand(2, 5); $k++) {
                    $comment = new Comment();

                    $content = '<p>' . join($faker->paragraphs(2), '</p><p>') . '</p>'; // Comme pour les articles

                    $now = new \DateTime();
                    $days = $now->diff($article->getCreatedAt())->days; // On peut remplacer "$now" dirctement par "(new \DateTime())'"
                    $minimum = '-' . $days . 'days';

                    $comment->setAuthor($faker->firstName())
                            ->setContent($content)
                            ->setCreatedAt($faker->dateTimeBetween($minimum)) // On peut remplacer "$minimum" dirctement par "'-' . $days . 'days'"
                            ->setArticle($article);

                    $manager->persist($comment);
                }
            }
        }
        $manager->flush();
    }
}
