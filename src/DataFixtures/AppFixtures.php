<?php

namespace App\DataFixtures;

use App\Entity\Media;
use App\Entity\Trick;
use App\Entity\TrickGroup;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class AppFixtures extends Fixture
{
    private $webDirectory;
    private $imageLocation;

    public function __construct(
        LoggerInterface $logger,
        $webDirectory
    ) {
        $this->logger = $logger;
        $this->webDirectory = $webDirectory;
        $this->imageLocation = $webDirectory . 'img/config/';
    }

    public function load(ObjectManager $manager)
    {

        $groupsNameArray = [
            'Grab',
            'Rotation',
            'Flip',
            'Rotation désaxées',
            'Slide',
            'One foot trick',
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
                    'default_cover' => 1,
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
        ];

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
        ];

        foreach ($tricksDetailsArray as $trickDetails) {
            $this->logger->info('> > > > > > AAAA IN LOAD  < < < < < <' . $trickDetails['slug']);

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
                $media->setTrick($trick); /// CA MARCHE CA ????
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
                $media->setTrick($trick); /// CA MARCHE CA ????
                $manager->persist($media);

                //$manager->flush();

                // Now we make a copy of the image.
                // The name of an image is the id of the media, plus the extension stored in the fileUrl attribute.

                $fileName = $image['file_name'] . '.' . $media->getFileUrl(); /// CA MARCHE CA ????
                $fileCopyName = $image['file_name'] . '-copy.' . $media->getFileUrl(); /// CA MARCHE CA ????

                $this->logger->info('> > > > > > IN LOAD  < < < < < <' . $fileName);
                copy($media->getFixturesPath() . $trick->getSlug() . '/' . $fileName, $media->getFixturesPath() . $trick->getSlug() . '/' . $fileCopyName);

                $file = new UploadedFile($media->getFixturesPath() . $trick->getSlug() . '/' . $fileCopyName, 'Image1', null, null, null, true);

                $media->setFile($file);
                $manager->persist($media);
            }
        }

        $manager->flush();
    }
}
