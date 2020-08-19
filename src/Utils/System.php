<?php


namespace Sheronov\PhpMyStem\Utils;


use BadFunctionCallException;
use Sheronov\PhpMyStem\Exceptions\MyStemException;
use Sheronov\PhpMyStem\Exceptions\MyStemNotFoundException;

class System
{
    protected const FAMILY_WINDOWS = 'Windows';
    protected const FAMILY_LINUX   = 'Linux';
    protected const FAMILY_MACOS   = 'Darwin';

    protected const BIN_PATH    = 'vendor/bin';
    protected const WINDOWS_BIN = 'mystem.exe.bat';
    protected const LINUX_BIN   = 'mystem';
    protected const MACOS_BIN   = 'mystem';

    /**
     * Running MyStem with input as pipe to proc through php proc_open
     *
     * @param  string  $input
     * @param  array  $arguments
     * @param  string|null  $myStemPath
     *
     * @return string
     * @throws MyStemException
     * @throws MyStemNotFoundException
     */
    public static function runMyStem(string $input, array $arguments = [], string $myStemPath = null): string
    {
        if (!isset($myStemPath)) {
            $myStemPath = self::myStemPath();
        }

        if (!empty($arguments)) {
            $myStemPath .= ' '.implode(' ', $arguments);
        }

        $descriptorSpec = [
            ['pipe', 'r'], //0 - stdIn
            ['pipe', 'w'], //1 - stdOut
            ['pipe', 'w'], //2 - stdErr
        ];

        $process = proc_open($myStemPath, $descriptorSpec, $pipes, null, null);

        if (!is_resource($process)) {
            throw new BadFunctionCallException('There is no "proc_open" function in your system');
        }

        fwrite($pipes[0], $input);
        fclose($pipes[0]);

        $output = stream_get_contents($pipes[1]);
        fclose($pipes[1]);

        $stdErr = stream_get_contents($pipes[2]);
        fclose($pipes[2]);

        if (!empty($stdErr)) {
            throw new MyStemException($stdErr);
        }

        proc_close($process);

        return $output;
    }

    /**
     * @return string
     * @throws MyStemNotFoundException
     */
    protected static function myStemPath(): string
    {
        $binPath = self::binPath();
        if (self::isWindows() && file_exists($binPath.self::WINDOWS_BIN)) {
            return $binPath.self::WINDOWS_BIN;
        }
        if (self::isLinux() && file_exists($binPath.self::LINUX_BIN)) {
            return $binPath.self::LINUX_BIN;
        }
        if (self::isMacos() && file_exists($binPath.self::MACOS_BIN)) {
            return $binPath.self::MACOS_BIN;
        }

        throw new MyStemNotFoundException('The bin file myStem does not exist');
    }

    protected static function binPath(): string
    {
        return dirname(__FILE__, 3).DIRECTORY_SEPARATOR.self::BIN_PATH.DIRECTORY_SEPARATOR;
    }

    protected static function isWindows(): bool
    {
        return PHP_OS_FAMILY === self::FAMILY_WINDOWS;
    }

    protected static function isLinux(): bool
    {
        return PHP_OS_FAMILY === self::FAMILY_LINUX;
    }

    protected static function isMacos(): bool
    {
        return PHP_OS_FAMILY === self::FAMILY_MACOS;
    }
}
