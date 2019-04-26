<?php
// tests/Service/TrickManagerTest.php
namespace App\Tests\Service;

use App\Entity\Trick;
use App\Entity\Media;
use App\Entity\TrickGroup;
use App\Service\TrickManager;
use Doctrine\ORM\EntityManager;
use PHPUnit\Framework\TestCase;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Validator\Validation;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockFileSessionStorage;
use Psr\Log\LoggerInterface;

// Doctrine data fixtures must be (re)loaded before executing
// these tests.
// WARNING: Loading fixture erases the database content!

class TrickManagerTest extends KernelTestCase
{
    /**
     * @var \Doctrine\ORM\EntityManager
     */
    private $entityManager;
    private $trickManager;
    private $validator;

    /**
     * {@inheritDoc}
     */
    protected function setUp()
    {
        $kernel = self::bootKernel();

        $this->entityManager = $kernel->getContainer()
            ->get('doctrine')
            ->getManager();

        $this->trickManager = $kernel->getContainer()
            ->get('test.App\Service\TrickManager');

        $this->validator = Validation::createValidator();
    }

    // ######################################################
    //   Testing saveTrickToDB(Trick $trick)
    // ######################################################

    /**
     * @dataProvider saveTrickToDBParams
     */
    public function testSaveTrickToDB(
        $name,
        $slug,
        $description,
        $creationDate,
        //$lastUpdateDate,
        //$trickGroup,
        $medias,
        $expectedDestPage
    ) {
        $trickGroup = $this->entityManager
            ->getRepository(TrickGroup::class)
            ->findByName('Grab')[0]
        ;

        $trick = new Trick();
        $trick->setName($name);
        $trick->setSlug($slug);
        $trick->setDescription($description);
        $trick->setCreationDate($creationDate);
        $trick->setTrickGroup($trickGroup);
        $trick->addMedia($medias);

        $result = $this->trickManager->saveTrickToDB($trick);

        $this->assertSame($expectedDestPage, $result['dest_page']);

        // Testing saved trick validity

        if ($result['is_successful']) {
            $savedTrick = $this->entityManager
            ->getRepository(Trick::class)
            ->findByName($name)[0];

            $errorsCount = $this->validator->validate($savedTrick)->count();

            $this->assertEquals(0, $errorsCount);
        }
    }

    public function saveTrickToDBParams()
    {
        $media01 = self::getTestMedia(1, false);
        $media02 = self::getTestMedia(2, false);
        $media03 = self::getTestMedia(3, false);

        $date = new \DateTime();

        // Can't access entityManager from data provider.
        // see https://stackoverflow.com/a/32708842/10980984
        /*$group = $this->entityManager
            ->getRepository(TrickGroup::class)
            ->searchByName('Grab')
        ;*/

        // The first 2 tricks creation must be successful.
        // The 3rd one must fail because of name & slug unicity constraint.
        return [
            ["Name 01", "name-01", "Descr. 01", $date,  $media01, "homepage"],
            ["Name 02", "name-02", "Descr. 02", $date,  $media02, "homepage"],
            ["Name 02", "name-02", "Descr. 03", $date,  $media03, "trick_new"]
          /*["Name 01", "name-01", "Descr. 01", $date,  $group, $media01, "homepage"],
            ["Name 02", "name-02", "Descr. 02", $date,  $group, $media02, "homepage"],
            ["Name 02", "name-01", "Descr. 03", $date,  $group, $media03, "trick_new"]
            */
        ];
    }

    // ######################################################
    //   Testing deleteTrickFromDB(Trick $trick)
    // ######################################################

    // At this point, there must be 13 tricks in the database :
    // - The 11 ones coming from the fixtures,
    // - The 2 created by the testSaveTrickToDB function above
    //   according to the saveTrickToDBParams data provider content.
    public function testDeleteTrickFromDB()
    {
        $trick = $this->entityManager
            ->getRepository(Trick::class)
            ->findByName('Indy')[0]
        ;

        $result = $this->trickManager->deleteTrickFromDB($trick);

        $remainingTricks = $this->entityManager
            ->getRepository(Trick::class)
            ->count([]);

        $this->assertSame("success", $result['msg_type']);
        $this->assertEquals(12, $remainingTricks);
    }

    /*
    // ######################################################
    //   Testing storeTrickInSession(Trick $trick)
    // ######################################################

    // Testing with sessions
    // See https://symfony.com/doc/current/components/http_foundation/session_testing.html
    public function testStoreTrickInSession()
    {
        $session = new Session(new MockFileSessionStorage());

        $trickGroup = $this->entityManager
            ->getRepository(TrickGroup::class)
            ->findByName('Grab')[0]
        ;

        $media = new Media();
        $media->setFileUrl("jpg");
        $media->setTitle("Title 01");
        $media->setAlt("Alt 01");
        $media->setFileType(0);
        $media->setDefaultCover(false);

        $trick = new Trick();
        $trick->setName("Name");
        $trick->setSlug("name");
        $trick->setDescription("This is the description.");
        $trick->setCreationDate(new \DateTime());
        $trick->setTrickGroup($trickGroup);
        $trick->addMedia($media);

        $this->trickManager->storeTrickInSession($trick);

        // Reading the trick and the trickGroup from the session.
        // Symfony remove() session method deletes a session attribute
        // and returns its value.
        $readTrick = unserialize($session->remove('trick'));

        $readTrickGroup = $this->entityManager
            ->merge(unserialize($session->remove('trickGroup')));

        $readTrick->setTrickGroup($readTrickGroup);

        $validator = Validation::createValidator();

        $errorsCount = $validator->validate($readTrick)->count();

        $this->assertEquals(0, $errorsCount);
    }
    */

    // ######################################################
    //   Testing storeTrickInSession(Trick $trick)
    //   and     readTrickFromSession()
    // ######################################################

    public function testStoreAndReadTrickInSession()
    {
        $trick = $this->entityManager
            ->getRepository(Trick::class)
            ->findByName("Backflip")[0]
        ;

        // Storing the trick in the session.
        $this->trickManager->storeTrickInSession($trick);

        // Reading the trick from the session.
        $readTrick = $this->trickManager->readTrickFromSession();

        $errorsCount = $this->validator->validate($readTrick)->count();

        $this->assertEquals(0, $errorsCount);
        $this->assertSame("Backflip", $readTrick->getName());
        $this->assertSame("Flip", $readTrick->getTrickGroup()->getName());
    }

    // ######################################################
    //   Testing deleteMediaFromDB(Media $media)
    // ######################################################

    public function testDeleteMediaFromDB()
    {
        // At this point, the "Backflip" trick has 4 medias.
        // Let's check this number before and after a media deletion.
        $trick = $this->entityManager
            ->getRepository(Trick::class)
            ->findByName("Backflip")[0]
        ;
        $mediasNbr = $trick->getMedias()->count();
        $this->assertEquals(4, $mediasNbr);

        $mediaToDelete = $trick->getMedias()[1];

        $result = $this->trickManager->deleteMediaFromDB($mediaToDelete);

        $remainingMedias = $trick->getMedias()->count();

        $this->assertSame("success", $result['msg_type']);
        $this->assertEquals(3, $remainingMedias);
    }

    // ######################################################
    //   Testing dropUploadedFileForAllMedias(Trick $trick)
    // ######################################################

    public function testDropUploadedFileForAllMedias()
    {
        $trick = self::getTestTrick(2);

        $this->trickManager->dropUploadedFileForAllMedias($trick);

        foreach ($trick->getMedias() as $media) {
            $this->assertSame(null, $media->getFile());
        }
    }

    // ######################################################
    //   Testing getTricksForIndexPage($limit, $offset)
    // ######################################################

    // At this point, there must be 12 tricks in the database :
    // + 11 coming from the fixtures,
    // + 2 created by the testSaveTrickToDB function above
    //   according to the saveTrickToDBParams data provider content.
    // - 1 deleted by the testDeleteTrickFromDB function above.
    /**
     * @dataProvider getTricksParams
     */
    public function testGetTricksForIndexPage(
        $limit,
        $offset,
        $tricksNbr
    ) {
        $tricksArray = $this->trickManager
            ->getTricksForIndexPage($limit, $offset);

        $this->assertEquals($tricksNbr, sizeof($tricksArray));

        //Tricks content validation
        $errorsCount = 0;

        foreach ($tricksArray as $trick) {
            $errorsCount += $this->validator->validate($trick)->count();
        }

        $this->assertEquals(0, $errorsCount);
    }

    public function getTricksParams()
    {
        return [
            [5,  0, 5], // Getting the first 5 tricks out of 12.
            [5,  5, 5], // Getting the 6th to 10th first tricks out of 12.
            [5, 10, 2]  // Getting the last 2 trick.
        ];
    }

    // ######################################################
    //   Testing getMediasArrayByTrickId($trickId)
    // ######################################################

    public function testGetMediasArrayByTrickId()
    {
        // At this point, the "Japan air" trick has 5 medias:
        // 2 images & 3 videos
        $trick = $this->entityManager
            ->getRepository(Trick::class)
            ->findByName("Japan air")[0]
        ;

        $mediasArray = $this->trickManager
            ->getMediasArrayByTrickId($trick->getId());

        $this->assertEquals(5, sizeOf($mediasArray));

        // Checking that the medias are correctly sorted:
        // Images first (with type 0) and then videos (type 1).
        $this->assertEquals(0, $mediasArray[0]->getFileType());
        $this->assertEquals(0, $mediasArray[1]->getFileType());
        $this->assertEquals(1, $mediasArray[2]->getFileType());
        $this->assertEquals(1, $mediasArray[3]->getFileType());
        $this->assertEquals(1, $mediasArray[4]->getFileType());

        // Medias content validation
        $errorsCount = 0;

        foreach ($mediasArray as $media) {
            $errorsCount += $this->validator->validate($media)->count();
        }

        $this->assertEquals(0, $errorsCount);
    }

    // ######################################################
    //   Testing getMediasCollectionByTrickId($trickId)
    // ######################################################

    public function testGetMediasCollectionByTrickId()
    {
        // At this point, the "Front flip" trick has 5 medias:
        // 3 images & 2 videos
        $trick = $this->entityManager
            ->getRepository(Trick::class)
            ->findByName("Front flip")[0]
        ;

        $mediasCollection = $this->trickManager
            ->getMediasCollectionByTrickId($trick->getId());

        $this->assertEquals(5, count($mediasCollection));

        // Checking that the medias are correctly sorted:
        // Images first (with type 0) and then videos (type 1).
        $this->assertEquals(0, $mediasCollection[0]->getFileType());
        $this->assertEquals(0, $mediasCollection[1]->getFileType());
        $this->assertEquals(0, $mediasCollection[2]->getFileType());
        $this->assertEquals(1, $mediasCollection[3]->getFileType());
        $this->assertEquals(1, $mediasCollection[4]->getFileType());

        // Medias content validation
        $mediasCollection->map(function ($media) {
            $errorsCount = $this->validator->validate($media)->count();
            $this->assertEquals(0, $errorsCount);
        });
    }

    // ######################################################
    //   Testing setTrickCover($trick, $newCoverMedia)
    //   and     getCoverImageByTrickId($trickId)
    // ######################################################

    public function testSetAndGetCoverImageByTrickId()
    {
        // We set a new cover image for the "Front flip" trick.
        $trick = $this->entityManager
            ->getRepository(Trick::class)
            ->findByName("Front flip")[0]
        ;

        // We create a new media with title "Title 132", and we don't
        // set it as cover image on creation.
        // This will be done by the setTrickCover method.
        $newMedia = self::getTestMedia(123, false);

        $trick->addMedia($newMedia);

        $this->trickManager->setTrickCover($trick, $newMedia);

        // We built the expected image filename, built with the created
        // media id followed by a "." (period) and ending with "jpg".
        $expectedCoverImageFile = $newMedia->getId() . "." . "jpg";

        // Then we get the cover image filename via the dedicated method.
        $readCoverImageFile = $this->trickManager
            ->getCoverImageByTrickId($trick->getId());

        $this->assertSame($expectedCoverImageFile, $readCoverImageFile);
    }

    // ######################################################
    //   Testing getMediasCollectionByTrickId($trickId)
    // ######################################################

    public function testGetGroupNameByTrickGroupId()
    {
        $trick = $this->entityManager
            ->getRepository(Trick::class)
            ->findByName("Backflip")[0]
        ;

        $trickGroupId = $trick->getTrickGroup()->getId();

        $groupName = $this->trickManager
            ->getGroupNameByTrickGroupId($trickGroupId);

        $this->assertSame("Flip", $groupName);
    }

    // ######################################################
    //   Testing unsetTrickCover($trick)
    // ######################################################

    public function testUnsetTrickCover()
    {
        // In a previous test, we set a new cover image for the "Front flip"
        // trick. We'll check it before after unsetting it.
        $trick = $this->entityManager
            ->getRepository(Trick::class)
            ->findByName("Front flip")[0]
        ;

        // Normally, we should get 1 element (if a cover is set)
        // or 0 (if no cover is set).
        // In our case, after the previous testSetAndGetCoverImageByTrickId
        // test, we must have 1.
        $coverArray = $this->entityManager
            ->getRepository(Media::class)
            ->findBy(['defaultCover' => 1, 'trick' => $trick])
        ;

        $this->assertEquals(1, sizeOf($coverArray));

        $this->trickManager->unsetTrickCover($trick);

        // Now we should have 0 default cover for this trick.
        $coverArray = $this->entityManager
            ->getRepository(Media::class)
            ->findBy(['defaultCover' => 1, 'trick' => $trick])
        ;

        $this->assertEquals(0, sizeOf($coverArray));
    }

    // ######################################################
    // Useful methods
    // ######################################################

    // Returns a Trick entity with as many madias as the
    // optional number $mediasNbr given as parameter.
    // Default medias number is 1.
    public function getTestTrick($mediasNbr = 1)
    {
        $trickGroup = $this->entityManager
            ->getRepository(TrickGroup::class)
            ->findByName('Grab')[0]
        ;

        $trick = new Trick();
        $trick->setName("Name");
        $trick->setSlug("name");
        $trick->setDescription("This is the description.");
        $trick->setCreationDate(new \DateTime());
        $trick->setTrickGroup($trickGroup);
        

        for ($med = 1; $med <= $mediasNbr; $med++) {
            $media = self::getTestMedia($med, false);
            $trick->addMedia($media);
        }

        return $trick;
    }

    // Returns a Media entity (of image type).
    // $number: Optional number added to the media title and alt properties.
    //          Default number is 1.
    // $isCover: Optional boolean that indicates if this image must be set
    //           as deflaut cover. Default is false.
    
    public function getTestMedia($number = 1, $isCover = false)
    {
        $uploadedFile = $this
            ->getMockBuilder('Symfony\Component\HttpFoundation\File\UploadedFile')
            ->enableOriginalConstructor()
            ->setConstructorArgs([tempnam(sys_get_temp_dir(), ''), 'dummy'])
            ->getMock();

        // See https://symfony.com/doc/current/testing/database.html
        // and method preUpload() of the Media.php entity.
        $uploadedFile->expects($this->any())
            ->method('guessExtension')
            ->willReturn('jpg');

        $media = new Media();
        $media->setFileUrl("jpg");
        $media->setTitle("Title " . $number);
        $media->setAlt("Alt " . $number);
        $media->setFileType(0);
        $media->setDefaultCover($isCover);
        $media->setFile($uploadedFile);

        return $media;
    }

    /**
     * {@inheritDoc}
     */
    protected function tearDown()
    {
        parent::tearDown();

        $this->entityManager->close();
        $this->entityManager = null; // avoid memory leaks
    }
}
