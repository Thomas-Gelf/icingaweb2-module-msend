<?php

namespace Icinga\Module\Msend;

use Icinga\Application\Config;
use Icinga\Module\Eventtracker\Event;
use Icinga\Module\Eventtracker\ObjectClassInventory;
use Icinga\Module\Eventtracker\Priority;
use Icinga\Module\Eventtracker\SenderInventory;
use InvalidArgumentException;

class MSendEventFactory
{
    protected $senders;

    protected $classes;

    protected $severityMap;

    public function __construct(SenderInventory $senders, ObjectClassInventory $classes)
    {
        $this->senders = $senders;
        $this->classes = $classes;
        $config = Config::module('msend');
        if ($config->hasSection('severity-map')) {
            $this->severityMap = $config->getSection('severity-map')->toArray();
        } else {
            $this->severityMap = $this->getDefaultSeverityMap();
        }
    }

    /**
     * @param MSendCommandLine $cmd
     * @return Event
     * @throws \Exception
     */
    public function fromCommandLine(MSendCommandLine $cmd)
    {
        $timeout = $cmd->getSlotValue('mc_timeout');
        if (strlen($timeout) > 0) {
            if (! ctype_digit($timeout)) {
                throw new InvalidArgumentException("mc_timeout=$timeout is not a number");
            }
        } else {
            $timeout = null;
        }
        $event = new Event();
        $event->setProperties([
            'host_name'       => $cmd->getRequiredSlotValue('mc_host'),
            'object_name'     => $cmd->getRequiredSlotValue('mc_object'),
            'object_class'    => $this->classes->requireClass($cmd->getRequiredSlotValue('mc_object_class')),
            'severity'        => $this->mapSeverity($cmd->getSeverity()),
            'priority'        => $this->mapPriority($cmd->getPriority('normal')),
            'message'         => $cmd->getMessage(),
            'event_timeout'   => $timeout,
            'sender_event_id' => $cmd->getSlotValue('mc_tool_key', ''),
            'sender_id'       => $this->senders->getSenderId(
                $cmd->getSlotValue('mc_tool', 'no-tool'),
                $cmd->getSlotValue('mc_tool_class', 'NO-CLASS')
                // $this->getRequiredSlotValue('mc_tool'),
                // $this->getRequiredSlotValue('mc_tool_class')
            ),
            'attributes'      => $cmd->getSlotValues(),
        ]);

        return $event;
    }

    protected function mapSeverity($severity)
    {
        $severities = $this->severityMap;

        if (isset($severities[$severity])) {
            return $severities[$severity];
        }

        throw new InvalidArgumentException("Got invalid severity $severity");
    }

    protected function getDefaultSeverityMap()
    {
        return [
            // 'emergency',
            'CRITICAL'      => 'alert',
            'MAJOR'         => 'critical',
            'MINOR'         => 'error',
            'WARNING'       => 'warning',
            'INFORMATIONAL' => 'informational', // was: 'notice',
            'INFO'          => 'informational', // was: 'notice',        // !?!?!?!
            'NORMAL'        => 'informational', // !?!?!?!
            'OK'            => 'informational', // !?!?!?!
        ];
    }

    protected function mapPriority($priority)
    {
        $priorities = [
            // we do not accept lowest on our input channel, mapping to low
            'PRIORITY_1' => Priority::LOW,
            'lowest'     => Priority::LOW,
            'PRIORITY_2' => Priority::LOW,
            'low'        => Priority::LOW,
            'PRIORITY_3' => Priority::NORMAL,
            'normal'     => Priority::NORMAL,
            'PRIORITY_4' => Priority::HIGH,
            'high'       => Priority::HIGH,
            // we do not accept highest on our input channel, mapping to high
            'PRIORITY_5' => Priority::HIGH,
            'highest'    => Priority::HIGH,
        ];

        if (isset($priorities[$priority])) {
            return $priorities[$priority];
        }

        throw new InvalidArgumentException("Got invalid priority $priority");
    }
}
