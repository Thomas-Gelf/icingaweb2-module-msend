<?php

namespace Icinga\Module\Msend\Controllers;

use gipfl\IcingaWeb2\CompatController;
use gipfl\ZfDb\Adapter\Adapter as Db;
use Icinga\Module\Eventtracker\Event;
use Icinga\Module\Eventtracker\DbFactory;
use Icinga\Module\Eventtracker\EventReceiver;
use Icinga\Module\Eventtracker\Issue;
use Icinga\Module\Eventtracker\ObjectClassInventory;
use Icinga\Module\Eventtracker\SenderInventory;
use Icinga\Module\Msend\MSendEventFactory;
use Icinga\Module\Msend\MSendCommandLine;

class Controller extends CompatController
{
    /** @var ?Db */
    protected $db;

    /** @var ?MSendCommandLine */
    protected $mSend;

    protected function db(): Db
    {
        if ($this->db === null) {
            $this->db = DbFactory::db();
        }

        return $this->db;
    }

    protected function processEvent(Event $event): ?Issue
    {
        $receiver = new EventReceiver($this->db());
        return $receiver->processEvent($event);
    }

    /**
     * @throws \Exception
     */
    protected function getEvent(): Event
    {
        $db = $this->db();
        $senders = new SenderInventory($db);
        $classes = new ObjectClassInventory($db);
        $eventFactory = new MSendEventFactory($senders, $classes);

        return $eventFactory->fromCommandLine($this->getMSend());
    }

    protected function getMSend(): MSendCommandLine
    {
        if ($this->mSend === null) {
            $cmd = $this->getRequest()->getRawBody();
            $this->mSend = new MSendCommandLine($cmd);
        }

        return $this->mSend;
    }
}
