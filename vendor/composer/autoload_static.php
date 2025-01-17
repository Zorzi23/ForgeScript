<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit47157ca44ab01ca25cd78e7c3dc8c761
{
    public static $prefixLengthsPsr4 = array (
        'G' => 
        array (
            'GrammiCore\\' => 11,
        ),
        'F' => 
        array (
            'ForgeScript\\' => 12,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'GrammiCore\\' => 
        array (
            0 => __DIR__ . '/..' . '/zorzi23/grammi_core/src',
        ),
        'ForgeScript\\' => 
        array (
            0 => __DIR__ . '/../..' . '/src',
        ),
    );

    public static $classMap = array (
        'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit47157ca44ab01ca25cd78e7c3dc8c761::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit47157ca44ab01ca25cd78e7c3dc8c761::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticInit47157ca44ab01ca25cd78e7c3dc8c761::$classMap;

        }, null, ClassLoader::class);
    }
}
