<?php

namespace App\Command;

use App\Repository\TransactionRepository;
use DateTimeImmutable;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Mailer\MailerInterface;

#[AsCommand(name: 'payment:ending:notification')]
class EndingRentNotificationCommand extends Command
{
    public function __construct(
        private readonly TransactionRepository $transactionRepository,
        private readonly MailerInterface $mailer,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $from = new DateTimeImmutable('+1 day midnight');
        $to = new DateTimeImmutable('+2 days midnight');
        $transactions = $this->transactionRepository->findEndingRentals($from, $to);

        $byEmail = [];
        foreach ($transactions as $transaction) {
            $byEmail[$transaction->getUser()->getEmail()][] = $transaction;
        }

        foreach ($byEmail as $email => $items) {
            $this->mailer->send((new TemplatedEmail())
                ->from('no-reply@study-on.local')
                ->to($email)
                ->subject('Срок аренды курсов скоро закончится')
                ->htmlTemplate('email/ending_rent.html.twig')
                ->context(['transactions' => $items]));
        }

        $output->writeln(sprintf('Sent %d notification(s).', count($byEmail)));

        return Command::SUCCESS;
    }
}
