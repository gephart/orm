<?php

require_once __DIR__ . '/../vendor/autoload.php';

class EventManagerTest extends \PHPUnit\Framework\TestCase
{
    public function testEvents()
    {
        $event_manager = new \Gephart\EventManager\EventManager();

        $listener1 = function(\Gephart\EventManager\EventInterface $event){echo "Word";$event->stopPropagation(true);};
        $listener2 = function(){echo "Bad";};
        $listener3 = function(){echo "Hello";};
        $listener4 = function(){echo "Stop";};

        $event_manager->attach("my.event", $listener1, 100);
        $event_manager->attach("my.event", $listener2, 1);
        $event_manager->attach("my.event", $listener3, 200);
        $event_manager->attach("my.event", $listener4, 100);


        $event_manager->detach("my.event", $listener2);

        ob_start();
        $event_manager->trigger("my.event");
        $result = ob_get_contents();
        ob_end_clean();

        $this->assertEquals("HelloWord", $result);
    }
}
