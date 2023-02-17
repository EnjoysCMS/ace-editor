<?php

namespace EnjoysCMS\ContentEditor\AceEditor;

use Composer\Script\Event;

class Helpers
{
    public static function assetsInstall(Event $event)
    {
        passthru(sprintf('cd %s && yarn install', realpath(__DIR__.'/..')));
    }
}