<?php


namespace App\Tests\Unit;

use App\Service\GetTreeList;
use App\Entity\Tree;
use App\Repository\TreeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use PHPUnit\Framework\TestCase;

class GetTreeListTest extends TestCase
{
    public function testSuccess() {
        $collection = new ArrayCollection();
        $tree = new Tree();
        $tree->setOriginal('((A;20;(B));40)');
        $tree->setFlattened('(A;20;B;40');
        $tree->setDepth(2);
        $created_at = '2021-05-25 14:50:38';
        $tree->setCreatedAt(\DateTime::createFromFormat('Y-m-d H:i:s', $created_at));
        $collection[] = $tree;
        
        $treeRepository = $this->getMockBuilder(TreeRepository::class)
            ->disableOriginalConstructor()
            ->getMock();

        $treeRepository->expects(self::exactly(1))
            ->method('findOrderBy')
            ->with('created_at', '100')
            ->willReturn($collection);

        $getTreeList = new GetTreeList($treeRepository);
        $getTreeList->doAction();

    }

    public function testSuccessEmptyCollection() {
        $collection = new ArrayCollection();

        $treeRepository = $this->getMockBuilder(TreeRepository::class)
            ->disableOriginalConstructor()
            ->getMock();

        $treeRepository->expects(self::exactly(1))
            ->method('findOrderBy')
            ->with('created_at', '100')
            ->willReturn($collection);

        $getTreeList = new GetTreeList($treeRepository);
        $getTreeList->doAction();

    }

}