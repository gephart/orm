<?php

namespace Gephart\EventManager;

class Event implements EventInterface
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var object
     */
    private $targer;

    /**
     * @var array
     */
    private $params;

    /**
     * @var bool
     */
    private $stop_propagation = false;

    /**
     * Get event name
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Get target/context from which event was triggered
     */
    public function getTarget()
    {
        return $this->targer;
    }

    /**
     * Get parameters passed to the event
     */
    public function getParams(): array
    {
        return $this->params;
    }

    /**
     * Get a single parameter by name
     */
    public function getParam(string $name)
    {
        return (isset($this->params[$name]))?$this->params[$name]:false;
    }

    /**
     * Set the event name
     */
    public function setName(string $name)
    {
        $this->name = $name;
    }

    /**
     * Set the event target
     */
    public function setTarget($target = null)
    {
        $this->targer = $target;
    }

    /**
     * Set event parameters
     */
    public function setParams(array $params)
    {
        $this->params = $params;
    }

    /**
     * Indicate whether or not to stop propagating this event
     */
    public function stopPropagation(bool $flag)
    {
        $this->stop_propagation = $flag;
    }

    /**
     * Has this event indicated event propagation should stop?
     */
    public function isPropagationStopped(): bool
    {
        return $this->stop_propagation;
    }
}