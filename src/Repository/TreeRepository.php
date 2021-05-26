<?php

namespace App\Repository;

use App\Entity\Tree;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
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
        $original = $tree->getOriginal();
        if (isset($id)) {
            $sql ='UPDATE tree SET depth = ' . $depth .' , flattened = "' . $flattened .'" WHERE id = '. $id .';';
        } else {
            $created_at = date('Y-m-d H:i:s');
            $sql = 'INSERT INTO tree (depth, created_at, original) VALUES ('. $depth .' , "'. $created_at .'" , "'. $original .'");';

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
        $flattened  = $database_array_node["flattened"];
        $created_at  = $database_array_node["created_at"];
        $original  = $database_array_node["original"];
        $this->buildTree($tree, $id, $depth, $flattened, $created_at, $original);
        return $tree;
    }

    public function findOrderBy(
        string $criteria,
        int $limit
    ): Collection
    {
        $sql = 'SELECT * FROM tree WHERE flattened IS NOT NULL AND LENGTH(flattened) > 0 ORDER BY ' . $criteria .' DESC LIMIT ' . $limit .';';
        $database_returned = $this->getDatabaseData($sql);
        $collection = new ArrayCollection();
        foreach ($database_returned as $array_tree) {
            $tree = new Tree();
            $id  = $array_tree["id"];
            $depth  = $array_tree["depth"];
            $flattened  = $array_tree["flattened"];
            $created_at  = $array_tree["created_at"];
            $original  = $array_tree["original"];
            $this->buildTree($tree, $id, $depth, $flattened, $created_at, $original);
            $collection[] = $tree;
        }
        return $collection;
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

    private function buildTree(Tree $tree, int $id, int $depth, ?string $flattened, string $created_at, ?string $original): void
    {
        $tree->setId($id);
        $tree->setDepth($depth);
        $tree->setFlattened($flattened);
        $tree->setCreatedAt(\DateTime::createFromFormat('Y-m-d H:i:s', $created_at));
        $tree->setOriginal($original);
    }
}
