<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit992c206b9c85bdf55ec222775c5f980f
{
    public static $prefixLengthsPsr4 = array (
        'C' => 
        array (
            'CloseForShabbat\\' => 16,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'CloseForShabbat\\' => 
        array (
            0 => __DIR__ . '/../..' . '/inc',
        ),
    );

    public static $classMap = array (
        'CloseForShabbat\\Admin' => __DIR__ . '/../..' . '/inc/Admin.php',
        'CloseForShabbat\\Options' => __DIR__ . '/../..' . '/inc/Options.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit992c206b9c85bdf55ec222775c5f980f::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit992c206b9c85bdf55ec222775c5f980f::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticInit992c206b9c85bdf55ec222775c5f980f::$classMap;

        }, null, ClassLoader::class);
    }
}
