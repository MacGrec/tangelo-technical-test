<?php


namespace App\Controller\Service;


use App\Entity\Node;
use App\Entity\Tree;
use App\Repository\NodeRepository;
use App\Repository\TreeRepository;

class BuildTree
{

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
        $this->master_depth = 0;
        $this->tree = new Tree();
     }

     public function doAction(string $request_content): Tree
     {
         $depth = 0;
         $accumulated = '';
         $last_node = new Node();
         for ($i=0; $i<strlen($request_content); $i++) {
             $request_character = $request_content[$i];
             switch ($request_character) {
                 case '(':
                     $this->saveOpening($request_character, $last_node, $depth, $current_node);
                     break;
                 case ')':
                     $this->saveClosure($depth, $accumulated, $last_node, $request_character, $current_node);
                     break;
                 case ';':
                     $this->saveAccumulated($accumulated, $last_node);
                     break;
                 default:
                     $accumulated .= $request_character;
                     break;
             }
         }
         $this->updateTree();
         return $this->tree;
     }

    public function saveAccumulated(string &$accumulated, Node &$last_node): void
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
                    case '(':
                        $this->nodeRepository->saveNodeRelation($last_node, $node_element);
                        break;
                    case ')':
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
        $flatten = '(';
        foreach ($this->flatten_array_true as $key => $element) {
            if ($key < (sizeof($this->flatten_array_true) - 1)) {
                $flatten .= $element . ";";
            } else {
                $flatten .= $element . ")";
            }
        }
        return $flatten;
    }

    private function saveClosure(mixed &$depth, mixed &$accumulated, mixed &$last_node, string $request_character, &$current_node): void
    {
        if ($depth > $this->master_depth) {
            $this->master_depth = $depth;
        }
        $depth = $depth - 2;
        if ($accumulated !== '') {
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

    private function saveOpening(string $request_character, mixed &$last_node, int &$depth, &$current_node): void
    {
        $open_node = new Node();
        $open_node->setValue($request_character);
        if (is_null($last_node->getId())) {
            $open_node->setTree($this->tree);
        }
        $depth += 1;
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
        if ($this->master_depth > 0) {
            $this->tree->setDepth($this->master_depth - 1);
        } else {
            $this->tree->setDepth($this->master_depth);
        }
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
}