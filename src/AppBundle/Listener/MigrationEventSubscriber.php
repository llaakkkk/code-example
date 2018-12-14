<?php

namespace AppBundle\Listener;

use Doctrine\Common\EventSubscriber;
use Doctrine\DBAL\Schema\PostgreSqlSchemaManager;
use Doctrine\ORM\Tools\Event\GenerateSchemaEventArgs;
use Doctrine\ORM\Tools\ToolEvents;

class MigrationEventSubscriber implements EventSubscriber
{
    /**
     * {@inheritdoc}
     */
    public function getSubscribedEvents(): array
    {
        return [
            ToolEvents::postGenerateSchema,
        ];
    }

    /**
     * @param GenerateSchemaEventArgs $args
     *
     * @throws \Doctrine\DBAL\Schema\SchemaException
     */
    public function postGenerateSchema(GenerateSchemaEventArgs $args): void
    {
        $schemaManager = $args->getEntityManager()
                              ->getConnection()
                              ->getSchemaManager();

        if (!$schemaManager instanceof PostgreSqlSchemaManager) {
            return;
        }

        foreach ($schemaManager->getExistingSchemaSearchPaths() as $namespace) {
            if (!$args->getSchema()->hasNamespace($namespace)) {
                $args->getSchema()->createNamespace($namespace);
            }
        }
    }
}