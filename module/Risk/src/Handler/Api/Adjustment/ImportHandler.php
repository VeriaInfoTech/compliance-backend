<?php

namespace Risk\Handler\Api\Adjustment;

use Laminas\Diactoros\Response\JsonResponse;
use Laminas\Math\Rand;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Risk\Service\AdjustmentService;
use Risk\Service\FormService;
use Risk\Service\SpreadsheetService;

class ImportHandler implements RequestHandlerInterface
{
    /** @var ResponseFactoryInterface */
    protected ResponseFactoryInterface $responseFactory;

    /** @var StreamFactoryInterface */
    protected StreamFactoryInterface $streamFactory;

    /** @var AdjustmentService */
    protected AdjustmentService $adjustmentService;

    /** @var FormService */
    protected FormService $formService;

    /* @var SpreadsheetService */
    protected SpreadsheetService $spreadsheetService;

    public function __construct(
        ResponseFactoryInterface $responseFactory,
        StreamFactoryInterface $streamFactory,
        AdjustmentService $adjustmentService,
        FormService $formService,
        SpreadsheetService $spreadsheetService
    ) {
        $this->responseFactory    = $responseFactory;
        $this->streamFactory      = $streamFactory;
        $this->adjustmentService  = $adjustmentService;
        $this->formService        = $formService;
        $this->spreadsheetService = $spreadsheetService;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        // Get account
        $account = $request->getAttribute('account');

        // Get uploaded file
        $uploadFiles = $request->getUploadedFiles();
        $uploadFile  = array_shift($uploadFiles);

        // Save file
        $fileInfo = pathinfo($uploadFile->getClientFilename());
        $fileName = sprintf('import-%s-%s.%s', date('Y-m-d-H-i-s'), rand(1000, 9999), $fileInfo['extension']);
        $filePath = realpath(__DIR__ . '/../../../../../../data/upload');
        $fullPath = sprintf('%s/%s', $filePath, $fileName);
        $uploadFile->moveTo($fullPath);

        // Set params
        $params = [
            'extension' => $fileInfo['extension'],
            'path'      => $fullPath,
        ];

        // Read data from file
        $data = $this->spreadsheetService->readFile($params);

        // Set input params
        $inputParams = [
            'user_id' => $account['id'],
            'form_id' => 0,
            'data'    => $data,
        ];

        // Save
        $result = $this->formService->saveInput($inputParams);

        // Set result
        $result = [
            'result' => true,
            'data'   => $result,
            'error'  => [],
        ];

        return new JsonResponse($result);
    }
}