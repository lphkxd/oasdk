<?php

declare(strict_types=1);
/**
 * This file is UUOA of Hyperf.plus
 *
 * @link     https://www.hyperf.plus
 * @document https://doc.hyperf.plus
 * @contact  4213509@qq.com
 * @license  https://github.com/hyperf-plus/admin/blob/master/LICENSE
 */

namespace UUOA\Sdk;

class ConfigProvider
{
    public function __invoke(): array
    {
        return [
            'annotations' => [
                'scan' => [
                    'paths' => [
                        __DIR__,
                    ],
                ],
            ],
            'publish' => [
                [
                    'id' => 'sdk',
                    'description' => 'sdk',
                    'source' => __DIR__ . '/../publish/sdk.php',
                    'destination' => BASE_PATH . '/config/autoload/sdk.php',
                ]
            ],
        ];
    }
}