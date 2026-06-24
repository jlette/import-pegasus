<?php

namespace App\Interface;

interface CodeRepositoryInterface
{
    public function findByPlatforme(string $platforme): array;
}
