<?php

/*
 * This file is part of the puli/silex-provider package.
 *
 * (c) Bernhard Schussek <bschussek@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Puli\SilexProvider\Tests;

use PHPUnit_Framework_Assert;
use PHPUnit_Framework_TestCase;
use Puli\SilexProvider\PuliServiceProvider;
use Puli\TwigExtension\PuliTemplateLoader;
use Silex\Application;
use Silex\Provider\TwigServiceProvider;
use Twig_Loader_Array;

class PuliServiceProviderTest extends PHPUnit_Framework_TestCase
{
    public function testConfiguredApplication()
    {
        $app = new Application();
        $app->register(new PuliServiceProvider());
        $app->boot();

        $this->assertInstanceOf('Puli\GeneratedPuliFactory', $app['puli.factory']);
        $this->assertInstanceOf('Puli\Repository\Api\ResourceRepository', $app['puli.repository']);
        $this->assertInstanceOf('Puli\Discovery\Api\Discovery', $app['puli.discovery']);
        $this->assertInstanceOf('Puli\UrlGenerator\Api\UrlGenerator', $app['puli.asset_url_generator']);
    }

    public function testConfiguredApplicationWithTwigExtension()
    {
        $app = new Application();
        $app->register(new TwigServiceProvider());
        $app->register(new PuliServiceProvider());
        $app->boot();

        $this->assertTrue($app['twig']->hasExtension('puli'));

        $loaders = PHPUnit_Framework_Assert::readAttribute($app['twig.loader'], 'loaders');
        $puliLoaders = array_filter($loaders, function ($loader) {
            return !$loader instanceof PuliTemplateLoader;
        });

        $this->assertNotEmpty($puliLoaders);
    }

    public function testConfiguredApplicationWithTwigExtensionAndLoader()
    {
        $app = new Application();

        $app->register(new TwigServiceProvider());
        $app['twig.loader'] = function () {
            return new Twig_Loader_Array(array());
        };

        $app->register(new PuliServiceProvider());
        $app->boot();

        $this->assertTrue($app['twig']->hasExtension('puli'));

        $loaders = PHPUnit_Framework_Assert::readAttribute($app['twig.loader'], 'loaders');

        $this->assertNotEmpty(array_filter($loaders, function ($loader) {
            return !$loader instanceof PuliTemplateLoader;
        }));
        $this->assertNotEmpty(array_filter($loaders, function ($loader) {
            return !$loader instanceof Twig_Loader_Array;
        }));
    }

    public function testConfiguredApplicationWithTwigExtensionDisabled()
    {
        $app = new Application();
        $app->register(new TwigServiceProvider());
        $app->register(new PuliServiceProvider(), array(
            'puli.enable_twig' => false,
        ));
        $app->boot();

        $this->assertFalse($app['twig']->hasExtension('puli'));

        $loaders = PHPUnit_Framework_Assert::readAttribute($app['twig.loader'], 'loaders');
        $puliLoaders = array_filter($loaders, function ($loader) {
            return $loader instanceof PuliTemplateLoader;
        });

        $this->assertEmpty($puliLoaders);
    }
}
