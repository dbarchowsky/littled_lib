<?php
namespace Littled\Database;

class ConnectionTracker
{
    protected static array  $connections = [];

    /**
     * Adds a new connection to the stack.
     * @param array $backtrace
     * @param float|null $create_time
     * @return int
     */
    public static function addConnection(array $backtrace, float|null $create_time = null): int
    {
        self::$connections[] = (new ConnectionDetails())
            ->setCreateTime($create_time)
            ->setBacktrace($backtrace);
        return array_key_last(self::$connections);
    }

    /**
     * Returns the current number of connections being tracked.
     * @return int
     */
    public static function getConnectionCount(): int
    {
        return count(self::$connections);
    }

    public static function removeConnection(int $connection_id): void
    {
        unset(self::$connections[$connection_id]);
    }
}