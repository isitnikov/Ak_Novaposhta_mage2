<?php
namespace Ak\NovaPoshta\Model\Log;

use Monolog\Logger;
use Magento\Framework\Logger\Handler\Base as BaseHandler;

class Handler extends BaseHandler
{
    /**
     * @var string
     */
    protected $fileName = '/var/log/novaposhta.log';

    /**
     * @var int
     */
    protected $loggerType = Logger::INFO;
}