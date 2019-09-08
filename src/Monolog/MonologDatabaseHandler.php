<?php

namespace App\Monolog;

use App\Model\Log;
use Doctrine\ORM\EntityManagerInterface;
use Monolog\Handler\AbstractProcessingHandler;

class MonologDatabaseHandler extends AbstractProcessingHandler
{
    /** @var EntityManagerInterface */
    protected $manager;

    /**
     * @param EntityManagerInterface $manager
     */
    public function __construct(EntityManagerInterface $manager)
    {
        parent::__construct();

        $this->manager = $manager;
    }

    /**
     * @param array $record
     */
    protected function write(array $record)
    {
        if (!$this->manager->isOpen()) {
            return;
        }

        $logEntry = new Log();
        $logEntry->setMessage($record['message']);
        $logEntry->setLevel($record['level']);
        $logEntry->setLevelName($record['level_name']);
        $logEntry->setExtra($record['extra']);
        $logEntry->setContext($record['context']);

        $this->manager->persist($logEntry);
        $this->manager->flush();
    }
}
