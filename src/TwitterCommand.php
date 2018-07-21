<?php
/**
 * Created by PhpStorm.
 * User: AfolabiAbass
 * Date: 04/07/2018
 * Time: 10:54
 */

namespace AfolabiAbass\App;


use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class TwitterCommand extends Command
{
    protected function configure()
    {
        $this->setName('twitter:run')
            ->setDescription('Call the Twitter class and retrieves the filter')
            ->addArgument('Filter', InputArgument::REQUIRED, 'What filter would you like to draw tweets');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        //Call Twitter
        $filter =  $input->getArgument('Filter');

        $data = [
            'consumer_key' => getenv('TWITTER_API_KEY'),
            'consumer_secret' => getenv('TWITTER_API_SECRET'),
            'oauth_token' => getenv('TWITTER_ACCESS_TOKEN'),
            'oauth_secret' => getenv('TWITTER_ACCESS_TOKEN_SECRET'),
            'method' => \Phirehose::METHOD_FILTER
        ];

        $sheets = new Sheets($data) ;
        $sheets->updateSpreadSheet($filter);

        $output->writeln("{$filter}");
    }
}