<?php
declare(strict_types=1);

namespace Restaurante\Domain\Entity;

final readonly class Familiar
{
    public function __construct(
        public int $titularId,
        public string $nombre,
        public string $parentesco,
    ) {
    }
}
