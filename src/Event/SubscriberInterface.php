<?php
/*
 * This file is part of the frenzy-framework package.
 *
 * (c) Gustavo Falco <comfortablynumb84@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace IronEdge\Component\Graphs\Event;


/**
 * @author Gustavo Falco <comfortablynumb84@gmail.com>
 */
interface SubscriberInterface
{
    /**
     * Returns the ID of the subscriber.
     *
     * @return string
     */
    public function getId(): string;

    /**
     * This method is called when an event is fired.
     *
     * @param string $id  - Event ID.
     * @param array $data - Event Data.
     *
     * @return void
     */
    public function handleEvent(string $id, array $data);
}