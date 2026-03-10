<?php

namespace Littled\PageContent;

use Littled\App\AppBase;
use Littled\App\LittledGlobals;
use Littled\Exception\ConfigurationUndefinedException;

class PageScript
{
    public string $script;
    public string $type;

    public function getScript(): string
    {
        return $this->script ?? '';
    }

    public function getType(): string
    {
        return $this->type ?? '';
    }

    /**
     * Resolves a versioned asset path from the Vite manifest.
     * @return string
     * @throws ConfigurationUndefinedException
     */
    public function getViteAsset(): string
    {
        $isDev = AppBase::getAppEnv() === 'development';
        $assetPath = 'app/js/';
        $devHost = 'http://localhost:5173';

        $fullPath = $assetPath . $this->script;
        if ($isDev) {
            return "$devHost/$fullPath";
        }

        if (!file_exists(static::getViteManifestPath())) {
            return "/dist/$fullPath";
        }
        $manifest = json_decode(file_get_contents(static::getViteManifestPath()), true);

        if (isset($manifest[$fullPath])) {
            return '/dist/' . $manifest[$fullPath]['file'];
        }
        return "/dist/$fullPath";
    }

    /**
     * Path to the Vite manifest file.
     * @return string
     * @throws ConfigurationUndefinedException
     */
    protected static function getViteManifestPath(): string
    {
        return LittledGlobals::getAppBaseDir() . 'dist/.vite/manifest.json';
    }

    public function setScript(string $script): static
    {
        $this->script = $script;
        return $this;
    }

    public function setType(string $type): static
    {
        $this->type = $type;
        return $this;
    }
}