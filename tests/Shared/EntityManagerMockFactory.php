<?php

declare(strict_types=1);

namespace TiendaTurismo\GestionDatos\Tests\Shared;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/** @template T of object */
trait EntityManagerMockFactory
{
    private EntityManagerInterface&MockObject $entityManager;
    private EntityRepository&MockObject $entityRepository;

    /** @param class-string<T> $entityClass */
    private function crearMocksEntityManager(TestCase $test, string $entityClass): void
    {
        $this->entityRepository = $test->createMock(EntityRepository::class);

        $this->entityManager = $test->createMock(EntityManagerInterface::class);
        $this->entityManager
            ->method('getRepository')
            ->with($entityClass)
            ->willReturn($this->entityRepository);
    }
}
