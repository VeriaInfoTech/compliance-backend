<?php

namespace Pi\Logger\Middleware;

use Pi\Core\Handler\ErrorHandler;
use Pi\Core\Service\UtilityService;
use Pi\Logger\Service\LoggerService;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class LoggerRequestResponseMiddleware implements MiddlewareInterface
{
    /** @var ResponseFactoryInterface */
    protected ResponseFactoryInterface $responseFactory;

    /** @var StreamFactoryInterface */
    protected StreamFactoryInterface $streamFactory;

    /** @var UtilityService */
    protected UtilityService $utilityService;

    /** @var ErrorHandler */
    protected ErrorHandler $errorHandler;

    /** @var LoggerService */
    protected LoggerService $loggerService;

    public function __construct(
        ResponseFactoryInterface $responseFactory,
        StreamFactoryInterface   $streamFactory,
        UtilityService           $utilityService,
        ErrorHandler             $errorHandler,
        LoggerService            $loggerService
    ) {
        $this->responseFactory = $responseFactory;
        $this->streamFactory   = $streamFactory;
        $this->utilityService  = $utilityService;
        $this->errorHandler    = $errorHandler;
        $this->loggerService   = $loggerService;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        // Call the next middleware or handler
        $response = $handler->handle($request);

        // Post-handler logic
        $this->writeRequestResponse($request, $response);

        return $response;
    }

    private function writeRequestResponse(ServerRequestInterface $request, ResponseInterface $response): void
    {
        // Get attributes
        $attributes = $request->getAttributes();

        // Get route information
        $routeMatch  = $request->getAttribute('Laminas\Router\RouteMatch');
        $routeParams = $routeMatch->getParams();

        // Set path
        $path = sprintf(
            '%s-%s-%s-%s',
            $routeParams['module'],
            $routeParams['section'],
            $routeParams['package'],
            $routeParams['handler']
        );

        // Set log params
        $params = [
            'path'        => $path,
            'message'     => $routeParams['title'] ?? '',
            'user_id'     => $attributes['account']['id'] ?? 0,
            'company_id'  => $attributes['company_authorization']['company_id'] ?? 0,
            'ip'          => $this->utilityService->getClientIp(),
            'route'       => $routeParams,
            'timestamp'   => $this->utilityService->getTime(),
            'time_create' => time(),
            'request'     => [
                'method'          => $request->getMethod(),
                'uri'             => (string)$request->getUri(),
                'headers'         => $request->getHeaders(),
                'body'            => (string)$request->getBody(),
                'protocolVersion' => $request->getProtocolVersion(),
                'serverParams'    => $request->getServerParams(),
                'queryParams'     => $request->getQueryParams(),
                'parsedBody'      => $request->getParsedBody(),
                'uploadedFiles'   => $request->getUploadedFiles(),
                'cookies'         => $request->getCookieParams(),
                'attributes'      => $request->getAttributes(),
                'target'          => $request->getRequestTarget(),
            ],
            'response'    => [
                //'body'            => $response->getBody(),
                'headers'         => $response->getHeaders(),
                'protocolVersion' => $response->getProtocolVersion(),
                'encodingOptions' => $response->getEncodingOptions(),
                //'payload'         => $response->getPayload(),
                'reasonPhrase'    => $response->getReasonPhrase(),
                'statusCode'      => $response->getStatusCode(),
            ],
        ];

        // Set log
        $this->loggerService->write($params);
    }
}