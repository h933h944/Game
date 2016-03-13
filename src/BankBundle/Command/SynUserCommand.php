<?php
namespace BankBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Predis\Client;

class SynUserCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName('redis:push:user');
        $this->setDescription('push user data to DB from redis');
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

        $userRepository = $em->getRepository('BankBundle:User');

        $client = new Client();
        $count = 1;

        while ($client->llen('updateUser') > 0 and $count <= 1000) {
            $userId = $client->lpop('updateUser');
            $user = $userRepository->find($userId);
            $balance = $client->get('userId:' . $userId . ':balance' );

            $user->setBalance($balance);

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

        $output->writeln('Done!');
    }
}