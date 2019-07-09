<?php

namespace App\DataFixtures;

use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use App\Entity\BillingUser;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\Executor\ORMExecutor;
use Doctrine\Common\DataFixtures\Loader;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpFoundation\Response;

class AppFixtures extends Fixture
{
    private $passwordEncoder;
    public function __construct(UserPasswordEncoderInterface $passwordEncoder)
    {
        $this->passwordEncoder = $passwordEncoder;
    }

   /* public function load(ObjectManager $manager)
    {
        $emails = ['testUser@test.com', 'testUser2@test.com'];
        $roles = [['ROLE_USER'], ['ROLE_SUPER_ADMIN']];
        $passwords = ['password', 'password'];
        for ($i = 0; $i < 2; $i++) {
            $billingUser = new BillingUser();
            $billingUser->setEmail($emails[$i]);
            $billingUser->setRoles($roles[$i]);
            $billingUser->setPassword($this->passwordEncoder->encodePassword($billingUser, $passwords[$i]));
            $manager->persist($billingUser);
        }
        $manager->flush();
    }*/
   public function load(ObjectManager $manager)
   {
       $email = "testadmin@gmail.com";
       $roles = [['ROLE_USER'],['ROLE_SUPER_ADMIN']];
       $password = 'aaaaaa';
       $billingUser = new BillingUser();
       $billingUser->setEmail($email);
       $billingUser->setBalance(0.0);
       for($i=0;$i<2;$i++)
       {
           $billingUser->setRoles($roles[$i]);
       }
       $billingUser->setPassword($this->passwordEncoder->encodePassword($billingUser, $password));
       $manager->persist($billingUser);
       $manager->flush();

   }
}
