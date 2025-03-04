<?php

declare(strict_types=1);

namespace League\Route;

interface RouteConditionHandlerInterface
{
    public function getHost(): ?string;
    public function getName(): ?string;
    public function getPort(): ?int;
    public function getScheme(): ?string;
    public function setHost(string $host): self;
    public function setName(string $name): self;
    public function setPort(int $port): self;
    public function setScheme(string $scheme): self;
}
