<?php

namespace App\Support;

class UnityContext
{
    protected ?int $unityId = null;

    public function set(?int $unityId): void
    {
        $this->unityId = $unityId;
    }

    public function id(): ?int
    {
        return $this->unityId;
    }

    public function has(): bool
    {
        return $this->unityId !== null;
    }

    public function clear(): void
    {
        $this->unityId = null;
    }
}
