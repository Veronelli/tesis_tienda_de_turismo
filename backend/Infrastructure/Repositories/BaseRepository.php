<?php

declare(strict_types=1);

namespace TiendaTurismo\GestionDatos\Infrastructure\Repositories;

use Doctrine\ORM\EntityManagerInterface;
use TiendaTurismo\GestionDatos\Infrastructure\Persistence\Doctrine\EntityManagerFactory;

abstract class BaseRepository
{
    protected EntityManagerInterface $entityManager;

    public function __construct(?EntityManagerInterface $entityManager = null)
    {
        $this->entityManager = $entityManager ?? EntityManagerFactory::createFromEnv();
    }

    protected function flush(): void
    {
        $this->entityManager->flush();
    }
}
