<?php
declare(strict_types=1);

namespace Restaurante\Application\Menus;

use DateTimeImmutable;
use Restaurante\Application\Port\MenuRepository;

final readonly class ConsultarMenuSemana
{
    public function __construct(private MenuRepository $menus)
    {
    }

    public function ejecutar(?string $fechaReferencia = null): array
    {
        $referencia = $fechaReferencia === null
            ? new DateTimeImmutable('now', new \DateTimeZone('America/La_Paz'))
            : DateTimeImmutable::createFromFormat('!Y-m-d', $fechaReferencia);
        if (!$referencia || ($fechaReferencia !== null && $referencia->format('Y-m-d') !== $fechaReferencia)) {
            return [];
        }
        $lunes = $referencia->modify('monday this week')->format('Y-m-d');
        return $this->menus->listarSemana($lunes);
    }
}
