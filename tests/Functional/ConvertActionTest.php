<?php


namespace App\Tests\Functional;


use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class ConvertActionTest extends WebTestCase
{

    const MESSAGE_WRONG_INPUT = 'Input array structure is not correct';

    public function testSuccessConvertTreeDepthZero() {
        $client = static::createClient();
        $input = '(10;20;30;40)';
        $output = '(10;20;30;40)';
        $client->request(
            'POST',
            '/api/tree/flatten',
            [],
            [],
            ['CONTENT_TYPE' => 'text/html'],
            $input
        );

        $response_code = $client->getResponse()->getStatusCode();
        $response_content = json_decode($client->getResponse()->getContent());
        $this->assertSame(Response::HTTP_ACCEPTED, $response_code);
        $this->assertIsInt($response_content->depth);
        $this->assertSame(0, $response_content->depth);
        $this->assertSame($output, $response_content->flattened);
    }

    public function testSuccessConvertTreeDepthOne() {
        $client = static::createClient();
        $input = '((10;20;30);40)';
        $output = '(10;20;30;40)';
        $client->request(
            'POST',
            '/api/tree/flatten',
            [],
            [],
            ['CONTENT_TYPE' => 'text/html'],
            $input
        );

        $response_code = $client->getResponse()->getStatusCode();
        $response_content = json_decode($client->getResponse()->getContent());
        $this->assertSame(Response::HTTP_ACCEPTED, $response_code);
        $this->assertIsInt($response_content->depth);
        $this->assertSame(1, $response_content->depth);
        $this->assertSame($output, $response_content->flattened);
    }

    public function testSuccessConvertTreeDepthTwo() {
        $client = static::createClient();
        $input = '((A;20;(B));40)';
        $output = '(A;20;B;40)';
        $client->request(
            'POST',
            '/api/tree/flatten',
            [],
            [],
            ['CONTENT_TYPE' => 'text/html'],
            $input
        );

        $response_code = $client->getResponse()->getStatusCode();
        $response_content = json_decode($client->getResponse()->getContent());
        $this->assertSame(Response::HTTP_ACCEPTED, $response_code);
        $this->assertIsInt($response_content->depth);
        $this->assertSame(2, $response_content->depth);
        $this->assertSame($output, $response_content->flattened);
    }

    public function testSuccessConvertTreeDepthFour() {
        $client = static::createClient();
        $input = '((10;((20;(30)));(40)))';
        $output = '(10;20;30;40)';
        $client->request(
            'POST',
            '/api/tree/flatten',
            [],
            [],
            ['CONTENT_TYPE' => 'text/html'],
            $input
        );

        $response_code = $client->getResponse()->getStatusCode();
        $response_content = json_decode($client->getResponse()->getContent());
        $this->assertSame(Response::HTTP_ACCEPTED, $response_code);
        $this->assertIsInt($response_content->depth);
        $this->assertSame(4, $response_content->depth);
        $this->assertSame($output, $response_content->flattened);
    }

    public function testFailConvertTreeWrongData() {
        $client = static::createClient();
        $input = '((34(20)))';
        $client->request(
            'POST',
            '/api/tree/flatten',
            [],
            [],
            ['CONTENT_TYPE' => 'text/html'],
            $input
        );

        $response_code = $client->getResponse()->getStatusCode();
        $response_content = json_decode($client->getResponse()->getContent());
        $this->assertSame(Response::HTTP_BAD_REQUEST, $response_code);
        $this->assertSame(self::MESSAGE_WRONG_INPUT, $response_content->message);
    }

    public function testFailConvertTreeEmptyData() {
        $client = static::createClient();
        $input = '';
        $client->request(
            'POST',
            '/api/tree/flatten',
            [],
            [],
            ['CONTENT_TYPE' => 'text/html'],
            $input
        );

        $response_code = $client->getResponse()->getStatusCode();
        $response_content = json_decode($client->getResponse()->getContent());
        $this->assertSame(Response::HTTP_BAD_REQUEST, $response_code);
        $this->assertSame(self::MESSAGE_WRONG_INPUT, $response_content->message);
    }
}