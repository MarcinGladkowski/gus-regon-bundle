<?php

declare(strict_types=1);

namespace MarcinGladkowski\GusBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

final class GusBundle extends Bundle
{
    public function getPath(): string
    {
        return \dirname(__DIR__);
    }
}
