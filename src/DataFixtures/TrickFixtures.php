<?php
// src/DataFixtures/TrickFixtures.php
namespace App\DataFixtures;

use App\Entity\Media;
use App\Entity\Trick;
use App\Entity\TrickGroup;
use App\DataFixtures\Data\TricksData;
use App\DataFixtures\Data\ImagesData;
use App\DataFixtures\Data\VideosData;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class TrickFixtures extends Fixture
{
    const FIXTURES_IMAGES_PATH = __DIR__ . '/../../src/DataFixtures/images/';

    public function load(ObjectManager $manager)
    {
        $this->loadGroups($manager);
        $tricksDetailsArray = $this->initTricksDetails($manager);
        $imagesDetailsArray = $this->initImagesDetails();
        $videosDetailsArray = $this->initVideosDetails();
        $this->loadTricks(
            $tricksDetailsArray,
            $imagesDetailsArray,
            $videosDetailsArray,
            $manager
        );
    }

    public function loadGroups(ObjectManager $manager)
    {
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
    }

    public function initTricksDetails($manager)
    {
        $tricksDetailsArray = (new TricksData)->getData($manager);

        return $tricksDetailsArray;
    }

    public function initImagesDetails()
    {
        $imagesDetailsArray = (new ImagesData)->getData();

        return $imagesDetailsArray;
    }

    public function initVideosDetails()
    {
        $videosDetailsArray = (new VideosData)->getData();

        return $videosDetailsArray;
    }

    public function loadTricks(
        $tricksDetailsArray,
        $imagesDetailsArray,
        $videosDetailsArray,
        ObjectManager $manager
    ) {
        self::saveTricks($tricksDetailsArray, $manager);

        $tricksArray = $manager->getRepository(Trick::class)->findAll();

        foreach ($tricksArray as $trick) {
            // For the newly created trick, we create the related video medias,
            // based on the trick's slug.
            $videosArray = $videosDetailsArray[$trick->getSlug()];

            self::saveVideos($videosArray, $trick, $manager);

            // And we also create the related images, based on the trick's slug
            $imagesArray = $imagesDetailsArray[$trick->getSlug()];

            self::saveImages($imagesArray, $trick, $manager);
        }

        $manager->flush();
    }

    public function saveTricks($tricksDetailsArray, $manager)
    {
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
    }

    public function saveVideos($videosArray, $trick, $manager)
    {
        foreach ($videosArray as $video) {
            $media = new Media();
            $media->setFileUrl($video['file_url']);
            $media->setTitle($video['title']);
            $media->setAlt($video['alt']);
            $media->setFileType($video['file_type']);
            $media->setTrick($trick);
            $manager->persist($media);
        }
    }

    public function saveImages($imagesArray, $trick, $manager)
    {
        foreach ($imagesArray as $image) {
            $media = new Media();
            $media->setFileUrl($image['file_url']);
            $media->setTitle($image['title']);
            $media->setAlt($image['alt']);
            $media->setFileType($image['file_type']);
            $media->setTrick($trick);
            $media->setDefaultCover($image['default_cover']);
            $manager->persist($media);

            // Now we make a copy of the image and will upload this copy
            // which will be automatically deleted during the process.
            // The name of an image is the id of the media, plus the
            // extension stored in the fileUrl attribute.

            $fileName = $image['file_name'] . '.' . $media->getFileUrl();
            $fileCopyName = $image['file_name'] . '-copy.' . $media->getFileUrl();

            copy(self::FIXTURES_IMAGES_PATH . $trick->getSlug() . '/'
                . $fileName, self::FIXTURES_IMAGES_PATH
                . $trick->getSlug() . '/' . $fileCopyName);

            $file = new UploadedFile(self::FIXTURES_IMAGES_PATH . $trick->getSlug()
                . '/' . $fileCopyName, 'Image1', null, null, null, true);

            $media->setFile($file);
            $manager->persist($media);
        }
    }
}
