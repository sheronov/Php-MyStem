<?php


namespace Sheronov\PhpMyStem\Utils;


class System
{
    public const FAMILY_WINDOWS = 'Windows';
    public const FAMILY_LINUX = 'Linux';

    public static function runMyStem(string $input, array $arguments = [])
    {
        $output = null;
        if(!$path = self::myStemPath()) {
            return null;
        }

        $arguments[] = '--format=json';
        $arguments[] = '-l';

        $descriptorSpec = [
            ['pipe','r'],
            ['pipe', 'w'],
            // ['file','/tmp/mystem-errors.txt','a']
        ];

        // $cwd = '/tmp';
        $cwd = null;

        $process = proc_open($path.' '.implode(' ',$arguments), $descriptorSpec, $pipes, $cwd, []);

        if(is_resource($process)) {
            // $pipes now looks like this:
            // 0 => writeable handle connected to child stdin
            // 1 => readable handle connected to child stdout
            // Any error output will be appended to /tmp/error-output.txt

            fwrite($pipes[0],  $input);
            fclose($pipes[0]);

            $output = stream_get_contents($pipes[1]);
            fclose($pipes[1]);

            proc_close($process);
        }

        return $output;
    }

    protected static function myStemPath(): ?string
    {
        if(self::isWindows()) {
            return dirname(__FILE__, 3).DIRECTORY_SEPARATOR.'bin'.DIRECTORY_SEPARATOR.'mystem.exe';
        }
        if(self::isLinux()) {
            return dirname(__FILE__, 3).DIRECTORY_SEPARATOR.'bin'.DIRECTORY_SEPARATOR.'mystem';
        }

        return null;
    }

    protected static function isWindows(): bool
    {
        return PHP_OS_FAMILY === self::FAMILY_WINDOWS;
    }

    protected static function isLinux(): bool
    {
        return PHP_OS_FAMILY === self::FAMILY_LINUX;
    }
}
