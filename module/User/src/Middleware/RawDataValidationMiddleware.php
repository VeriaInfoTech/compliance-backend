<?php

declare(strict_types=1);

namespace Pi\User\Middleware;

use Fig\Http\Message\StatusCodeInterface;
use Laminas\InputFilter\Input;
use Laminas\InputFilter\InputFilter;
use Pi\Core\Handler\ErrorHandler;
use Pi\Core\Service\CacheService;
use Pi\Core\Service\ConfigService;
use Pi\Core\Service\UtilityService;
use Pi\User\Service\AccountService;
use Pi\User\Validator\EmailValidator;
use Pi\User\Validator\IdentityValidator;
use Pi\User\Validator\MobileValidator;
use Pi\User\Validator\NameValidator;
use Pi\User\Validator\OtpValidator;
use Pi\User\Validator\PasswordValidator;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

use function sprintf;

class RawDataValidationMiddleware implements MiddlewareInterface
{
    public array $validationResult = [
        'status'  => true,
        'code'    => StatusCodeInterface::STATUS_OK,
        'message' => '',
    ];

    protected ResponseFactoryInterface $responseFactory;
    protected StreamFactoryInterface $streamFactory;
    protected AccountService $accountService;
    protected UtilityService $utilityService;
    protected CacheService $cacheService;
    protected ConfigService $configService;
    protected ErrorHandler $errorHandler;

    public function __construct(
        ResponseFactoryInterface $responseFactory,
        StreamFactoryInterface $streamFactory,
        AccountService $accountService,
        UtilityService $utilityService,
        CacheService $cacheService,
        ConfigService $configService,
        ErrorHandler $errorHandler
    ) {
        $this->responseFactory = $responseFactory;
        $this->streamFactory   = $streamFactory;
        $this->accountService  = $accountService;
        $this->utilityService  = $utilityService;
        $this->cacheService    = $cacheService;
        $this->configService   = $configService;
        $this->errorHandler    = $errorHandler;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $routeMatch = $request->getAttribute('Laminas\Router\RouteMatch');
        $stream     = $this->streamFactory->createStreamFromFile('php://input');
        $rawData    = $stream->getContents();
        $parsedBody = json_decode($rawData, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            $request = $request->withAttribute('status', StatusCodeInterface::STATUS_FORBIDDEN);
            $request = $request->withAttribute(
                'error',
                [
                    'message' => 'Invalid JSON data !',
                    'code'    => StatusCodeInterface::STATUS_BAD_REQUEST,
                ]
            );
            return $this->errorHandler->handle($request);
        }

        $account     = $request->getAttribute('account');
        $routeParams = $routeMatch->getParams();

        switch ($routeParams['validator'] ?? '') {
            case 'global':
                break;
            case 'login':
                $this->loginIsValid($parsedBody);
                break;
            case 'add':
                $this->registerIsValid($parsedBody);
                break;
            case 'edit':
                $this->editIsValid($parsedBody, $account);
                break;
            case 'device-token':
                $this->deviceTokenIsValid($parsedBody, $account);
                break;
            case 'password-add':
                $this->passwordAddIsValid($parsedBody, $account);
                break;
            case 'password-update':
                $this->passwordEditIsValid($parsedBody);
                break;
            case 'password-admin':
                $this->passwordAdminIsValid($parsedBody);
                break;
            case 'email-request':
                $this->emailRequestIsValid($parsedBody);
                break;
            case 'email-verify':
                $this->emailVerifyIsValid($parsedBody);
                break;
            case 'mobile-request':
                $this->mobileRequestIsValid($parsedBody);
                break;
            case 'mobile-verify':
                $this->mobileVerifyIsValid($parsedBody);
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
        }

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

    protected function setErrorHandler($inputFilter): array
    {
        $message = [];
        foreach ($inputFilter->getInvalidInput() as $error) {
            $message[$error->getName()] = $error->getName() . ': ' . implode(', ', $error->getMessages());
        }
        return $this->validationResult = [
            'status'  => false,
            'code'    => StatusCodeInterface::STATUS_FORBIDDEN,
            'message' => implode(', ', $message),
        ];
    }

    protected function loginIsValid($params): void
    {
        $inputFilter = new InputFilter();
        if (isset($params['email']) && !empty($params['email'])) {
            $email = new Input('email');
            $email->getValidatorChain()->attach(new EmailValidator($this->accountService, ['check_duplication' => false]));
            $inputFilter->add($email);
        } elseif (isset($params['identity']) && !empty($params['identity'])) {
            $identity = new Input('identity');
            $identity->getValidatorChain()->attach(new IdentityValidator($this->accountService, ['check_duplication' => false]));
            $inputFilter->add($identity);
        } elseif (isset($params['mobile']) && !empty($params['mobile'])) {
            $option  = ['check_duplication' => false, 'country' => $params['country'] ?? 'IR'];
            $mobile  = new Input('mobile');
            $mobile->getValidatorChain()->attach(new MobileValidator($this->accountService, $option));
            $inputFilter->add($mobile);
        } else {
            $this->validationResult = [
                'status'  => false,
                'code'    => StatusCodeInterface::STATUS_FORBIDDEN,
                'message' => 'Login fields not set !',
            ];
            return;
        }
        $credential = new Input('credential');
        $credential->getValidatorChain()->attach(new PasswordValidator($this->accountService, $this->utilityService, $this->configService));
        $inputFilter->add($credential);
        $inputFilter->setData($params);
        if (!$inputFilter->isValid()) {
            $this->setErrorHandler($inputFilter);
        }
    }

    protected function registerIsValid($params): void
    {
        if (
            isset($params['first_name'], $params['last_name'])
            && !empty($params['first_name'])
            && !empty($params['last_name'])
        ) {
            $params['name'] = sprintf('%s %s', $params['first_name'], $params['last_name']);
        }
        $inputFilter = new InputFilter();
        if (isset($params['name']) && !empty($params['name'])) {
            $name = new Input('name');
            $name->getValidatorChain()->attach(new NameValidator($this->accountService));
            $inputFilter->add($name);
        }
        if (isset($params['email']) && !empty($params['email'])) {
            $email = new Input('email');
            $email->getValidatorChain()->attach(new EmailValidator($this->accountService));
            $inputFilter->add($email);
        }
        if (isset($params['mobile']) && !empty($params['mobile'])) {
            $option = ['check_duplication' => true, 'country' => $params['country'] ?? 'IR'];
            $mobile = new Input('mobile');
            $mobile->getValidatorChain()->attach(new MobileValidator($this->accountService, $option));
            $inputFilter->add($mobile);
        }
        if (isset($params['identity']) && !empty($params['identity'])) {
            $identity = new Input('identity');
            $identity->getValidatorChain()->attach(new IdentityValidator($this->accountService));
            $inputFilter->add($identity);
        }
        if (isset($params['credential']) && !empty($params['credential'])) {
            $credential = new Input('credential');
            $credential->getValidatorChain()->attach(new PasswordValidator($this->accountService, $this->utilityService, $this->configService));
            $inputFilter->add($credential);
        }
        $inputFilter->setData($params);
        if (!$inputFilter->isValid()) {
            $this->setErrorHandler($inputFilter);
        }
    }

    protected function editIsValid($params, $account): void
    {
        if (
            isset($params['first_name'], $params['last_name'])
            && !empty($params['first_name'])
            && !empty($params['last_name'])
        ) {
            $params['name'] = sprintf('%s %s', $params['first_name'], $params['last_name']);
        }
        $inputFilter = new InputFilter();
        $op = ['user_id' => $account['id']];
        if (isset($params['email']) && !empty($params['email'])) {
            $email = new Input('email');
            $email->getValidatorChain()->attach(new EmailValidator($this->accountService, $op));
            $inputFilter->add($email);
        }
        if (isset($params['name']) && !empty($params['name'])) {
            $name = new Input('name');
            $name->getValidatorChain()->attach(new NameValidator($this->accountService, $op));
            $inputFilter->add($name);
        }
        if (isset($params['identity']) && !empty($params['identity'])) {
            $identity = new Input('identity');
            $identity->getValidatorChain()->attach(new IdentityValidator($this->accountService, $op));
            $inputFilter->add($identity);
        }
        if (isset($params['mobile']) && !empty($params['mobile'])) {
            $mobile = new Input('mobile');
            $mobile->getValidatorChain()->attach(new MobileValidator($this->accountService, $op));
            $inputFilter->add($mobile);
        }
        $inputFilter->setData($params);
        if (!$inputFilter->isValid()) {
            $this->setErrorHandler($inputFilter);
        }
    }

    protected function deviceTokenIsValid($params, $account): void
    {
        if (!isset($params['device_token']) || empty($params['device_token']) || !is_string($params['device_token'])) {
            $this->validationResult = [
                'status'  => false,
                'code'    => StatusCodeInterface::STATUS_FORBIDDEN,
                'message' => 'Device token was not set or its wrong !',
            ];
        }
    }

    protected function passwordAddIsValid($params, $account): void
    {
        $option = ['user_id' => $params['user_id'] ?? $account['id'], 'check_has_password' => 1];
        $credential = new Input('credential');
        $credential->getValidatorChain()->attach(new PasswordValidator($this->accountService, $this->utilityService, $this->configService, $option));
        $inputFilter = new InputFilter();
        $inputFilter->add($credential);
        $inputFilter->setData($params);
        if (!$inputFilter->isValid()) {
            $this->setErrorHandler($inputFilter);
        }
    }

    protected function passwordEditIsValid($params): void
    {
        $currentCredential = new Input('current_credential');
        $currentCredential->getValidatorChain()->attach(new PasswordValidator($this->accountService, $this->utilityService, $this->configService));
        $newCredential = new Input('new_credential');
        $newCredential->getValidatorChain()->attach(new PasswordValidator($this->accountService, $this->utilityService, $this->configService));
        $inputFilter = new InputFilter();
        $inputFilter->add($currentCredential);
        $inputFilter->add($newCredential);
        $inputFilter->setData($params);
        if (!$inputFilter->isValid()) {
            $this->setErrorHandler($inputFilter);
        }
    }

    protected function passwordAdminIsValid($params): void
    {
        $credential = new Input('credential');
        $credential->getValidatorChain()->attach(new PasswordValidator($this->accountService, $this->utilityService, $this->configService));
        $inputFilter = new InputFilter();
        $inputFilter->add($credential);
        $inputFilter->setData($params);
        if (!$inputFilter->isValid()) {
            $this->setErrorHandler($inputFilter);
        }
    }

    protected function emailRequestIsValid($params): void
    {
        $email = new Input('email');
        $email->getValidatorChain()->attach(new EmailValidator($this->accountService, ['check_duplication' => false]));
        $inputFilter = new InputFilter();
        $inputFilter->add($email);
        $inputFilter->setData($params);
        if (!$inputFilter->isValid()) {
            $this->setErrorHandler($inputFilter);
        }
    }

    protected function emailVerifyIsValid($params): void
    {
        $email = new Input('email');
        $email->getValidatorChain()->attach(new EmailValidator($this->accountService, ['check_duplication' => false]));
        $otp = new Input('otp');
        $otp->getValidatorChain()->attach(new OtpValidator($this->accountService, $this->cacheService, ['email' => $params['email']]));
        $inputFilter = new InputFilter();
        $inputFilter->add($email);
        $inputFilter->add($otp);
        $inputFilter->setData($params);
        if (!$inputFilter->isValid()) {
            $this->setErrorHandler($inputFilter);
        }
    }

    protected function mobileRequestIsValid($params): void
    {
        $option = ['check_duplication' => false, 'country' => $params['country'] ?? 'IR'];
        $mobile = new Input('mobile');
        $mobile->getValidatorChain()->attach(new MobileValidator($this->accountService, $option));
        $inputFilter = new InputFilter();
        $inputFilter->add($mobile);
        $inputFilter->setData($params);
        if (!$inputFilter->isValid()) {
            $this->setErrorHandler($inputFilter);
        }
    }

    protected function mobileVerifyIsValid($params): void
    {
        $option = ['check_duplication' => false, 'country' => $params['country'] ?? 'IR'];
        $mobile = new Input('mobile');
        $mobile->getValidatorChain()->attach(new MobileValidator($this->accountService, $option));
        $otp = new Input('otp');
        $otp->getValidatorChain()->attach(new OtpValidator($this->accountService, $this->cacheService, ['mobile' => $params['mobile']]));
        $inputFilter = new InputFilter();
        $inputFilter->add($mobile);
        $inputFilter->add($otp);
        $inputFilter->setData($params);
        if (!$inputFilter->isValid()) {
            $this->setErrorHandler($inputFilter);
        }
    }
}
