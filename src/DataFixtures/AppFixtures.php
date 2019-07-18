<?php

namespace App\DataFixtures;

use App\Entity\Course;
use App\Service\PaymentService;
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

        $this->createUsers($manager);

       //$this->createAdminUser($manager);
       //$this->createRealCourses($manager);

   }
   public function createAdminUser(ObjectManager $manager)
   {
       $email = "testadmin3@gmail.com";
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
       $paymentService = new PaymentService($manager);
       $paymentService->increaseBalance($billingUser,$_ENV['INCOME']);
       $manager->persist($billingUser);
       $manager->flush();
   }
   public function createRealCourses(ObjectManager $manager)
   {
        $courses = ['kurs-programmirovaniya-na-c','kurs-veb-razrabotki-dlya-novichkov3','kurs-veb-razrabotki-dlya-novichkov', 'free-kurs'];
        $prices = [15,20,10,0];
        $types = [Course::TYPE_FULL,Course::TYPE_RENT,Course::TYPE_RENT,Course::TYPE_FREE];
        for($i=0; $i < 4; $i++)
        {
            $course = new Course();
            $course->setSlug($courses[$i]);
            $course->setPrice($prices[$i]);
            $course->setType($types[$i]);
            $manager->persist($course);
        }
        $manager->flush();
   }
   public function createUsers(ObjectManager $manager)
   {
       $emails = ['testUser@test.com', 'testUser2@test.com'];
       $roles = [['ROLE_USER'], ['ROLE_SUPER_ADMIN']];
       $passwords = ['password', 'password'];
       $paymentService = new PaymentService($manager);
       for ($i = 0; $i < 2; $i++) {
           $billingUser = new BillingUser();
           $billingUser->setEmail($emails[$i]);
           $billingUser->setRoles($roles[$i]);
           $billingUser->setBalance(0.0);
           $billingUser->setPassword($this->passwordEncoder->encodePassword($billingUser, $passwords[$i]));
           $paymentService->increaseBalance($billingUser,$_ENV['INCOME']);
           $manager->persist($billingUser);
       }
       $nullBalanceUser = new BillingUser();
       $nullBalanceUser->setEmail("nullbalance@gmail.com");
       $nullBalanceUser->setRoles(['ROLE_USER']);
       $nullBalanceUser->setBalance(0.0);
       $nullBalanceUser->setPassword($this->passwordEncoder->encodePassword($nullBalanceUser, "aaaaaa"));
       $manager->persist($nullBalanceUser);
       $manager->flush();
       $this->createCourses($manager);
       $this->buyCourses($manager);
   }
   public function createCourses(ObjectManager $manager)
   {
       $coursesSlugs = ['test-kurs-arenda','test-kurs-pokupka', 'test-kurs-snova', 'i-eto-test-kurs','test-kurs'];
       $coursesPrices = [10.0, 20.0, 3.0, 20.0,5.0];
       $coursesTypes = ['rent', 'full', 'rent', 'rent','free'];
       for ($i = 0; $i < 5; $i++) {
           $course = new Course();
           $course->setSlug($coursesSlugs[$i]);
           $course->setPrice($coursesPrices[$i]);
           switch ($coursesTypes[$i]) {
               case 'rent':
                   $course->setType(Course::TYPE_RENT);
                   break;
               case 'full':
                   $course->setType(Course::TYPE_FULL);
                   break;
               case 'free':
                   $course->setType(Course::TYPE_FREE);
                   break;
           }
           $manager->persist($course);
       }
       $manager->flush();
   }
   public function buyCourses(ObjectManager $manager)
   {
       $emails = ['testUser@test.com', 'testUser2@test.com'];
       $paymentService = new PaymentService($manager);
       $coursesSlugs = ['test-kurs-arenda','test-kurs-pokupka', 'test-kurs-snova', 'i-eto-test-kurs'];
       foreach ($emails as $user) {
           foreach($coursesSlugs as $course) {
               $courseByRepo = $manager->getRepository(Course::class)->findOneBy(['slug'=>$course]);
               $paymentService->buyCourse($user, $courseByRepo);
           }
       }
   }

}
