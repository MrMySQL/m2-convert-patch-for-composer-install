#!/usr/bin/env php
<?php
class Converter 
{
    const COMMAND_HELP = 'help';

    const MODULE = 'Module';
    const LIBRARY = 'Library';

    protected $nonComposerPath = [
        self::MODULE    => 'app/code/Magento',
        self::LIBRARY   => 'lib/internal/Magento'
    ];
    protected $composerPath    = [
        self::MODULE    => 'vendor/magento/module-',
        self::LIBRARY   => 'vendor/magento/'
    ];

    public function __construct($params = array())
    {
        if (!isset($params[1])) {
            $params[1] = self::COMMAND_HELP;
        }

        $filename = $params[1];

        if ($filename == self::COMMAND_HELP) {
            echo <<<HELP_TEXT
Usage: php -f converter-for-composer.php [file ...|help] [> new-file]
    converter-for-composer.php [file ...|help] [> new-file]

    file        path to PATCH file which contains pathes like app/code/Magento,
                that is in case when Magento 2 was installed without help of composer
    help        this help

HELP_TEXT;
            exit(0);
        }

        if (!file_exists($filename)) {
            printf("Error! File %s does not exist.\n", $filename);
            exit(1);
        }

        $content = file_get_contents($filename);
        echo $this->replaceContent($content);
        exit(0);
    }

    public function camelCaseStringCallback($value)
    {
        return trim(preg_replace_callback('/((?:^|[A-Z])[a-z]+)/',
            array($this, 'splitCamelCaseByDashes'), $value[1]), '-') . '/';
    }

    public function camelCaseStringCallbackModule($value)
    {
        return $this->composerPath[self::MODULE] . $this->camelCaseStringCallback($value);
    }

    public function camelCaseStringCallbackLibrary($value)
    {
        return $this->composerPath[self::LIBRARY] . $this->camelCaseStringCallback($value);
    }


    public function splitCamelCaseByDashes($value)
    {
        return '-' . strtolower($value[0]);
    }

    protected function replaceContent(&$fileContent)
    {
        foreach ($this->nonComposerPath as $type => $path) {
            $fileContent = preg_replace_callback('/' . addcslashes($path, '/') . '\/([A-z0-9\-]+)?\//',
                array($this, 'camelCaseStringCallback' . $type), $fileContent);
        }

        return $fileContent;
    }
}

new Converter($argv);
