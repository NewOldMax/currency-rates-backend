<?php

namespace features\CurrencyRates\Support;

use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Component\DependencyInjection\ContainerInterface;

trait DoctrineHelperTrait
{

    protected static $schemaIsReady = false;

    protected static $truncateTablesSQL = [];

    protected static $needToReload = false;

    /**
     * @return ContainerInterface
     */
    abstract public function getContainer();

    protected function rememberToReloadEntities()
    {
        self::$needToReload = true;
    }

    /**
     * @AfterStep
     */
    protected function reloadChanges()
    {
        if (!self::$needToReload) {
            return;
        }

        $em = $this->getContainer()->get('doctrine.orm.entity_manager');
        $im = $em->getUnitOfWork()->getIdentityMap();
        foreach ($im as $entities) {
            foreach ($entities as $entity) {
                $em->refresh($entity);
            }
        }
    }

    /**
     * @TODO: closed on DQL operation
     * @AfterStep
     */
    public function flushChanges()
    {
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');
        if ($em->isOpen()) {
            $em->flush();
        }
    }

    /**
     * @BeforeScenario
     */
    public function cleanupDatabase()
    {
        if (self::$schemaIsReady) {
            $this->truncateTables();
        } else {
            $this->createSchemaFromScratch();
        }
    }

    private function truncateTables()
    {
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');
        $connection = $em->getConnection();
        foreach (self::$truncateTablesSQL as $sql) {
            $connection->exec($sql);
        }
    }

    private function createSchemaFromScratch()
    {
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');
        $metadata = $em->getMetadataFactory()->getAllMetadata();
        if (!empty($metadata)) {
            $tool = new SchemaTool($em);
            $tool->dropSchema($metadata);
            $tool->createSchema($metadata);
        }

        $connection = $em->getConnection();
        // truncate all tables
        self::$truncateTablesSQL[] = sprintf(
            'truncate table %s restart identity cascade;',
            implode(', ', $connection->getSchemaManager()->listTableNames())
        );

        self::$schemaIsReady = true;
    }

    /**
     * //FIXME: BeforeSuite should be static
     * //@BeforeSuite
     */
    public function restartSequences()
    {
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');
        $connection = $em->getConnection();
        foreach ($connection->getSchemaManager()->listSequences() as $seq) {
            $seq_name = $seq->getName();
            self::$truncateTablesSQL[] = "alter sequence $seq_name restart with 1";
        }
    }
}
