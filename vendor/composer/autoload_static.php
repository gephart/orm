<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit79fab1cb3435572075ce6541b91acaaa
{
    public static $prefixLengthsPsr4 = array (
        'P' => 
        array (
            'Psr\\Container\\' => 14,
        ),
        'G' => 
        array (
            'Gephart\\ORM\\' => 12,
            'Gephart\\EventManager\\' => 21,
            'Gephart\\DependencyInjection\\' => 28,
            'Gephart\\Configuration\\' => 22,
            'Gephart\\Annotation\\' => 19,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'Psr\\Container\\' => 
        array (
            0 => __DIR__ . '/..' . '/psr/container/src',
        ),
        'Gephart\\ORM\\' => 
        array (
            0 => __DIR__ . '/../..' . '/src',
        ),
        'Gephart\\EventManager\\' => 
        array (
            0 => __DIR__ . '/..' . '/gephart/event-manager/src',
        ),
        'Gephart\\DependencyInjection\\' => 
        array (
            0 => __DIR__ . '/..' . '/gephart/dependency-injection/src',
        ),
        'Gephart\\Configuration\\' => 
        array (
            0 => __DIR__ . '/..' . '/gephart/configuration/src',
        ),
        'Gephart\\Annotation\\' => 
        array (
            0 => __DIR__ . '/..' . '/gephart/annotation/src',
        ),
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit79fab1cb3435572075ce6541b91acaaa::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit79fab1cb3435572075ce6541b91acaaa::$prefixDirsPsr4;

        }, null, ClassLoader::class);
    }
}
