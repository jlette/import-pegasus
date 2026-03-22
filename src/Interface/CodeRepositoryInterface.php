<?php

namespace App\Interface;

interface CodeRepositoryInterface
{
    public function findCode(string $code): string;
}
