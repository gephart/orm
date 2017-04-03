Gephart Event Manager
===

[![Build Status](https://travis-ci.org/gephart/event-manager.svg?branch=master)](https://travis-ci.org/gephart/event-manager)

Dependencies
---
 - PHP >= 7.0

Instalation
---

```
composer require gephart/event-manager
```

Using
---

Basic using:

```
$listener1 = function(){echo "Hello";};
$listener2 = function(){echo "World";};

$event_manager->attach("my.event", $listener1, 200);
$event_manager->attach("my.event", $listener2, 100);

$event_manager->trigger("my.event"); // HelloWorld
```