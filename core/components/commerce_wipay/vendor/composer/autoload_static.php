<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit2da315b28792c77fdcf70adebb10827d
{
    public static $prefixLengthsPsr4 = array (
        'm' => 
        array (
            'modmore\\WiPay\\' => 14,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'modmore\\WiPay\\' => 
        array (
            0 => __DIR__ . '/../..' . '/src',
        ),
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit2da315b28792c77fdcf70adebb10827d::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit2da315b28792c77fdcf70adebb10827d::$prefixDirsPsr4;

        }, null, ClassLoader::class);
    }
}
