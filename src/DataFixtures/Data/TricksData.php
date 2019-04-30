<?php
// src/DataFixtures/Data/TricksData.php

namespace App\DataFixtures\Data;

use App\Entity\TrickGroup;
use Doctrine\Common\Persistence\ObjectManager;

class TricksData
{
    private $manager;
    
    public function getData(ObjectManager $manager)
    {
        $tricksDetailsArray = [
            [
                'name' => 'Mute',
                'slug' => 'mute',
                'description' => 'Saisie de la carre frontside de la planche entre les deux pieds avec la main avant.',
                'creationDate' => new \Datetime(),
                'trickGroup' => $manager->getRepository(TrickGroup::class)->findByName('Grab'),
            ],
            [
                'name' => 'Sad',
                'slug' => 'sad',
                'description' => 'Saisie de la carre backside de la planche, entre les deux pieds, avec la main avant.',
                'creationDate' => new \Datetime(),
                'trickGroup' => $manager->getRepository(TrickGroup::class)->findByName('Grab'),
            ],
            [
                'name' => 'Indy',
                'slug' => 'indy',
                'description' => 'Saisie de la carre frontside de la planche, entre les deux pieds, avec la main arrière.',
                'creationDate' => new \Datetime(),
                'trickGroup' => $manager->getRepository(TrickGroup::class)->findByName('Grab'),
            ],
            [
                'name' => 'Japan air',
                'slug' => 'japan-air',
                'description' => "Saisie de l'avant de la planche, avec la main avant, du côté de la carre frontside.",
                'creationDate' => new \Datetime(),
                'trickGroup' => $manager->getRepository(TrickGroup::class)->findByName('Grab'),
            ],
            [
                'name' => 'Backside Triple Cork 1440',
                'slug' => 'backside-triple-cork-1440',
                'description' => "Un cork est une rotation horizontale plus ou moins désaxée, selon un mouvement d'épaules effectué juste au moment du saut.",
                'creationDate' => new \Datetime(),
                'trickGroup' => $manager->getRepository(TrickGroup::class)->findByName('Rotation désaxée'),
            ],
            [
                'name' => 'Front flip',
                'slug' => 'front-flip',
                'description' => "Un flip est une rotation verticale. On distingue les front flips, rotations en avant, et les back flips, rotations en arrière.

Il est possible de faire plusieurs flips à la suite, et d'ajouter un grab à la rotation.

Les flips agrémentés d'une vrille existent aussi (Mac Twist, Hakon Flip, ...), mais de manière beaucoup plus rare, et se confondent souvent avec certaines rotations horizontales désaxées.

Néanmoins, en dépit de la difficulté technique relative d'une telle figure, le danger de retomber sur la tête ou la nuque est réel et conduit certaines stations de ski à interdire de telles figures dans ses snowparks.",
                'creationDate' => new \Datetime(),
                'trickGroup' => $manager->getRepository(TrickGroup::class)->findByName('Flip'),
            ],
            [
                'name' => 'Backflip',
                'slug' => 'backflip',
                'description' => "Un flip est une rotation verticale. Les backflips sont des rotations en arrière.

Il est possible de faire plusieurs flips à la suite, et d'ajouter un grab à la rotation.",
                'creationDate' => new \Datetime(),
                'trickGroup' => $manager->getRepository(TrickGroup::class)->findByName('Flip'),
            ],
            [
                'name' => 'Nose slide',
                'slug' => 'nose-slide',
                'description' => "Un slide consiste à glisser sur une barre de slide. Le slide se fait soit avec la planche dans l'axe de la barre, soit perpendiculaire, soit plus ou moins désaxé.

On peut slider avec la planche centrée par rapport à la barre (celle-ci se situe approximativement au-dessous des pieds du rideur), mais aussi en nose slide, c'est-à-dire l'avant de la planche sur la barre.",
                'creationDate' => new \Datetime(),
                'trickGroup' => $manager->getRepository(TrickGroup::class)->findByName('Slide'),
            ],
            [
                'name' => 'Tail slide',
                'slug' => 'tail-slide',
                'description' => "Un slide consiste à glisser sur une barre de slide. Le slide se fait soit avec la planche dans l'axe de la barre, soit perpendiculaire, soit plus ou moins désaxé.

On peut slider avec la planche centrée par rapport à la barre (celle-ci se situe approximativement au-dessous des pieds du rideur), mais aussi en tail slide, l'arrière de la planche sur la barre.",
                'creationDate' => new \Datetime(),
                'trickGroup' => $manager->getRepository(TrickGroup::class)->findByName('Slide'),
            ],
            [
                'name' => 'Method Air',
                'slug' => 'method-air',
                'description' => "Cette figure – qui consiste à attraper sa planche d'une main et le tourner perpendiculairement au sol – est un classique \"old school\". Il n'empêche qu'il est indémodable, avec de vrais ambassadeurs comme Jamie Lynn ou la star Terje Haakonsen. En 2007, ce dernier a même battu le record du monde du \"air\" le plus haut en s'élevant à 9,8 mètres au-dessus du kick (sommet d'un mur d'une rampe ou autre structure de saut).",
                'creationDate' => new \Datetime(),
                'trickGroup' => $manager->getRepository(TrickGroup::class)->findByName('Old School'),
            ],
            [
                'name' => 'Stalefish',
                'slug' => 'stalefish',
                'description' => "Saisie de la carre backside de la planche entre les deux pieds avec la main arrière.",
                'creationDate' => new \Datetime(),
                'trickGroup' => $manager->getRepository(TrickGroup::class)->findByName('Grab'),
            ],
        ];

        return $tricksDetailsArray;
    }
}
