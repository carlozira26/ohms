<?php
use Ratchet\Server\IoServer;
use Ratchet\WebSocket\WsServer;
use Ratchet\Http\HttpServer;

use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;

    // Make sure composer dependencies have been installed
    require __DIR__ . '/vendor/autoload.php';
/**
 * chat.php
 * Send any incoming messages to all connected clients (except sender)
 */
class Api implements MessageComponentInterface {
    protected $clients;
    public $sql;

    public function __construct() {
        $this->clients = new \SplObjectStorage;
    }

    public function onOpen(ConnectionInterface $conn) {
        $this->clients->attach($conn);
        echo "New connection! ({$conn->resourceId})\n";
    }

    public function onMessage(ConnectionInterface $from, $msg) {
        foreach ($this->clients as $client) {
            if ($from != $client) {
                $client->send($msg);
            }
        }
    }

    public function onClose(ConnectionInterface $conn) {
        $this->clients->detach($conn);
    }

    public function onError(ConnectionInterface $conn, Exception $e) {
        $conn->close();
    }
}
    $server = IoServer::factory(
        new HttpServer(
            new WsServer(
                new Api()
            )
        ),
        3552
    );

    $server->run();
    // $app = new Ratchet\App('localhost', 3552);
    // $app->route('/chat', new Api, array('*'));
    // $app->run();
