<?php

namespace App\Controller\Api;

use App\Controller\Service\BuildTree;
use FOS\RestBundle\View\View;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class TreeController extends AbstractFOSRestController
{
    /**
     * @Rest\Post(path="/convert", requirements={"id"="\d+"})
     * @Rest\View(serializerGroups={"convert"}, serializerEnableMaxDepthChecks=true)
     */
    public function convertAction(Request $request, BuildTree $buildTree)
    {
        $request_content = $request->getContent();
        $response = $buildTree->doAction($request_content);
        return View::create($response, Response::HTTP_ACCEPTED);
    }
}

