<?php

namespace Reverb\Http\Controllers;

use Psr\Http\Message\RequestInterface;
use Ratchet\ConnectionInterface;
use Ratchet\Http\HttpServerInterface;
use Reverb\Contracts\ChannelManager;
use Symfony\Component\HttpFoundation\JsonResponse;

class EventController implements HttpServerInterface
{
    public function onOpen(ConnectionInterface $conn, RequestInterface $request = null)
    {
        $event = json_decode($request->getBody(), true);

        foreach (app(ChannelManager::class)->all() as $connection) {
            $connection->send(json_encode([
                'event' => $event['name'],
                'channel' => $event['channel'],
                'data' => $event['data'],
            ]));
        }

        tap($conn)->send(new JsonResponse((object) []))->close();
    }

    public function onMessage(ConnectionInterface $from, $message)
    {
        //
    }

    public function onClose(ConnectionInterface $connection)
    {
        //
    }

    public function onError(ConnectionInterface $connection, \Exception $e)
    {
        //
    }
}