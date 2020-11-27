<?php

namespace Icinga\Module\Eventtracker\Controllers;

use ipl\Html\Html;

class TestController extends Controller
{
    protected $requiresAuthentication = false;

    /**
     * @throws \Exception
     */
    public function indexAction()
    {
        $event = $this->getEvent();
        $issue = $this->processEvent($event);
        $mSend = $this->getMSend();
        $this->addSingleTab('Testing');
        $this->content()->add([
            Html::tag('h1', 'Issue'),
            Html::tag('pre', $issue ? print_r($issue->getProperties(), true) : 'This event is NOT an issue'),
            Html::tag('h1', 'Event'),
            Html::tag('pre', print_r($event->getProperties(), true)),
            Html::tag('h1', 'Arguments'),
            Html::tag('pre', print_r($mSend->getArguments(), true)),
            Html::tag('h1', 'Slot Values'),
            Html::tag('pre', print_r($mSend->getSlotValues(), true)),
        ]);
    }
}
