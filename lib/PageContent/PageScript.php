<?php

namespace Littled\PageContent;

use Littled\App\AppBase;
use Littled\App\LittledGlobals;
use Littled\Exception\ConfigurationUndefinedException;

class PageScript
{
    public string $script;
    public string $type;
    protected static string $asset_path = 'scripts/';
    protected static string $dev_host = 'http://localhost:5173';
    protected static ?array $manifest = null;

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
        $path = static::$asset_path . $this->script;
        if (AppBase::getAppEnv() === 'development') {
            return static::$dev_host . "/$path";
        }

        if (static::$manifest === null) {
            static::$manifest = file_exists(static::getViteManifestPath())
                ? json_decode(file_get_contents(static::getViteManifestPath()), true)
                : [];
        }
        return '/dist/' . (static::$manifest[$path]['file'] ?? $path);
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