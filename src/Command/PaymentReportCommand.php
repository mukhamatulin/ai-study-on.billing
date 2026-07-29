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

#[AsCommand(name: 'payment:report')]
class PaymentReportCommand extends Command
{
    public function __construct(
        private readonly TransactionRepository $transactionRepository,
        private readonly MailerInterface $mailer,
        private readonly string $billingReportEmail,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $from = new DateTimeImmutable('first day of previous month midnight');
        $to = new DateTimeImmutable('first day of this month midnight');
        $rows = $this->transactionRepository->getPaymentReport($from, $to);
        $total = array_reduce($rows, static fn (float $sum, array $row): float => $sum + (float) $row['total_amount'], 0.0);

        $this->mailer->send((new TemplatedEmail())
            ->from('no-reply@study-on.local')
            ->to($this->billingReportEmail)
            ->subject('Отчет об оплаченных курсах')
            ->htmlTemplate('email/payment_report.html.twig')
            ->context([
                'from' => $from,
                'to' => $to,
                'rows' => $rows,
                'total' => number_format($total, 2, '.', ''),
            ]));

        $output->writeln('Payment report sent.');

        return Command::SUCCESS;
    }
}
