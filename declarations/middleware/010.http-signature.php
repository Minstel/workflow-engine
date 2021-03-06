<?php declare(strict_types=1);

use Jasny\RouterInterface;
use Jasny\HttpDigest\HttpDigest;
use Jasny\HttpDigest\ServerMiddleware as HttpDigestMiddleware;
use Jasny\HttpSignature\HttpSignature;
use Jasny\HttpSignature\ServerMiddleware as HttpSignatureMiddleware;
use Psr\Container\ContainerInterface;
use LTO\Account;
use LTO\Account\ServerMiddleware as AccountMiddleware;
use LTO\AccountFactory;

return [
    static function (RouterInterface $router, ContainerInterface $container) {
        $service = $container->get(HttpDigest::class);
        $middleware = (new HttpDigestMiddleware($service))
            ->withOptionalDigest((bool)$container->get('config.debug'));

        return $middleware->asDoublePass();
    },
    static function (RouterInterface $router, ContainerInterface $container) {
        $service = $container->get(HttpSignature::class);
        $middleware = new HttpSignatureMiddleware($service);

        return $middleware->asDoublePass();
    },
    static function (RouterInterface $router, ContainerInterface $container) {
        $account = $container->get(Account::class);
        $accountFactory = $container->get(AccountFactory::class);

        $middleware = (new AccountMiddleware($accountFactory))->withTrustedAccount($account);

        return $middleware->asDoublePass();
    },
];
