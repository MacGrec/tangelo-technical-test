<?php


namespace App\Controller\Service;

use App\Repository\TreeRepository;
use Doctrine\Common\Collections\Collection;

class GetTreeList
{
    const MAX_NUMBER_RESULTS = 100;
    const ORDER_CRITERIA = 'created_at';
    private TreeRepository $treeRepository;

    public function __construct(TreeRepository $treeRepository) {
        $this->treeRepository = $treeRepository;
    }

    public function doAction(): Collection
    {
        return  $this->treeRepository->findOrderBy(self::ORDER_CRITERIA, self::MAX_NUMBER_RESULTS);
    }
}