<?php


namespace App\Tests\Unit;

use App\Service\BuildTree;
use App\Entity\Tree;
use App\Repository\NodeRepository;
use App\Repository\TreeRepository;
use PHPUnit\Framework\TestCase;

class BuildTreeTest extends TestCase
{

    public function testSuccessDepthZero() {
        $input_string = '(10;20;30;40)';
        $expected_tree = new Tree();
        $expected_tree->setDepth(0);
        $expected_tree->setOriginal($input_string);
        $expected_tree->setFlattened($input_string);
        $nodeRepository = $this->getMockBuilder(NodeRepository::class)
            ->disableOriginalConstructor()
            ->getMock();

        $treeRepository = $this->getMockBuilder(TreeRepository::class)
            ->disableOriginalConstructor()
            ->getMock();

        $nodeRepository->expects(self::exactly(6))
            ->method('save');

        $treeRepository->expects(self::exactly(2))
            ->method('save')
            ->willReturn($expected_tree);

        $buildTree = new BuildTree($nodeRepository, $treeRepository);
        $tree = $buildTree->doAction($input_string);
        $this->assertSame($expected_tree->getDepth(), $tree->getDepth());
        $this->assertSame($expected_tree->getOriginal(), $tree->getOriginal());
        $this->assertSame($expected_tree->getFlattened(), $tree->getFlattened());
    }

    public function testSuccessDepthOne() {
        $input_string = '((10;20;30);40)';
        $flattened_string = '(10;20;30;40)';
        $expected_tree = new Tree();
        $expected_tree->setDepth(1);
        $expected_tree->setOriginal($input_string);
        $expected_tree->setFlattened($flattened_string);
        $nodeRepository = $this->getMockBuilder(NodeRepository::class)
            ->disableOriginalConstructor()
            ->getMock();

        $treeRepository = $this->getMockBuilder(TreeRepository::class)
            ->disableOriginalConstructor()
            ->getMock();

        $nodeRepository->expects(self::exactly(8))
            ->method('save');

        $treeRepository->expects(self::exactly(3))
            ->method('save')
            ->willReturn($expected_tree);

        $buildTree = new BuildTree($nodeRepository, $treeRepository);
        $tree = $buildTree->doAction($input_string);
        $this->assertSame($expected_tree->getDepth(), $tree->getDepth());
        $this->assertSame($expected_tree->getOriginal(), $tree->getOriginal());
        $this->assertSame($expected_tree->getFlattened(), $tree->getFlattened());
    }

    public function testSuccessDepthTwo() {
        $input_string = '((A;20;(B));40)';
        $flattened_string = '(A;20;B;40)';
        $expected_tree = new Tree();
        $expected_tree->setDepth(2);
        $expected_tree->setOriginal($input_string);
        $expected_tree->setFlattened($flattened_string);
        $nodeRepository = $this->getMockBuilder(NodeRepository::class)
            ->disableOriginalConstructor()
            ->getMock();

        $treeRepository = $this->getMockBuilder(TreeRepository::class)
            ->disableOriginalConstructor()
            ->getMock();

        $nodeRepository->expects(self::exactly(10))
            ->method('save');

        $treeRepository->expects(self::exactly(4))
            ->method('save')
            ->willReturn($expected_tree);

        $buildTree = new BuildTree($nodeRepository, $treeRepository);
        $tree = $buildTree->doAction($input_string);
        $this->assertSame($expected_tree->getDepth(), $tree->getDepth());
        $this->assertSame($expected_tree->getOriginal(), $tree->getOriginal());
        $this->assertSame($expected_tree->getFlattened(), $tree->getFlattened());
    }

    public function testSuccessDepthFour() {
        $input_string = '((10;((20;(30)));(40)))';
        $flattened_string = '(10;20;30;40)';
        $expected_tree = new Tree();
        $expected_tree->setDepth(4);
        $expected_tree->setOriginal($input_string);
        $expected_tree->setFlattened($flattened_string);
        $nodeRepository = $this->getMockBuilder(NodeRepository::class)
            ->disableOriginalConstructor()
            ->getMock();

        $treeRepository = $this->getMockBuilder(TreeRepository::class)
            ->disableOriginalConstructor()
            ->getMock();

        $nodeRepository->expects(self::exactly(16))
            ->method('save');

        $treeRepository->expects(self::exactly(7))
            ->method('save')
            ->willReturn($expected_tree);

        $buildTree = new BuildTree($nodeRepository, $treeRepository);
        $tree = $buildTree->doAction($input_string);
        $this->assertSame($expected_tree->getDepth(), $tree->getDepth());
        $this->assertSame($expected_tree->getOriginal(), $tree->getOriginal());
        $this->assertSame($expected_tree->getFlattened(), $tree->getFlattened());
    }

    public function testSuccessDepthThree() {
        $input_string = '((10;((20));(40)))';
        $flattened_string = '(10;20;30;40)';
        $expected_tree = new Tree();
        $expected_tree->setDepth(3);
        $expected_tree->setOriginal($input_string);
        $expected_tree->setFlattened($flattened_string);
        $nodeRepository = $this->getMockBuilder(NodeRepository::class)
            ->disableOriginalConstructor()
            ->getMock();

        $treeRepository = $this->getMockBuilder(TreeRepository::class)
            ->disableOriginalConstructor()
            ->getMock();

        $nodeRepository->expects(self::exactly(13))
            ->method('save');

        $treeRepository->expects(self::exactly(6))
            ->method('save')
            ->willReturn($expected_tree);

        $buildTree = new BuildTree($nodeRepository, $treeRepository);
        $tree = $buildTree->doAction($input_string);
        $this->assertSame($expected_tree->getDepth(), $tree->getDepth());
        $this->assertSame($expected_tree->getOriginal(), $tree->getOriginal());
        $this->assertSame($expected_tree->getFlattened(), $tree->getFlattened());
    }

    public function testFailEmpty() {
        $input_string = '';
        $flattened_string = '';
        $expected_tree = new Tree();
        $expected_tree->setDepth(0);
        $expected_tree->setOriginal($input_string);
        $expected_tree->setFlattened($flattened_string);
        $nodeRepository = $this->getMockBuilder(NodeRepository::class)
            ->disableOriginalConstructor()
            ->getMock();

        $treeRepository = $this->getMockBuilder(TreeRepository::class)
            ->disableOriginalConstructor()
            ->getMock();

        $nodeRepository->expects(self::exactly(0))
            ->method('save');

        $treeRepository->expects(self::exactly(1))
            ->method('save')
            ->willReturn($expected_tree);

        $buildTree = new BuildTree($nodeRepository, $treeRepository);
        $tree = $buildTree->doAction($input_string);
        $this->assertNull($tree);
    }

    public function testFailWrongStructure() {
        $input_string = '((34(20)))';
        $flattened_string = '';
        $expected_tree = new Tree();
        $expected_tree->setDepth(0);
        $expected_tree->setOriginal($input_string);
        $expected_tree->setFlattened($flattened_string);
        $nodeRepository = $this->getMockBuilder(NodeRepository::class)
            ->disableOriginalConstructor()
            ->getMock();

        $treeRepository = $this->getMockBuilder(TreeRepository::class)
            ->disableOriginalConstructor()
            ->getMock();

        $nodeRepository->expects(self::exactly(2))
            ->method('save');

        $treeRepository->expects(self::exactly(2))
            ->method('save')
            ->willReturn($expected_tree);

        $buildTree = new BuildTree($nodeRepository, $treeRepository);
        $tree = $buildTree->doAction($input_string);
        $this->assertNull($tree);
    }
}