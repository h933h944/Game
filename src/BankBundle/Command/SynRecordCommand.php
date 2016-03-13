<?php
namespace BankBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Predis\Client;

use BankBundle\Entity\Record;

class SynRecordCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName('redis:push:record');
        $this->setDescription('push record data to DB from redis');
        $this->addArgument(
            'name',
            InputArgument::OPTIONAL,
            'Who do you want to greet?'
        );
        $this->addOption(
                'yell',
                null,
                InputOption::VALUE_NONE,
                'If set, the task will yell in uppercase letters'
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $em = $this->getContainer()->get('doctrine')->getManager();

        $client = new Client();
        $count = 1;

        while ($client->llen('jsonRecordList') > 0 and $count <= 1000) {
            $jsonRecord = $client->lpop('jsonRecordList');
            $record = json_decode($jsonRecord);
//            $output->writeln($record);
            $user = $em->find('BankBundle:User', $record->userId);

            $newRecord = new Record($user, $record->amount, $record->balance);

            $em->persist($newRecord);

            $count = $count + 1;
        }

        $em->flush();
//
//        $name = $input->getArgument('name');
//        if ($name) {
//            $text = 'Hello '.$name;
//        } else {
//            $text = 'Hello';
//        }
//
//        if ($input->getOption('yell')) {
//            $text = strtoupper($text);
//        }
//        $jsonRecord = $client->lpop('jsonRecordList');
//        $record = json_decode($jsonRecord);
        $output->writeln($jsonRecord);
        $output->writeln('Done!');
    }
}