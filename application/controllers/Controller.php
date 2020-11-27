<?php

namespace Icinga\Module\Eventtracker\Controllers;

use gipfl\IcingaWeb2\CompatController;
use Icinga\Module\Eventtracker\Event;
use Icinga\Module\Eventtracker\MSendEventFactory;
use Icinga\Module\Eventtracker\DbFactory;
use Icinga\Module\Eventtracker\EventReceiver;
use Icinga\Module\Eventtracker\MSendCommandLine;
use Icinga\Module\Eventtracker\ObjectClassInventory;
use Icinga\Module\Eventtracker\SenderInventory;
use Zend_Db_Adapter_Pdo_Abstract as ZfDbAdapter;

class Controller extends CompatController
{
    /** @var ZfDbAdapter */
    protected $db;

    protected $mSend;

    /**
     * @return ZfDbAdapter
     */
    protected function db()
    {
        if ($this->db === null) {
            $this->db = DbFactory::db();
        }

        return $this->db;
    }

    /**
     * @param Event $event
     * @return \Icinga\Module\Eventtracker\Issue|null
     * @throws \Zend_Db_Adapter_Exception
     */
    protected function processEvent(Event $event)
    {
        $receiver = new EventReceiver($this->db());
        return $receiver->processEvent($event);
    }

    /**
     * @return Event
     * @throws \Exception
     */
    protected function getEvent()
    {
        $db = $this->db();
        $senders = new SenderInventory($db);
        $classes = new ObjectClassInventory($db);
        $eventFactory = new MSendEventFactory($senders, $classes);

        return $eventFactory->fromCommandLine($this->getMSend());
    }

    protected function getMSend()
    {
        if ($this->mSend === null) {
            $cmd = $this->getRequest()->getRawBody();
            $this->mSend = new MSendCommandLine($cmd);
        }

        return $this->mSend;
    }
}
