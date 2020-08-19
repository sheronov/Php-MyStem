<?php

namespace Sheronov\PhpMyStem\Utils;

use Composer\Composer;
use Composer\IO\IOInterface;
use Composer\Installer\InstallationManager;
use Composer\DependencyResolver\Operation\InstallOperation;
use Composer\Repository\InstalledRepositoryInterface;
use Composer\Repository\WritableRepositoryInterface;
use Composer\Script\Event;
use Composer\Package\Package;
use RuntimeException;

class MyStemInstaller
{
    /**
     * @var Composer $composer
     */
    public static $composer;

    /**
     * @var IOInterface $io
     */
    public static $io;

    /**
     * @var InstallationManager $installer
     */
    public static $installer;

    /**
     * @var WritableRepositoryInterface|InstalledRepositoryInterface $localRepo
     */
    public static $localRepo;

    /**
     * @var array $config
     */
    public static $config;

    /**
     * @var Package[] platform-specific packages to install
     */
    public static $toInstall = array();


    public static function install(Event $event): void
    {
        if (!self::init($event)) {
            return;
        }

        $notInstalled = 0;
        if (!empty(self::$toInstall)) {
            self::$io->write('<info>Installing platform-specific dependencies</info>');
            foreach (self::$toInstall as $package) {
                if (!self::$installer->isPackageInstalled(self::$localRepo, $package)) {
                    self::$installer->install(self::$localRepo, new InstallOperation($package));
                    self::updateBinary($package);
                } else {
                    $notInstalled++;
                }
            }
        }
        if (empty(self::$toInstall) || $notInstalled > 0) {
            self::$io->write('Nothing to install or update in platform-specific dependencies');
        }
    }

    public static function update(Event $event): void
    {
        //@TODO: update changed packages
        self::install($event);
    }


    protected static function init(Event $event): bool
    {
        self::$composer = $event->getComposer();
        self::$io = $event->getIO();
        self::$installer = $event->getComposer()->getInstallationManager();
        self::$localRepo = $event->getComposer()->getRepositoryManager()->getLocalRepository();

        $fileName = __DIR__ . '/composer-platform-specific.json';
        if (!file_exists($fileName)) {
            self::$io->write("<error>File $fileName not exists.</error>");
            return false;
        }
        $content = file_get_contents($fileName);
        if (!$content) {
            self::$io->write("<error>Can't read $fileName file.</error>");
            return false;
        }
        self::$config = json_decode($content, true);

        if (!isset(self::$config['extra']['platform-specific-packages'])) {
            return false;
        }

        //@TODO: refactor it all to use composer.lock file, to track updated platform-specific packages
        self::$toInstall = array();

        $unresolved = array();
        foreach (self::$config['extra']['platform-specific-packages'] as $name => $variants) {
            $package = self::createPlatformSpecificPackage($name, $variants);
            if ($package) {
                self::$toInstall[] = $package;
            } else {
                $unresolved[] = $name;
            }
        }

        if (!empty($unresolved)) {
            self::$io->write(
                '<error>Your requirements could not be resolved for current OS and/or processor architecture.</error>'
            );
            self::$io->write("\n  Unresolved platform-specific packages:");
            foreach ($unresolved as $name) {
                self::$io->write("    - $name");
            }
        }

        return true;
    }

    protected static function updateBinary(Package $package): void
    {
        $binaries = $package->getBinaries();
        if (isset($binaries[0]) && PHP_OS_FAMILY !== 'Windows') {
            $binDir = rtrim(self::$composer->getConfig()->get('bin-dir'), '/') . '/';
            if (!@chmod($binDir . 'mystem', 0555)) {
                throw new RuntimeException("Can't chmod binary file '{$binDir}mystem'");
            }
        }
    }

    /**
     * @param string $packageName
     * @param array $variants
     * @return null|Package
     */
    protected static function createPlatformSpecificPackage($packageName, $variants): ?Package
    {
        foreach ($variants as $variant) {
            if (!empty($variant['os']) && $variant['os'] !== PHP_OS_FAMILY) {
                continue;
            }

            reset($variant);
            $name = key($variant);
            $version = $variant[$name];

            return self::createPackage($name, $version, $packageName);
        }

        return null;
    }

    /**
     * @param string $name
     * @param string $version
     * @param string $newName
     * @return null|Package
     */
    protected static function createPackage($name, $version, $newName): ?Package
    {
        if (!isset(self::$config['repositories'])) {
            return null;
        }
        $package = null;
        foreach (self::$config['repositories'] as $cursor) {
            if (isset($cursor['package']['name'], $cursor['package']['version'])
                && $cursor['package']['name'] === $name
                && ($version === '*' || $cursor['package']['version'] === $version)
            ) {
                $package = $cursor['package'];
                break;
            }
        }
        if (!$package) {
            return null;
        }
        $new = self::bindPackageValues($newName, $package);
        self::$localRepo->addPackage($new);
        return $new;
    }

    /**
     * @param string $newName
     * @param array $package
     * @return Package
     */
    protected static function bindPackageValues($newName, array $package): Package
    {
        $new = new Package($newName, $package['version'], $package['version']);
        $new->setType('dist');
        if (isset($package['bin'])) {
            $new->setBinaries($package['bin']);
        }
        if (isset($package['dist']['type'])) {
            $new->setDistType($package['dist']['type']);
        }
        if (isset($package['dist']['url'])) {
            $new->setDistUrl($package['dist']['url']);
        }
        if (isset($package['excludes'])) {
            $new->setArchiveExcludes($package['excludes']);
        }
        return $new;
    }
}
