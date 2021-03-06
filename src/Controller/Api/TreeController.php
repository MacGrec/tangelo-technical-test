<?php

namespace App\Controller\Api;

use App\Service\BuildTree;
use App\Service\GetTreeList;
use FOS\RestBundle\View\View;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class TreeController extends AbstractFOSRestController
{
    const INPUT_ARRAY_STRUCTURE_IS_NOT_CORRECT = "Input array structure is not correct";
    const MESSAGE_KEY = "message";
    const CODE_KEY = "code";

    /**
     * @Rest\Post(path="/tree/flatten", requirements={"id"="\d+"})
     * @Rest\View(serializerGroups={"convert"}, serializerEnableMaxDepthChecks=true)
     */
    public function convertAction(
        Request $request,
        BuildTree $buildTree
    )
    {
        $request_content = $request->getContent();
        if (empty($request_content)) {
            [$response, $response_code] = $this->buildBadRequest();
        } else {
            $response = $buildTree->doAction($request_content);
            $response_code = Response::HTTP_ACCEPTED;
            if(is_null($response)) {
                [$response, $response_code] = $this->buildBadRequest();
            }
        }

        return View::create($response, $response_code);
    }

    /**
     * @Rest\Get(path="/tree/list")
     * @Rest\View(serializerGroups={"tree"}, serializerEnableMaxDepthChecks=true)
     */
    public function listAction(GetTreeList $getTreeList)
    {
        $response = $getTreeList->doAction();
        return View::create($response, Response::HTTP_ACCEPTED);
    }

    private function buildBadRequest(): array
    {
        $response_code = Response::HTTP_BAD_REQUEST;
        $response = [self::CODE_KEY => $response_code, self::MESSAGE_KEY => self::INPUT_ARRAY_STRUCTURE_IS_NOT_CORRECT];
        return [$response, $response_code];
    }
}

