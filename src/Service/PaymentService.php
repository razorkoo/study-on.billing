<?php

namespace App\Service;

use App\Entity\BillingUser;
use App\Entity\Transaction;
use App\Entity\Course;
use Doctrine\ORM\EntityManager;
use PHPUnit\Runner\Exception;

class PaymentService
{
    private $manager;
    public function __construct(EntityManager $entityManager)
    {
        $this->manager = $entityManager;

    }
    public function increaseBalance(BillingUser $user, $income)
    {
        $this->manager->getConnection()->beginTransaction();
        try {
            $newTransaction = new Transaction();
            $newTransaction->setType(Transaction::TYPE_DEPOSIT);
            $newTransaction->setBUser($user);
            $newTransaction->setValue($income);
            $user->setBalance($user->getBalance()+$income);
            $this->manager->persist($newTransaction);
            $this->manager->persist($user);
            $this->manager->flush();
            $this->manager->getConnection()->commit();
        } catch (Exception $e) {
            $this->manager->getConnection()->rollBack();
        }
    }
    public function buyCourse($userId, Course $course)
    {
        $this->manager->getConnection()->beginTransaction();
        $newTransaction = new Transaction();
        try {
            $user = $this->manager->getRepository(BillingUser::class)->findOneBy(['email'=>$userId]);
            if ($user->getBalance() >= $course->getPrice())
            {
                $newTransaction->setType(Transaction::TYPE_PAYMENT);
                $newTransaction->setBUser($user);
                $newTransaction->setCourse($course);

                if ($course->getPrice()) {
                    $newTransaction->setValue($course->getPrice());
                    $user->setBalance($user->getBalance()-$course->getPrice());
                    if ($course->getType()==Course::TYPE_RENT) {
                        $newTransaction->setExpiredat();
                    }
                } else {
                    $newTransaction->setValue(0.0);
                }
                $newTransaction->setCreatedat(new \DateTime());
                $this->manager->persist($newTransaction);
                $this->manager->persist($user);
                $this->manager->flush();
                $this->manager->getConnection()->commit();
            } else {
                throw new \Exception("You don't have enough money",400);
            }

        } catch (Exception $e) {
            $this->manager->getConnection()->rollBack();
        }
        return $newTransaction;
    }

}