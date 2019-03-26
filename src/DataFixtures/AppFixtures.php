<?php

namespace App\DataFixtures;

use App\Entity\User;
use App\Entity\Media;
use App\Entity\Message;
use App\Entity\Trick;
use App\Entity\TrickGroup;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class AppFixtures extends Fixture
{
    public function __construct(
        LoggerInterface $logger
    ) {
        $this->logger = $logger;
    }

    public function load(ObjectManager $manager)
    {

        $user = new User();

        $user->setUsername('eric');
        $user->setEmail('eric.codron@gmail.com');
        $user->setPassword('$2y$13$/W5ATAIIrJTUU9GoSPAQG.emjMEYEDQlL9T811y.KyMWsLWmhMjW2');

        $manager->persist($user);
        $manager->flush();

        for ($i=0; $i<10; $i++) {
            $message = new Message();

            $message->setContent("Ceci est le " . strval($i+1) . "° message du forum.");
            $message->setDate(new \Datetime());
            $message->setUser($user);
            //$message->setTrick();

            $manager->persist($message);
        }

        $manager->flush();

        $groupsNameArray = [
            'Grab',
            'Rotation',
            'Flip',
            'Rotation désaxée',
            'Slide',
            'One foot trick',
            'Old School',
        ];

        foreach ($groupsNameArray as $groupName) {
            $group = new TrickGroup();
            $group->setName($groupName);

            $manager->persist($group);
        }

        $manager->flush();

        $groupsArray = $manager->getRepository(TrickGroup::class)->findAll();

        //$this->logger->info('> > > > > > IN LOAD  < < < < < <'.array_rand($groupsArray, 1));

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

        //$this->logger->info('> > > > > > IN LOAD  < < < < < <'.serialize($tricksDetailsArray[0]['trickGroup']));

        $imagesDetailsArray = [
            'mute' => [
                [
                    'file_name' => 'mute-1-1920x1080',
                    'file_url' => 'jpg',
                    'title' => 'How to do a Mute Grab',
                    'alt' => 'How to do a Mute Grab',
                    'file_type' => 0,
                    'default_cover' => 1,
                ],
                [
                    'file_name' => 'mute-2-1920x1080',
                    'file_url' => 'jpg',
                    'title' => 'Mute Grab',
                    'alt' => 'Mute Grab',
                    'file_type' => 0,
                    'default_cover' => 0,
                ],
                [
                    'file_name' => 'mute-3-1920x1080',
                    'file_url' => 'jpg',
                    'title' => 'Mute Grab close-up',
                    'alt' => 'Mute Grab close-up',
                    'file_type' => 0,
                    'default_cover' => 0,
                ],
                [
                    'file_name' => 'mute-4-1920x1080',
                    'file_url' => 'jpg',
                    'title' => 'Mute Grab',
                    'alt' => 'Mute Grab',
                    'file_type' => 0,
                    'default_cover' => 0,
                ],
            ],
            'sad' => [
                [
                    'file_name' => 'sad-1-1920x1080',
                    'file_url' => 'jpg',
                    'title' => 'How to do a Mute Grab',
                    'alt' => 'How to do a Mute Grab',
                    'file_type' => 0,
                    'default_cover' => 1,
                ],
                [
                    'file_name' => 'sad-2-1920x1080',
                    'file_url' => 'jpg',
                    'title' => 'Mute Grab',
                    'alt' => 'Mute Grab',
                    'file_type' => 0,
                    'default_cover' => 0,
                ],
            ],
            'indy' => [
                [
                    'file_name' => 'indy-1-1920x1080',
                    'file_url' => 'jpg',
                    'title' => 'Indy Grab',
                    'alt' => 'Indy Grab',
                    'file_type' => 0,
                    'default_cover' => 0,
                ],
                [
                    'file_name' => 'indy-2-1920x1080',
                    'file_url' => 'jpg',
                    'title' => 'Indy close-up',
                    'alt' => 'Indy close-up',
                    'file_type' => 0,
                    'default_cover' => 1,
                ],
                [
                    'file_name' => 'indy-3-1920x1080',
                    'file_url' => 'jpg',
                    'title' => 'Indy upside-down',
                    'alt' => 'Indy upside-down',
                    'file_type' => 0,
                    'default_cover' => 0,
                ],
            ],
            'japan-air' => [
                [
                    'file_name' => 'japan-air-1-1920x1080',
                    'file_url' => 'jpg',
                    'title' => 'Japan Air Grab',
                    'alt' => 'Japan Air Grab',
                    'file_type' => 0,
                    'default_cover' => 1,
                ],
                [
                    'file_name' => 'japan-air-2-1920x1080',
                    'file_url' => 'jpg',
                    'title' => 'Japan Air',
                    'alt' => 'Japan Air',
                    'file_type' => 0,
                    'default_cover' => 0,
                ],
            ],
            'backside-triple-cork-1440' => [
                [
                    'file_name' => 'backside-triple-cork-1440-1-1920x1080',
                    'file_url' => 'jpg',
                    'title' => 'Backside Triple Cork 1440',
                    'alt' => 'Backside Triple Cork 1440',
                    'file_type' => 0,
                    'default_cover' => 1,
                ],
            ],
            'front-flip' => [
                [
                    'file_name' => 'front-flip-1-1920x1080',
                    'file_url' => 'jpg',
                    'title' => 'Front flip detailed',
                    'alt' => 'Front flip detailed',
                    'file_type' => 0,
                    'default_cover' => 0,
                ],
                [
                    'file_name' => 'front-flip-2-1920x1080',
                    'file_url' => 'jpg',
                    'title' => 'Front flip',
                    'alt' => 'Front flip',
                    'file_type' => 0,
                    'default_cover' => 1,
                ],
                [
                    'file_name' => 'front-flip-3-1920x1080',
                    'file_url' => 'jpg',
                    'title' => 'Front flip detailed',
                    'alt' => 'Front flip detailed',
                    'file_type' => 0,
                    'default_cover' => 0,
                ],
            ],
            'backflip' => [
                [
                    'file_name' => 'backflip-1-1920x1080',
                    'file_url' => 'jpg',
                    'title' => 'Backflip',
                    'alt' => 'Backflip',
                    'file_type' => 0,
                    'default_cover' => 1,
                ],
                [
                    'file_name' => 'backflip-2-1920x1080',
                    'file_url' => 'jpg',
                    'title' => 'Backflip detailed',
                    'alt' => 'Backflip detailed',
                    'file_type' => 0,
                    'default_cover' => 0,
                ],
            ],
            'nose-slide' => [
                [
                    'file_name' => 'nose-slide-1-1920x1080',
                    'file_url' => 'jpg',
                    'title' => 'Nose slide',
                    'alt' => 'Nose slide',
                    'file_type' => 0,
                    'default_cover' => 1,
                ],
                [
                    'file_name' => 'nose-slide-2-1920x1080',
                    'file_url' => 'jpg',
                    'title' => 'Nose slide',
                    'alt' => 'Nose slide',
                    'file_type' => 0,
                    'default_cover' => 0,
                ],
            ],
            'tail-slide' => [
                [
                    'file_name' => 'tail-slide-1-1920x1080',
                    'file_url' => 'jpg',
                    'title' => 'Tail slide',
                    'alt' => 'Tail slide',
                    'file_type' => 0,
                    'default_cover' => 0,
                ],
                [
                    'file_name' => 'tail-slide-2-1920x1080',
                    'file_url' => 'jpg',
                    'title' => 'Tail slide',
                    'alt' => 'Tail slide',
                    'file_type' => 0,
                    'default_cover' => 1,
                ],
            ],
            'method-air' => [
                [
                    'file_name' => 'method-air-1-1920x1080',
                    'file_url' => 'jpg',
                    'title' => 'Method Air',
                    'alt' => 'Method Air',
                    'file_type' => 0,
                    'default_cover' => 1,
                ],
                [
                    'file_name' => 'method-air-2-1920x1080',
                    'file_url' => 'jpg',
                    'title' => 'Method Air',
                    'alt' => 'Method Air',
                    'file_type' => 0,
                    'default_cover' => 0,
                ],
            ],
            'stalefish' => [
                [
                    'file_name' => 'stalefish-1-1920x1080',
                    'file_url' => 'jpg',
                    'title' => 'Stalefish',
                    'alt' => 'Stalefish',
                    'file_type' => 0,
                    'default_cover' => 1,
                ],
                [
                    'file_name' => 'stalefish-2-1920x1080',
                    'file_url' => 'jpg',
                    'title' => 'Stalefish',
                    'alt' => 'Stalefish',
                    'file_type' => 0,
                    'default_cover' => 0,
                ],
            ],        ];

        $videosDetailsArray = [
            'mute' => [
                [
                    'file_url' => '<iframe width="560" height="315" src="https://www.youtube.com/embed/k6aOWf0LDcQ" frameborder="0" allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>',
                    'title' => 'How to do a Mute Grab',
                    'alt' => 'How to do a Mute Grab video',
                    'file_type' => 1,
                ],
                [
                    'file_url' => '<iframe width="560" height="315" src="https://www.youtube.com/embed/J-ODWe9wm0g" frameborder="0" allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>',
                    'title' => '180 Mute Grab',
                    'alt' => '180 Mute Grab video',
                    'file_type' => 1,
                ],
            ],
            'sad' => [
                [
                    'file_url' => '<iframe width="560" height="315" src="https://www.youtube.com/embed/KEdFwJ4SWq4" frameborder="0" allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>',
                    'title' => 'How to Grab Sad Air',
                    'alt' => 'How to Grab Sad Air video',
                    'file_type' => 1,
                ],
            ],
            'indy' => [
                [
                    'file_url' => '<iframe width="560" height="315" src="https://www.youtube.com/embed/iKkhKekZNQ8" frameborder="0" allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>',
                    'title' => 'How to Indy Grab',
                    'alt' => 'How to Indy Grab video',
                    'file_type' => 1,
                ],
                [
                    'file_url' => '<iframe width="560" height="315" src="https://www.youtube.com/embed/6QsLhWzXGu0" frameborder="0" allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>',
                    'title' => 'Indy Grab',
                    'alt' => 'Indy Grab video',
                    'file_type' => 1,
                ],
                [
                    'file_url' => '<iframe width="560" height="315" src="https://www.youtube.com/embed/-iLTNuWKmeY" frameborder="0" allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>',
                    'title' => 'Snowboard Trick Indy',
                    'alt' => 'Snowboard Trick Indy video',
                    'file_type' => 1,
                ],
                [
                    'file_url' => '<iframe width="560" height="315" src="https://www.youtube.com/embed/bkUDlpMfiv4" frameborder="0" allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>',
                    'title' => 'Indy Grab Trick Snowboarding Slowmotion',
                    'alt' => 'Indy Grab Trick Snowboarding Slowmotion video',
                    'file_type' => 1,
                ],
                [
                    'file_url' => '<iframe width="560" height="315" src="https://www.youtube.com/embed/aiDtbEJNamQ" frameborder="0" allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>',
                    'title' => 'Snowboard straight air indy',
                    'alt' => 'Snowboard straight air indy video',
                    'file_type' => 1,
                ],
            ],
            'japan-air' => [
                [
                    'file_url' => '<iframe width="560" height="315" src="https://www.youtube.com/embed/jH76540wSqU" frameborder="0" allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>',
                    'title' => 'How to Grab Japan',
                    'alt' => 'How to Grab Japan video',
                    'file_type' => 1,
                ],
                [
                    'file_url' => '<iframe width="560" height="315" src="https://www.youtube.com/embed/I7N45iRPrhw" frameborder="0" allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>',
                    'title' => 'How To Japan',
                    'alt' => 'How To Japan video',
                    'file_type' => 1,
                ],
                [
                    'file_url' => '<iframe width="560" height="315" src="https://www.youtube.com/embed/CzDjM7h_Fwo" frameborder="0" allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>',
                    'title' => 'How To Japan Grab',
                    'alt' => 'How To Japan Grab video',
                    'file_type' => 1,
                ],
            ],
            'backside-triple-cork-1440' => [
                [
                    'file_url' => '<iframe width="560" height="315" src="https://www.youtube.com/embed/cWWk4Vo3buU" frameborder="0" allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>',
                    'title' => 'Backside Triple Cork 1440',
                    'alt' => 'Backside Triple Cork 1440 video',
                    'file_type' => 1,
                ],
                [
                    'file_url' => '<iframe width="560" height="315" src="https://www.youtube.com/embed/4ekuyDYBrz4" frameborder="0" allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>',
                    'title' => 'Mark McMorris Triple Cork 1440 Deconstructed',
                    'alt' => 'Mark McMorris Triple Cork 1440 Deconstructed video',
                    'file_type' => 1,
                ],
            ],
            'front-flip' => [
                [
                    'file_url' => '<iframe width="560" height="315" src="https://www.youtube.com/embed/gMfmjr-kuOg" frameborder="0" allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>',
                    'title' => 'Front flip',
                    'alt' => 'Front flip video',
                    'file_type' => 1,
                ],
                [
                    'file_url' => '<iframe width="560" height="315" src="https://www.youtube.com/embed/64flAiRA0i0" frameborder="0" allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>',
                    'title' => 'Tame Dog (Front Flip)',
                    'alt' => 'Tame Dog (Front Flip) video',
                    'file_type' => 1,
                ],
            ],
            'backflip' => [
                [
                    'file_url' => '<iframe width="560" height="315" src="https://www.youtube.com/embed/Yz4brafqk5A" frameborder="0" allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>',
                    'title' => 'Backflip',
                    'alt' => 'Backflip video',
                    'file_type' => 1,
                ],
                [
                    'file_url' => '<iframe width="560" height="315" src="https://www.youtube.com/embed/W853WVF5AqI" frameborder="0" allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>',
                    'title' => 'Le Backflip en détail',
                    'alt' => 'Le Backflip en détail video',
                    'file_type' => 1,
                ],
            ],
            'nose-slide' => [
                [
                    'file_url' => '<iframe width="560" height="315" src="https://www.youtube.com/embed/oAK9mK7wWvw" frameborder="0" allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>',
                    'title' => 'Le Nose slide en détail',
                    'alt' => 'Le Nose slide en détail video',
                    'file_type' => 1,
                ],
                [
                    'file_url' => '<iframe width="560" height="315" src="https://www.youtube.com/embed/7AB0FZWyrGQ" frameborder="0" allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>',
                    'title' => 'Nose slide',
                    'alt' => 'Nose slide video',
                    'file_type' => 1,
                ],
            ],
            'tail-slide' => [
                [
                    'file_url' => '<iframe width="560" height="315" src="https://www.youtube.com/embed/PxYKuE_SZec" frameborder="0" allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>',
                    'title' => 'Tailslide 270 on snow',
                    'alt' => 'Tailslide 270 on snow video',
                    'file_type' => 1,
                ],
                [
                    'file_url' => '<iframe width="560" height="315" src="https://www.youtube.com/embed/KqSi94FT7EE" frameborder="0" allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>',
                    'title' => 'Nose & Tail Slide',
                    'alt' => 'Nose & Tail Slide video',
                    'file_type' => 1,
                ],
            ],
            'method-air' => [
                [
                    'file_url' => '<iframe width="560" height="315" src="https://www.youtube.com/embed/_Cfssjuv0Zg" frameborder="0" allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>',
                    'title' => 'Method Air',
                    'alt' => 'Method Air video',
                    'file_type' => 1,
                ],
                [
                    'file_url' => '<iframe src="http://www.zapiks.fr/index.php?action=playerIframe&media_id=99159&width=640&height=360&autoStart=false&language=fr" style="position : absolute; top : 0; left : 0; width : 100%; height : 100%;" frameborder="0" scrolling="no" allowfullscreen></iframe>',
                    'title' => 'Method Air au ralenti',
                    'alt' => 'Method Air au ralenti video',
                    'file_type' => 1,
                ],
            ],
            'stalefish' => [
                [
                    'file_url' => '<iframe width="560" height="315" src="https://www.youtube.com/embed/rDzm-lkFAI4" frameborder="0" allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>',
                    'title' => 'Stalefish Grab on Snow',
                    'alt' => 'Stalefish Grab on Snow video',
                    'file_type' => 1,
                ],
                [
                    'file_url' => '<iframe width="560" height="315" src="https://www.youtube.com/embed/0Oez89EoE_c" frameborder="0" allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>',
                    'title' => 'Stalefish',
                    'alt' => 'Stalefish video',
                    'file_type' => 1,
                ],
            ],
        ];

        foreach ($tricksDetailsArray as $trickDetails) {
            $trick = new Trick();
            $trick->setName($trickDetails['name']);
            $trick->setSlug($trickDetails['slug']);
            $trick->setDescription($trickDetails['description']);
            $trick->setCreationDate($trickDetails['creationDate']);
            $trick->setTrickGroup($trickDetails['trickGroup'][0]);
            $manager->persist($trick);
        }
        $manager->flush();

        $tricksArray = $manager->getRepository(Trick::class)->findAll();

        foreach ($tricksArray as $trick) {
            // For the newly created trick, we create the related video medias,
            // based on the trick's slug.
            $videosArray = $videosDetailsArray[$trick->getSlug()];

            foreach ($videosArray as $video) {
                $media = new Media();
                $media->setFileUrl($video['file_url']);
                $media->setTitle($video['title']);
                $media->setAlt($video['alt']);
                $media->setFileType($video['file_type']);
                $media->setTrick($trick);
                $manager->persist($media);
            }

            // And we also create the related images, based on the trick's slug.
            $imagesArray = $imagesDetailsArray[$trick->getSlug()];

            foreach ($imagesArray as $image) {
                $media = new Media();
                $media->setFileUrl($image['file_url']);
                $media->setTitle($image['title']);
                $media->setAlt($image['alt']);
                $media->setFileType($image['file_type']);
                $media->setTrick($trick);
                $media->setDefaultCover($image['default_cover']);
                $manager->persist($media);

                //$manager->flush();

                // Now we make a copy of the image and will upload this copy
                // which will be automatically deleted during the process.
                // The name of an image is the id of the media, plus the extension stored in the fileUrl attribute.

                $fileName = $image['file_name'] . '.' . $media->getFileUrl();
                $fileCopyName = $image['file_name'] . '-copy.' . $media->getFileUrl();

                copy($media->getFixturesPath() . $trick->getSlug() . '/' . $fileName, $media->getFixturesPath() . $trick->getSlug() . '/' . $fileCopyName);

                $file = new UploadedFile($media->getFixturesPath() . $trick->getSlug() . '/' . $fileCopyName, 'Image1', null, null, null, true);

                $media->setFile($file);
                $manager->persist($media);
            }
        }

        $manager->flush();
    }
}
