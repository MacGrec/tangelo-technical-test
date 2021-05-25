<?php


namespace App\Tests\Functional;


use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class ListActionTest extends WebTestCase
{
    public function testSuccessGetTreeList() {
        $client = static::createClient();
        $client->request(
            'GET',
            '/api/tree/list',
            [],
            [],
            ['CONTENT_TYPE' => 'text/html']
        );

        $response_code = $client->getResponse()->getStatusCode();
        $response_content = json_decode($client->getResponse()->getContent());
        $this->assertSame(Response::HTTP_ACCEPTED, $response_code);
        $this->assertIsArray($response_content);
        $this->assertLessThanOrEqual(100, sizeof($response_content));
        foreach ($response_content as $tree) {
            $tree_as_array = get_object_vars($tree);
            $this->assertArrayHasKey('original', $tree_as_array);
            $this->assertNotNull($tree->original);
            $this->assertIsString($tree->original);
            $this->assertNotEmpty($tree->original);
            $this->assertNotNull($tree->flattened);
            $this->assertIsString($tree->flattened);
            $this->assertNotEmpty($tree->flattened);
            $this->assertArrayHasKey('flattened', $tree_as_array);
            $this->assertNotNull($tree->depth);
            $this->assertIsInt($tree->depth);
            $this->assertGreaterThanOrEqual(0, $tree->depth);
            $this->assertArrayHasKey('depth', $tree_as_array);
            $this->assertNotNull($tree->created_at);
            $this->assertIsString($tree->created_at);
            $this->assertNotEmpty($tree->created_at);
            $this->assertArrayHasKey('created_at', $tree_as_array);
            $this->assertArrayNotHasKey('id', $tree_as_array);
            $this->assertArrayNotHasKey('node', $tree_as_array);
        }
    }
}