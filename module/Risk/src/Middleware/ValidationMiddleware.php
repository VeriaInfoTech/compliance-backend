<?php

namespace Risk\Middleware;

use Fig\Http\Message\StatusCodeInterface;
use Laminas\Validator\File\Extension;
use Laminas\Validator\File\MimeType;
use Laminas\Validator\File\Size;
use Laminas\Validator\File\UploadFile;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Pi\Core\Handler\ErrorHandler;

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
        $uploadFiles = $request->getUploadedFiles();
        $routeMatch  = $request->getAttribute('Laminas\Router\RouteMatch');
        $routeParams = $routeMatch->getParams();

        // Check parsedBody
        switch ($routeParams['validator']) {
            case 'attache':
                $this->attacheIsValid($parsedBody, $uploadFiles);
                break;

            default:
                $request = $request->withAttribute('status', StatusCodeInterface::STATUS_FORBIDDEN);
                $request = $request->withAttribute('error',
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
            $request = $request->withAttribute('error',
                [
                    'message' => $this->validationResult['message'],
                    'code'    => $this->validationResult['code'],
                ]
            );
            return $this->errorHandler->handle($request);
        }

        return $handler->handle($request);
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

    protected function attacheIsValid($parsedBody, $uploadFiles)
    {
        $validatorUpload    = new UploadFile();
        $validatorExtension = new Extension(
            [
                'csv',
                'xlsx',
                'xls',
            ]
        );
        $validatorMimeType  = new MimeType([
            'application/vnd.ms-excel',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'text/csv',
            'application/vnd.oasis.opendocument.spreadsheet',
        ]);
        $validatorSize      = new Size(
            [
                'min' => '1kB',
                'max' => '4MB',
            ]
        );

        // Check attached files
        foreach ($uploadFiles as $uploadFile) {
            if (!$validatorUpload->isValid($uploadFile)) {
                return $this->setErrorHandler($validatorUpload);
            }
            if (!$validatorExtension->isValid($uploadFile)) {
                return $this->setErrorHandler($validatorExtension);
            }
            /* if (!$validatorMimeType->isValid($uploadFile)) {
                return $this->setErrorHandler($validatorMimeType);
            } */
            if (!$validatorSize->isValid($uploadFile)) {
                return $this->setErrorHandler($validatorUpload);
            }
        }
    }
}