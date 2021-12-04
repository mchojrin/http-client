<?php

namespace Amp\Http\Client\Connection;

use Amp\Cancellation;
use Amp\Future;
use Amp\Http\Client\Internal\ForbidCloning;
use Amp\Http\Client\Internal\ForbidSerialization;
use Amp\Socket\EncryptableSocket;
use Amp\Socket\SocketAddress;
use Amp\Socket\TlsInfo;

final class UpgradedSocket implements EncryptableSocket
{
    use ForbidCloning;
    use ForbidSerialization;

    private EncryptableSocket $socket;

    private ?string $buffer;

    /**
     * @param EncryptableSocket $socket
     * @param string            $buffer Remaining buffer previously read from the socket.
     */
    public function __construct(EncryptableSocket $socket, string $buffer)
    {
        $this->socket = $socket;
        $this->buffer = $buffer !== '' ? $buffer : null;
    }

    public function read(?Cancellation $token = null): ?string
    {
        if ($this->buffer !== null) {
            $buffer = $this->buffer;
            $this->buffer = null;
            return $buffer;
        }

        return $this->socket->read($token);
    }

    public function close(): void
    {
        $this->socket->close();
    }

    public function __destruct()
    {
        $this->close();
    }

    public function write(string $data): Future
    {
        return $this->socket->write($data);
    }

    public function end(string $finalData = ""): Future
    {
        return $this->socket->end($finalData);
    }

    public function reference(): void
    {
        $this->socket->reference();
    }

    public function unreference(): void
    {
        $this->socket->unreference();
    }

    public function isClosed(): bool
    {
        return $this->socket->isClosed();
    }

    public function getLocalAddress(): SocketAddress
    {
        return $this->socket->getLocalAddress();
    }

    public function getRemoteAddress(): SocketAddress
    {
        return $this->socket->getRemoteAddress();
    }

    public function setupTls(?Cancellation $token = null): void
    {
        $this->socket->setupTls($token);
    }

    public function shutdownTls(?Cancellation $token = null): void
    {
        $this->socket->shutdownTls();
    }

    public function getTlsState(): int
    {
        return $this->socket->getTlsState();
    }

    public function getTlsInfo(): ?TlsInfo
    {
        return $this->socket->getTlsInfo();
    }

    public function isReadable(): bool {
        return $this->socket->isReadable();
    }

    public function isWritable(): bool {
        return $this->socket->isWritable();
    }
}
