<?php


namespace App\Service;


use App\Entity\Node;
use App\Entity\Tree;
use App\Repository\NodeRepository;
use App\Repository\TreeRepository;

class BuildTree
{

    const DELIMITER = ';';
    const CLOSURE_ELEMENT = ')';
    const OPENING_ELEMENT = '(';
    const LEVELS_DEPTH_TO_REDUCE_AFTER_FIND_CLOSURE = 2;
    const NUMBER_LEVELS_TO_REDUCE = 1;
    const LEVEL_ZERO = 0;
    const NUMBER_LEVELS_TO_INCREASE_AFTER_EACH_NODE = 1;
    const POSITION_FIRST_ELEMENT_OF_INPUT = 0;
    private NodeRepository $nodeRepository;
    private TreeRepository $treeRepository;
    private array $flatten_array_true;
    private int $master_depth;
    private Tree $tree;

     public function __construct(
         NodeRepository $nodeRepository,
         TreeRepository $treeRepository
     ) {
        $this->nodeRepository = $nodeRepository;
        $this->treeRepository = $treeRepository;
        $this->flatten_array_true = [];
        $this->master_depth = self::LEVEL_ZERO;
        $this->tree = new Tree();
     }

     public function doAction(string $request_content): ?Tree
     {
         $depth = self::LEVEL_ZERO;
         $accumulated = '';
         $last_node = new Node();
         $this->tree->setOriginal($request_content);
         for ($i= self::POSITION_FIRST_ELEMENT_OF_INPUT; $i<strlen($request_content); $i++) {
             $request_character = $request_content[$i];
             switch ($request_character) {
                 case self::OPENING_ELEMENT:
                     if ($this->isBadStructure($accumulated, $last_node)) {
                         return null;
                     }
                     $this->saveOpening($request_character, $last_node, $depth, $current_node);
                     break;
                 case self::CLOSURE_ELEMENT:
                     $this->saveClosure($depth, $accumulated, $last_node, $request_character, $current_node);
                     break;
                 case self::DELIMITER:
                     $this->saveAccumulated($accumulated, $last_node);
                     break;
                 default:
                     $accumulated .= $request_character;
                     break;
             }
         }
         $this->updateTree();
         return $this->successfulRequest();
     }

    public function saveAccumulated(
        string &$accumulated,
        Node &$last_node
    ): void
    {
        if (!empty($accumulated)) {
            $this->flatten_array_true[] = $accumulated;
            $node_element = new Node();
            $node_element->setValue($accumulated);
            if (!$this->existElement($last_node)) {
                $node_element->setTree($this->tree);
            }
            $accumulated = '';
            $current_node = $this->nodeRepository->save($node_element);
            if ($this->existElement($last_node)) {
                switch ($last_node->getValue()) {
                    case self::OPENING_ELEMENT:
                        $this->nodeRepository->saveNodeRelation($last_node, $node_element);
                        break;
                    case self::CLOSURE_ELEMENT:
                        $node_parent = $this->getDepthParent($last_node);
                        if (isset($node_parent)) {
                            $this->nodeRepository->saveNodeRelation($node_parent, $node_element);
                        } else {
                            $this->nodeTreeChildren($node_element);
                        }
                        break;
                    default:
                        $grandparent_node = $this->nodeRepository->getParent($last_node);
                        $this->nodeRepository->saveNodeRelation($grandparent_node, $node_element);
                        break;
                }
            }
            $last_node = $current_node;
        }
    }

    public function buildFlattenString(): string
    {
        $flatten = '';
        if (!empty($this->flatten_array_true)) {
            $flatten = self::OPENING_ELEMENT;
            foreach ($this->flatten_array_true as $key => $element) {
                if ($key < (sizeof($this->flatten_array_true) - 1)) {
                    $flatten .= $element . self::DELIMITER;
                } else {
                    $flatten .= $element . self::CLOSURE_ELEMENT;
                }
            }
        }

        return $flatten;
    }

    private function saveClosure(
        int &$depth,
        string &$accumulated,
        ?Node &$last_node,
        string $request_character,
        ?Node &$current_node
    ): void
    {
        $this->UpdateDepth($depth);
        if (!empty($accumulated)) {
            $this->saveAccumulated($accumulated, $last_node);
        }
        $close_node = new Node();
        $close_node->setValue($request_character);
        if (!$this->existElement($last_node)) {
            $close_node->setTree($this->tree);
        }
        $current_node = $this->nodeRepository->save($close_node);
        if ($this->existElement($last_node)) {
            $this->nodeRepository->saveNodeRelation($last_node, $current_node);
            $parent_node = $this->nodeRepository->getParent($last_node);
            if (isset($parent_node)) {
                $grandparent_node = $this->nodeRepository->getParent($parent_node);
                $current_node = $grandparent_node;
            }
        }
        $last_node = $current_node;
    }

    private function saveOpening(
        string $request_character,
        Node &$last_node,
        int &$depth,
        ?Node &$current_node
    ): void
    {
        $open_node = new Node();
        $open_node->setValue($request_character);
        if (!$this->existElement($last_node)) {
            $open_node->setTree($this->tree);
        }
        $this->IncreaseCurrentDepth($depth);
        $this->tree = $this->treeRepository->save($this->tree);
        $current_node = $this->nodeRepository->save($open_node);
        if ($this->existElement($last_node)) {
            $this->nodeRepository->saveNodeRelation($last_node, $open_node);
        }
        $last_node = $current_node;
    }

    private function existElement(?Node $last_node): bool
    {
        return  (!is_null($last_node) && !is_null($last_node->getId()));
    }

    private function updateTree(): void
    {
        $this->setTotalDepth();
        $flattened_string = $this->buildFlattenString();
        $this->tree->setFlattened($flattened_string);
        $this->tree = $this->treeRepository->save($this->tree);
    }

    private function nodeTreeChildren(Node $node_element): void
    {
        $node_element->setTree($this->tree);
        $this->nodeRepository->save($node_element);
    }

    private function getDepthParent(Node $last_node): ?Node
    {
        $node_parent = null;
        $node_cousin = $this->nodeRepository->getParent($last_node);
        if (isset($node_cousin)) {
            $node_sibling = $this->nodeRepository->getParent($node_cousin);
            if (isset($node_sibling)) {
                $node_parent = $this->nodeRepository->getParent($node_sibling);
            }
        }
        return $node_parent;
    }

    private function UpdateDepth(int &$depth): void
    {
        if ($depth > $this->master_depth) {
            $this->master_depth = $depth;
        }
        $depth = $depth - self::LEVELS_DEPTH_TO_REDUCE_AFTER_FIND_CLOSURE;
    }

    private function IncreaseCurrentDepth(int &$depth): void
    {
        $depth += self::NUMBER_LEVELS_TO_INCREASE_AFTER_EACH_NODE;
    }

    private function setTotalDepth(): void
    {
        if ($this->master_depth > self::LEVEL_ZERO) {
            $this->tree->setDepth($this->master_depth - self::NUMBER_LEVELS_TO_REDUCE);
        } else {
            $this->tree->setDepth($this->master_depth);
        }
    }

    private function successfulRequest(): ?Tree
    {
        if (empty($this->tree->getFlattened())) {
            return null;
        }
        return $this->tree;
    }

    private function isBadStructure(
        string $accumulated,
        Node $last_node
    ): bool
    {
        return !empty($accumulated) || is_null($last_node);
    }
}