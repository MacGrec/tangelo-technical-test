<?php

namespace App\Repository;

use App\Entity\Tree;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\Driver\Connection;
use Doctrine\DBAL\Driver\Statement;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Tree|null find($id, $lockMode = null, $lockVersion = null)
 * @method Tree|null findOneBy(array $criteria, array $orderBy = null)
 * @method Tree[]    findAll()
 * @method Tree[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TreeRepository extends ServiceEntityRepository
{
    private Connection $connection;

    public function __construct(
        ManagerRegistry $registry,
        Connection $connection
    )
    {
        $this->connection = $connection;
        parent::__construct($registry, Tree::class);
    }

    public function save(Tree $tree): Tree
    {
        $id = $tree->getId();
        $depth = $tree->getDepth();
        $flattened = $tree->getFlattened();
        if (isset($id)) {
            $sql ='UPDATE tree SET depth = ' . $depth .' , flattened = "' . $flattened .'" WHERE id = '. $id .';';
        } else {
            $created_at = date('Y-m-d H:i:s');
            $sql = 'INSERT INTO tree (depth, created_at) VALUES ('. $depth .' , "'. $created_at .'");';

        }
        $this->executeQuery($sql);
        if (!isset($id) || $id === 0) {
            $tree->setId($this->connection->lastInsertId());
        }
        return $tree;
    }

    public function findById(int $id): ?Tree
    {
        $sql = 'SELECT * FROM tree WHERE id = '. $id .';';
        $database_returned = $this->getDatabaseData($sql);
        if(!isset($database_returned[0])) {
            return null;
        }
        $database_array_node = $database_returned[0];
        $tree = new Tree();
        $depth  = $database_array_node["depth"];
        $tree->setId($id);
        $tree->setDepth($depth);
        return $tree;
    }

    private function executeQuery(string $sql): Statement
    {
        $statement = $this->connection->prepare($sql);
        $statement->executeQuery();
        return $statement;
    }

    private function getDatabaseData(string $sql): array
    {
        $statement = $this->executeQuery($sql);
        return $statement->fetchAll();
    }
}
