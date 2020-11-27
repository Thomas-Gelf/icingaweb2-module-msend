<?php

namespace Icinga\Module\Eventtracker\Controllers;

use Icinga\Application\Logger;

class IndexController extends Controller
{
    protected $requiresAuthentication = false;

    /**
     * @throws \Exception
     */
    public function indexAction()
    {
        $cmd = $this->getRequest()->getRawBody();
        $this->getResponse()->setHeader('Content-Type', 'text/plain');
        try {
            $event = $this->getEvent();
            $issue = $this->processEvent($event);
            if ($issue) {
                $uuid = $issue->getNiceUuid();
            } else {
                $uuid = 0;
            }
            echo "Message #1 - Evtid = $uuid\n";

            $error = false;
        } catch (\Exception $e) {
            $error = $e->getMessage();
            echo $e->getMessage() . "\n";
        }
        if ($this->Config()->get('msend', 'force_log') === 'yes') {
            if ($error) {
                Logger::error("msend (ERR: $error): $cmd");
            } else {
                Logger::error("msend ($uuid): $cmd");
            }
        } elseif ($error) {
            Logger::error("msend (ERR: $error): $cmd");
        } else {
            Logger::debug("msend ($uuid): $cmd");
        }
        // TODO: disable layout + viewRenderer, clean shutdown
        exit;
    }
}
