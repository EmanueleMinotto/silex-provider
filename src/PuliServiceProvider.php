<?php

/*
 * This file is part of the puli/silex-provider package.
 *
 * (c) Bernhard Schussek <bschussek@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Puli\SilexProvider;

use Puli\TwigExtension\PuliExtension;
use Puli\TwigExtension\PuliTemplateLoader;
use Silex\Application;
use Silex\ServiceProviderInterface;

class PuliServiceProvider implements ServiceProviderInterface
{
    /**
     * Registers services and parameters on the app.
     *
     * @param Application $app Silex application.
     */
    public function register(Application $app)
    {
        // flag for Twig extension and loader
        $app['puli.enable_twig'] = true;

        $app['puli.factory'] = $app->share(function (Application $app) {
            $factoryClass = PULI_FACTORY_CLASS;

            return new $factoryClass();
        });

        $app['puli.repository'] = $app->share(function (Application $app) {
            return $app['puli.factory']->createRepository();
        });

        $app['puli.discovery'] = $app->share(function (Application $app) {
            return $app['puli.factory']->createDiscovery($app['puli.repository']);
        });

        $app['puli.asset_url_generator'] = $app->share(function (Application $app) {
            return $app['puli.factory']->createUrlGenerator($app['puli.discovery']);
        });
    }

    /**
     * Bootstraps the services.
     *
     * @param Application $app Silex application.
     */
    public function boot(Application $app)
    {
        if (isset($app['twig']) && $app['puli.enable_twig']) {
            $app['twig.loader'] = $app->share(function (Application $app) {
                return new PuliTemplateLoader($app['puli.repository']);
            });

            $app['twig']->addExtension(new PuliExtension($app['puli.repository'], $app['puli.asset_url_generator']));
        }
    }
}
