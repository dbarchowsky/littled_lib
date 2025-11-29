<?php

namespace Littled\Database;


class ConnectionDetails
{
    protected float $create_time;
    protected array $backtrace = [];

    /**
     * Backtrace getter.
     * @return array
     */
    public function getBacktrace(): array
    {
        return $this->backtrace;
    }

    /**
     * Create time getter.
     * @return float|null
     */
    public function getCreateTime(): float|null
    {
        return $this->create_time;
    }

    /**
     * Backtrace setter.
     * @param array $backtrace
     * @return $this
     */
    public function setBacktrace(array $backtrace): static
    {
        $this->backtrace = $backtrace;
        return $this;
    }

    /**
     * Creation time setter
     * @param float|null $timestamp
     * @return $this
     */
    public function setCreateTime(float|null $timestamp = null): static
    {
        $this->create_time =$timestamp ?? microtime(true);
        return $this;
    }
}