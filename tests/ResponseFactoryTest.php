<?php
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;
use Lou117\Wake\ResponseFactory;
use Psr\Http\Message\ResponseInterface;

class ResponseFactoryTest extends TestCase
{
    public function testCreateHTMLResponse()
    {
        $code = 201;
        $body = "test";

        $response = ResponseFactory::createHTMLResponse($body, $code);
        $this->assertInstanceOf(ResponseInterface::class, $response);

        $this->assertEquals(
            "text/html",
            $response->getHeaderLine(ResponseFactory::HTTP_HEADER_CONTENT_TYPE)
        );
        $this->assertEquals($body, $response->getBody()->read($response->getBody()->getSize()));
        $this->assertEquals($code, $response->getStatusCode());
    }

    public function testCreateHTMLResponseWithEmptyString()
    {
        $response = ResponseFactory::createHTMLResponse("\n\t  ");
        $this->assertInstanceOf(ResponseInterface::class, $response);

        $this->assertFalse($response->hasHeader(ResponseFactory::HTTP_HEADER_CONTENT_TYPE));
        $this->assertEmpty($response->getBody()->read($response->getBody()->getSize()));
    }

    public function testCreateJSONResponse()
    {
        $code = 201;
        $json = ["test" => "test"];

        $response = ResponseFactory::createJSONResponse($json, $code);
        $this->assertInstanceOf(ResponseInterface::class, $response);

        $this->assertEquals(
            "application/json;charset=utf-8",
            $response->getHeaderLine(ResponseFactory::HTTP_HEADER_CONTENT_TYPE)
        );
        $this->assertEquals(
            $json,
            json_decode($response->getBody()->read($response->getBody()->getSize()), true)
        );
        $this->assertEquals($code, $response->getStatusCode());
    }

    public function testCreateJSONResponseWithInvalidJson()
    {
        $resource = tmpfile();
        $this->expectException(InvalidArgumentException::class);
        ResponseFactory::createJSONResponse($resource);
    }

    public function testCreateTextResponse()
    {
        $code = 201;
        $body = "test";

        $response = ResponseFactory::createTextResponse($body, $code);
        $this->assertInstanceOf(ResponseInterface::class, $response);

        $this->assertEquals(
            "text/plain",
            $response->getHeaderLine(ResponseFactory::HTTP_HEADER_CONTENT_TYPE)
        );
        $this->assertEquals($body, $response->getBody()->read($response->getBody()->getSize()));
        $this->assertEquals($code, $response->getStatusCode());
    }

    public function testCreateTextResponseWithEmptyString()
    {
        $response = ResponseFactory::createTextResponse("\n\t  ");
        $this->assertInstanceOf(ResponseInterface::class, $response);

        $this->assertFalse($response->hasHeader(ResponseFactory::HTTP_HEADER_CONTENT_TYPE));
        $this->assertEmpty($response->getBody()->read($response->getBody()->getSize()));
    }

    public function testCreateRedirectResponse()
    {
        $location = "https://google.com";

        $response = ResponseFactory::createRedirectResponse($location);
        $this->assertInstanceOf(ResponseInterface::class, $response);

        $this->assertEquals($location, $response->getHeaderLine("Location"));
        $this->assertEquals(302, $response->getStatusCode());
    }

    public function testCreateRedirectResponseWithEmptyLocation()
    {
        $this->expectException(InvalidArgumentException::class);
        ResponseFactory::createRedirectResponse("");
    }

    public function testIsEmptyResponse()
    {
        $response = new Response();
        $this->assertFalse(ResponseFactory::isEmptyResponse($response));

        $response = $response->withStatus(204);
        $this->assertTrue(ResponseFactory::isEmptyResponse($response));

        $response = $response->withStatus(205);
        $this->assertTrue(ResponseFactory::isEmptyResponse($response));

        $response = $response->withStatus(304);
        $this->assertTrue(ResponseFactory::isEmptyResponse($response));
    }
}
