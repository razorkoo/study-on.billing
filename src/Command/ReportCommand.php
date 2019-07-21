<?php

namespace App\Command;

use Symfony\Component\Console\Command\Command;
use App\Entity\Transaction;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Doctrine\ORM\EntityManager;
use App\Service\Twig;
use App\Entity\Course;
use App\Entity\BillingUser;
use Symfony\Component\Validator\Constraints\DateTime;

class ReportCommand extends Command
{
    private $twig;
    private $mailer;
    private $sendFrom;
    private $sendTo;
    private $entityManager;
    protected static $defaultName = 'payment:report';

    public function __construct(Twig $twig, \Swift_Mailer $mailer, $sendFrom, $sendTo, $entityManager, $name = null)
    {
      $this->twig = $twig;
      $this->mailer = $mailer;
      $this->sendFrom = $sendFrom;
      $this->sendTo = $sendTo;
      $this->entityManager = $entityManager;
      parent::__construct();
    }
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->send();
    }
    private function send()
    {
        $currentDate = new \DateTime();
        $firstDate = new \DateTime('first day of this month');
        $lastDate = new \DateTime();
        $transactions = $this->entityManager->getRepository(Transaction::class)->findByDate($firstDate, $lastDate, Transaction::TYPE_PAYMENT, null, true, null);
        $allCourses = Array();
        foreach ($transactions as $transaction) {
            $allCourses[] = $transaction->getCourse();
        }
        $uniqueCourses = array_unique($allCourses, SORT_REGULAR);
        $result = Array();
        $revenueForAllCourses = 0.0;
        foreach ($uniqueCourses as $course) {
            $transactionsByCourse = $this->entityManager->getRepository(Transaction::class)->findByDate($firstDate, $lastDate, Transaction::TYPE_PAYMENT, null, true, $course);
            $type = "";
            $revenueForAllCourses+=($course->getPrice() * count($transactionsByCourse));
            switch ($course->getType()) {
                case Course::TYPE_RENT:
                    $type = 'Аренда';
                    break;
                case Course::TYPE_FULL:
                    $type = 'Покупка';
                    break;
            }
            $result[] = ['coursename' => $course->getSlug(), 'type' => $type, 'count' => count($transactionsByCourse), 'revenue' => ($course->getPrice() * count($transactionsByCourse))];
        }
        $message = (new \Swift_Message('Ежемесячный отчет'))
            ->setFrom($this->sendFrom)
            ->setTo($this->sendTo)
            ->setBody($this->twig->render('report.html.twig', ['transactions' => $result, 'fullrevenue' => $revenueForAllCourses]),'text/html');
        $this->mailer->send($message);
        print(count($result));
    }


}