<?php

namespace Gui\Components;

use Gui\Application;

class Object
{
    protected $application;
    protected $eventHandlers = [];
    public $lazarusClass = 'TObject';
    public $lazarusObjectId;

    public function __construct($defaultAttributes = null, $application = null)
    {
        $object = $this;

        // We can use multiple applications, but, if no one is defined, we use the
        // first (default)
        if ($application == null) {
            $this->application = Application::$defaultApplication;
        } else {
            $this->application = $application;
        }

        // Get the next object id
        $this->application->addObject($this);

        // Send the createObject command
        $this->application->sendCommand('createObject', [
            [
                'lazarusClass' => $this->lazarusClass,
                'lazarusObjectId' => $this->lazarusObjectId,
            ]
        ], function($result) use ($object) {
            // Ok, object created
        });
    }

    public function __set($name, $value)
    {
        // @TODO - Check on a property list if we need to send the
        // command to Lazarus
        $this->application->sendCommand('setObjectProperty', [
            $this->lazarusObjectId,
            $name,
            $value
        ], function($result) {
            // Ok, the property changed
        });

        $this->$name = $value;
    }

    /**
     * Fire an object event
     * @param  String $eventName Event Name
     */
    public function fire($eventName)
    {
        if (array_key_exists($eventName, $this->eventHandlers)) {
            foreach ($this->eventHandlers[$eventName] as $eventHandler) {
                $eventHandler();
            }
        }
    }

    /**
     * Add a listener to an event
     * @param  String $eventName Event Name
     * @param  Function $eventHandler Event Handler Function
     */
    public function on($eventName, $eventHandler)
    {
        $eventName = 'on' . $eventName;

        $this->application->sendCommand('setObjectEventListener', [
            $this->lazarusObjectId,
            $eventName
        ], function($result) {
            // Ok, the event listener created
        });

        if (! array_key_exists($eventName, $this->eventHandlers)) {
            $this->eventHandlers[$eventName] = [];
        }

        $this->eventHandlers[$eventName][] = $eventHandler;
    }
}