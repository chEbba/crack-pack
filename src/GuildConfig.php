<?php
/**
 * @LICENSE_TEXT
 */

namespace Che\CrackPack;

use Composer\Config;

/**
 * Class GuildConfig
 *
 * @author Kirill chEbba Chebunin <iam@chebba.org>
 */
class GuildConfig
{
    const REPOSITORY_FILE = 'packages.json';

    private static $DEFAULT_CONFIG = [
        'install-dir' => '/var/www',
        'cache-dir' => '/var/cache/build-guild',
        'stability' => 'stable'
    ];

    private $repository;
    private $installDir;
    private $cacheDir;
    private $stability;
    private $composer;

    public function __construct(array $config)
    {
        $required = ['repository'];
        foreach ($required as $property) {
            if (!isset($config[$property])) {
                throw new \InvalidArgumentException(sprintf('Option "%s" is required', $property));
            }
        }
        $config = array_merge(self::$DEFAULT_CONFIG, $config);

        $this->repository = $config['repository'];
        $this->installDir = $config['install-dir'];
        $this->cacheDir = $config['cache-dir'];
        $this->stability = $config['stability'];

        $this->composer = $this->createComposerConfig();
    }

    /**
     * @return mixed
     */
    public function getInstallDir()
    {
        return $this->installDir;
    }

    /**
     * @return mixed
     */
    public function getRepository()
    {
        return $this->repository;
    }

    public function getLocalRepository()
    {
        return $this->getInstallDir() . '/' . self::REPOSITORY_FILE;
    }

    /**
     * @return mixed
     */
    public function getCacheDir()
    {
        return $this->cacheDir;
    }

    /**
     * @return string
     */
    public function getStability()
    {
        return $this->stability;
    }

    /**
     * @return Config
     */
    public function getComposer()
    {
        return $this->composer;
    }

    private function createComposerConfig()
    {
        $config = new Config();
        $config->merge(['config' => [
            'cache-dir' => $this->cacheDir,
            'vendor-dir' => $this->installDir
        ]]);

        return $config;
    }
}
