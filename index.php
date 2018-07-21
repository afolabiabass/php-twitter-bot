<?php
/**
 * Created by PhpStorm.
 * User: AfolabiAbass
 * Date: 16/06/2018
 * Time: 04:45
 */

require_once('vendor/autoload.php');

use Symfony\Component\Dotenv\Dotenv;
use AfolabiAbass\App\Sheets;

$dotenv = new Dotenv();
$dotenv->load(__DIR__.'/.env');

session_start();

$sheets = new Sheets(getenv('TWITTER_ACCESS_TOKEN'), getenv('TWITTER_ACCESS_TOKEN_SECRET'), Phirehose::METHOD_FILTER);
$sheets->updateSpreadSheet('worldcup');