<?php

namespace Content\Middleware;

use Content\Validator\SlugValidator;
use Content\Validator\TypeValidator;
use Fig\Http\Message\StatusCodeInterface;
use Laminas\InputFilter\Input;
use Laminas\InputFilter\InputFilter;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use User\Handler\ErrorHandler;
use function implode;

class ValidationMiddleware implements MiddlewareInterface
{
    public array $validationResult
        = [
            'status'  => true,
            'code'    => StatusCodeInterface::STATUS_OK,
            'message' => '',
        ];

    /** @var ResponseFactoryInterface */
    protected ResponseFactoryInterface $responseFactory;

    /** @var StreamFactoryInterface */
    protected StreamFactoryInterface $streamFactory;

    /** @var ErrorHandler */
    protected ErrorHandler $errorHandler;

    public function __construct(
        ResponseFactoryInterface $responseFactory,
        StreamFactoryInterface $streamFactory,
        ErrorHandler $errorHandler
    ) {
        $this->responseFactory = $responseFactory;
        $this->streamFactory   = $streamFactory;
        $this->errorHandler    = $errorHandler;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        // Get information from request
        $parsedBody  = $request->getParsedBody();
        $routeMatch  = $request->getAttribute('Laminas\Router\RouteMatch');
        $routeParams = $routeMatch->getParams();

        // Check parsedBody
        switch ($routeParams['validator']) {
            case 'list':
                $this->listIsValid($parsedBody);
                break;

            case 'detail':
                $this->detailIsValid($parsedBody);
                break;

            case 'reserve':
                $this->reserveIsValid($parsedBody);
                break;

            default:
                $request = $request->withAttribute('status', StatusCodeInterface::STATUS_FORBIDDEN);
                $request = $request->withAttribute(
                    'error',
                    [
                        'message' => 'Validator not set !',
                        'code'    => StatusCodeInterface::STATUS_FORBIDDEN,
                    ]
                );
                return $this->errorHandler->handle($request);
                break;
        }

        // Check if validation result is not true
        if (!$this->validationResult['status']) {
            $request = $request->withAttribute('status', $this->validationResult['code']);
            $request = $request->withAttribute(
                'error',
                [
                    'message' => $this->validationResult['message'],
                    'code'    => $this->validationResult['code'],
                ]
            );
            return $this->errorHandler->handle($request);
        }

        return $handler->handle($request);
    }

    protected function listIsValid($params)
    {
        $type = new Input('type');
        $type->getValidatorChain()->attach(new TypeValidator());

        $inputFilter = new InputFilter();
        $inputFilter->add($type);
        $inputFilter->setData($params);

        if (!$inputFilter->isValid()) {
            return $this->setErrorHandler($inputFilter);
        }
    }

    protected function setErrorHandler($inputFilter): array
    {
        $message = [];
        foreach ($inputFilter->getInvalidInput() as $error) {
            $message[$error->getName()] = implode(', ', $error->getMessages());
        }

        return $this->validationResult = [
            'status'  => false,
            'code'    => StatusCodeInterface::STATUS_FORBIDDEN,
            'message' => $message,
        ];
    }

    protected function detailIsValid($params)
    {
        $slug = new Input('slug');
        $slug->getValidatorChain()->attach(new SlugValidator());

        $inputFilter = new InputFilter();
        $inputFilter->add($slug);
        $inputFilter->setData($params);

        if (!$inputFilter->isValid()) {
            return $this->setErrorHandler($inputFilter);
        }
    }

    protected function reserveIsValid($params)
    {
        $code = new Input('code');
        $itemId = new Input('item_id');
        $code->getValidatorChain()->attach(new SlugValidator());
        $itemId->getValidatorChain()->attach(new SlugValidator());

        $inputFilter = new InputFilter();
        $inputFilter->add($code);
        $inputFilter->add($itemId);
        $inputFilter->setData($params);

        if (!$inputFilter->isValid()) {
            return $this->setErrorHandler($inputFilter);
        }
    }
}