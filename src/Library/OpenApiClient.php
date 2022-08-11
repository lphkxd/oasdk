<?php

declare(strict_types=1);
/**
 * This file is  UUOA of Hyperf.plus
 *
 * @link     https://www.hyperf.plus
 * @document https://doc.hyperf.plus
 * @contact  4213509@qq.com
 * @license  https://github.com/hyperf-plus/admin/blob/master/LICENSE
 */

namespace UUOA\Sdk\Library;


use GuzzleHttp\Client;

use Hyperf\Guzzle\HandlerStackFactory;
use Psr\Log\LoggerInterface;


class OpenApiClient
{
    /**
     * @var LoggerInterface
     */
    protected $logger;

    public function __construct(
        public ?string $app_id = null,
        protected ?string $app_secret = null,
        protected ?string $base_api = null,
    )
    {
        if (empty($app_id)) $this->app_id = config('sdk.app_id', '');
        if (empty($app_secret)) $this->app_secret = config('sdk.app_secret', '');
        if (empty($base_api)) $this->base_api = config('sdk.base_api');
    }

    /**
     * @param string $type
     * @return array|Client
     */
    public function getClient()
    {
        $factory = new HandlerStackFactory();
        $stack = $factory->create();
        return make(Client::class, [
            'config' => [
                'handler' => $stack,
                'base_uri' => $this->base_api,
                'use_pool' => false,
                'headers' => [
                    'Content-Type' => 'application/json',
                ],
            ],
        ]);
    }

    /**
     * @param $appid
     * @param $app_secret
     * @return bool
     * @throws GuzzleException
     */
    public function getAccessToken()
    {
        return cache_has_set('getAccessToken:' . $this->app_id, function () {
            $res = self::getClient()->get('/open/auth/token?appid=' . $this->app_id . '&app_secret=' . $this->app_secret);
            $content = $res->getBody()->getContents();
            if ($res->getStatusCode() != 200) {
                throw new \Exception('请求失败', $res->getStatusCode());
            }
            // 这里应该做发放成功失败的检测
            return json_decode($content, true)['access_token'] ?? '';
        }, 7200);

    }


    /**
     * @return bool
     * @throws GuzzleException
     */
    public function getJsTicket($access_token)
    {
        return cache_has_set('jssdk:ticket:' . md5($access_token), function () use ($access_token) {
            $res = self::getClient()->get('/open/apis/jssdk/ticket/get', [
                'headers' => [
                    # appid 秘钥 暂时先写死，后边分离出来后在正规化处理
                    'Authorization' => 'Bearer ' . $access_token,
                ],
            ]);
            $content = $res->getBody()->getContents();
            if ($res->getStatusCode() != 200) {
                throw new \Exception('获取js_ticket失败', $res->getStatusCode());
            }
            // 这里应该做发放成功失败的检测
            $res = json_decode($content, true);
            if (empty($res['ticket']) || empty($res['expire_in'])) {
                throw new \Exception('获取js_ticket失败', $res->getStatusCode());
            }
            return $res['ticket'];
        }, 1800);

    }

    /**
     * Sign the params.
     *
     * @param string $ticket
     * @param string $nonce
     * @param int $timestamp
     * @param string $url
     *
     * @return string
     */
    public static function getTicketSignature($ticket, $nonce, $timestamp, $url): string
    {
        return sha1(sprintf('jsapi_ticket=%s&noncestr=%s&timestamp=%s&url=%s', $ticket, $nonce, $timestamp, $url));
    }

    /**
     * Generate a "random" alpha-numeric string.
     *
     * Should not be considered sufficient for cryptography, etc.
     *
     * @param int $length
     *
     * @return string
     */
    public static function quickRandom($length = 16)
    {
        return substr(str_shuffle(str_repeat('0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ', $length)), 0, $length);
    }

}
