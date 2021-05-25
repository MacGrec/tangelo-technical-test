<?php

namespace App\Repository;

use App\Entity\Node;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use \Doctrine\DBAL\Driver\Statement;
use Doctrine\DBAL\Driver\Connection;

/**
 * @method Node|null find($id, $lockMode = null, $lockVersion = null)
 * @method Node|null findOneBy(array $criteria, array $orderBy = null)
 * @method Node[]    findAll()
 * @method Node[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class NodeRepository extends ServiceEntityRepository
{
    private Connection $connection;
    private TreeRepository $treeRepository;

    public function __construct(
        ManagerRegistry $registry,
        Connection $connection,
        TreeRepository $treeRepository
    )
    {
        $this->connection = $connection;
        $this->treeRepository = $treeRepository;
        parent::__construct($registry, Node::class);
    }

    public function save(Node $node): Node
    {
        $node_id = $node->getId();
        if (isset($node_id)) {
            $saved_node = $this->findById($node_id);
            $tree = $node->getTree();
            $tree_id = $tree->getId();
            if (isset($tree_id)) {
                $sql ='UPDATE node SET tree_id = ' . $tree_id .' WHERE id = '. $node_id .';';
            }
            $this->executeQuery($sql);
            $saved_node->setTree($tree);
            $node = $saved_node;
        } else {
            $tree = $node->getTree();
            $tree_id = null;
            if(isset($tree)) {
                $tree_id = $node->getTree()->getId();
            }
            $value = $node->getValue();

            if (isset($tree_id)) {
                $sql = 'INSERT INTO node (tree_id, value) VALUES ('. $tree_id .', "'. $value .'");';

            } else {
                $sql = 'INSERT INTO node (tree_id, value) VALUES ( null , "'. $value .'");';
            }

            $this->executeQuery($sql);
            $node->setId($this->connection->lastInsertId());
        }
        return $node;
    }

    public function saveNodeRelation(Node $parent, Node $children)
    {
        $parent_id = $parent->getId();
        $children_id = $children->getId();
        $sql = 'INSERT INTO node_node (node_source, node_target) VALUES ('. $parent_id .', '. $children_id .');';
        $this->executeQuery($sql);
    }

    public function findById(int $id): ?Node
    {
        $sql = 'SELECT * FROM node WHERE id = '. $id .';';
        $database_returned = $this->getDatabaseData($sql);
        if(!isset($database_returned[0])) {
            return null;
        }
        $database_array_node = $database_returned[0];
        $node = new Node();
        $tree_id = $database_array_node["tree_id"];
        $value = $database_array_node["value"];
        $node->setId($id);
        if (isset($tree_id)) {
            $tree = $this->treeRepository->findById($tree_id);
            $node->setTree($tree);
        }
        $node->setValue($value);
        return $node;
    }

    public function getParent(Node $sibling): ?Node
    {
        $sibling_id = $sibling->getId();
        $sql = 'SELECT * FROM node_node WHERE node_target = '. $sibling_id .';';
        $database_returned = $this->getDatabaseData($sql);
        if(!isset($database_returned[0])) {
            return null;
        }
        $database_array_node_node = $database_returned[0];
        $node_parent_id = $database_array_node_node["node_source"];
        return $this->findById($node_parent_id);
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
