<?php


namespace UUOA\Sdk\Controller\Api\Sdk;


use HPlus\Route\Annotation\ApiController;
use HPlus\Route\Annotation\GetApi;
use HPlus\Route\Annotation\Query;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Contract\ResponseInterface;
use UUOA\Sdk\Library\OpenApiClient;

/**
 * Class Client
 * @package UUOA\Sdk\Controller
 */
#[ApiController()]
class Client
{
    #[Inject]
    protected RequestInterface $request;

    #[Inject]
    protected ResponseInterface $response;

    /**
     * @return array
     * @author wgy
     */
    #[GetApi(summary: "config")]
    #[Query(key: "url", name: "url")]
    public function config()
    {
        $url = $this->request->query('url');
        if (empty($url)) {
            throw new \Exception('url参数必传', 400);
        }
        /** @var OpenApiClient $openApi */
        $openApi = make(OpenApiClient::class);
        $access_token = $openApi->getAccessToken();
        $ticket = $openApi->getJsTicket($access_token);
        $nonce = OpenApiClient::quickRandom(10);
        $timestamp = get_millisecond();
        return [
            'appid' => $openApi->app_id,
            'nonceStr' => $nonce,
            'timeStamp' => $timestamp,
            'url' => $url,
            'signature' => OpenApiClient::getTicketSignature($ticket, $nonce, $timestamp, $url),
        ];
    }
}