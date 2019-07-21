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

class NotificationCommand extends Command
{
    private $twig;
    private $mailer;
    private $sendFrom;
    private $entityManager;
    protected static $defaultName = 'payment:ending:notification';

    public function __construct(Twig $twig, \Swift_Mailer $mailer, $sendFrom, $entityManager,$name = null)
    {
      $this->twig = $twig;
      $this->mailer = $mailer;
      $this->sendFrom = $sendFrom;
      $this->entityManager = $entityManager;
      parent::__construct();
    }
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->send();
    }
    private function send()
    {
        $users = $this->entityManager->getRepository(BillingUser::class)->findAll();
        $currentDate = new \DateTime();
        $tomorrow = (new \DateTime())->modify('1 day');
        foreach ($users as $user) {
            $transactions = $this->entityManager->getRepository(Transaction::class)->findByDate($currentDate, $tomorrow, Transaction::TYPE_PAYMENT, $user, null, null);
            $transactionsEmail = Array();
            print(count($transactionsEmail));
            foreach ($transactions as $transaction) {
                $transactionsEmail[] = ['coursename' => $transaction->getCourse()->getSlug(), 'expires' => $transaction->getExpiredat()];
            }
            if (count($transactionsEmail) > 0) {
                $message = (new \Swift_Message('Аренда следующих курсов скоро закончится'))
                    ->setFrom($this->sendFrom)
                    ->setTo($user->getEmail())
                    ->setBody($this->twig->render('notification.html.twig', ['transactionsEmail' => $transactionsEmail]),'text/html');
                $this->mailer->send($message);
            }
        }
    }


}