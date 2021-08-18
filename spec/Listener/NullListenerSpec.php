<?php

namespace spec\Mahalay\PhpSpec\CoverageTest\Listener;

use Mahalay\PhpSpec\CoverageTest\Listener\NullListener;
use PhpSpec\ObjectBehavior;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * NullListener
 */
class NullListenerSpec extends ObjectBehavior
{
    public function it_is_an_event_subscriber()
    {
        $this->shouldHaveType(EventSubscriberInterface::class);
    }

    public function it_should_not_subscribe_to_any_events()
    {
        $this->getSubscribedEvents()->shouldBe([]);
    }
}
