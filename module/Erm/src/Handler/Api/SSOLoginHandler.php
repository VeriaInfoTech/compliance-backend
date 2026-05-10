<?php

namespace Erm\Handler\Api;

use Erm\Service\TaskService;
use Fig\Http\Message\StatusCodeInterface;
use JetBrains\PhpStorm\NoReturn;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Pi\User\Handler\Api\Authentication\Oauth\Oauth2Handler;
use Pi\User\Handler\Api\Authentication\Oauth\SettingHandler;

class
SSOLoginHandler implements RequestHandlerInterface
{
    /** @var ResponseFactoryInterface */
    protected ResponseFactoryInterface $responseFactory;

    /** @var StreamFactoryInterface */
    protected StreamFactoryInterface $streamFactory;
    /** @var Oauth2Handler */
    protected Oauth2Handler $oauth2Handler;


    /* @var array */
    protected array $config;

    protected ServerRequestInterface $rawData;
    public function __construct(
        ResponseFactoryInterface $responseFactory,
        StreamFactoryInterface   $streamFactory,
        Oauth2Handler   $oauth2Handler,
        $config
    )
    {
        $this->responseFactory = $responseFactory;
        $this->streamFactory = $streamFactory;
        $this->oauth2Handler = $oauth2Handler;
        $this->config          = $config;
    }

    /**
     * @param ServerRequestInterface $request
     *
     * @return ResponseInterface
     */
    #[NoReturn] public function handle(ServerRequestInterface $request): ResponseInterface
    {


        $requestBody = $request->getParsedBody();

        $authorizeUrl = sprintf(
            $this->config['authorize_url'],
            $this->config['client_id'],
            $this->config['response_type'],
            $this->config['scope'],
            $this->config['redirect_uri'],
            $this->config['state'],
            $this->config['nonce'],
            $this->config['response_mode'],
        );

        if(isset($requestBody['code'])){
            $response =  $this->oauth2Handler->handle($request);
            $content = $response->getBody()->getContents();
            $data = json_decode($content, true);
            $token = 'invalid';
            if($data['result']){
                $token = $data['data']['access_token'];
            }
            $target_rul = sprintf($this->config['client_login_url'],$token);
            header(sprintf('Location:%s',$target_rul));

        }else{
            header(sprintf('Location:%s',$authorizeUrl));
        }
        exit();

    }
}