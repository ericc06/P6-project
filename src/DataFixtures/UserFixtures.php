<?php
// src/DataFixtures/UserFixtures.php
namespace App\DataFixtures;

use App\Entity\Trick;
use App\Entity\Message;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;

class UserFixtures extends Fixture
{
    public function load(ObjectManager $manager)
    {
        $user = new User();

        $user->setUsername('eric');
        $user->setEmail('eric.codron@gmail.com');
        $user->setPassword('$2y$13$/W5ATAIIrJTUU9GoSPAQG.emjMEYEDQlL9T811y.KyMWsLWmhMjW2');
        $user->setRoles(array('ROLE_USER'));
        $user->setIsActiveAccount(true);

        $manager->persist($user);
        $manager->flush();

        $trick = $manager->getRepository(Trick::class)
            ->findByName("Backflip")[0]
        ;

        for ($i = 0; $i < 15; $i++) {
            $message = new Message();

            $message->setContent("Ceci est le " . strval($i + 1) . "° message du forum.");
            $message->setDate(new \Datetime());
            $message->setUser($user);
            $message->setTrick($trick);

            $manager->persist($message);
        }

        $manager->flush();
    }
}
