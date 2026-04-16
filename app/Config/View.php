<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

class View extends BaseConfig
{
    /**
     * --------------------------------------------------------------------------
     * Default View Extension
     * --------------------------------------------------------------------------
     *
     * Sets the default extension used in view files.
     *
     * @var string
     */
    public string $defaultExtension = 'php';

    /**
     * --------------------------------------------------------------------------
     * Title Section
     * --------------------------------------------------------------------------
     *
     * Options for the <title> tag.
     *
     * @var array<string, array<string, string|false>>
     */
    public array $title = [
        'prefix' => '',
        'separator' => ' | ',
        'suffix' => '',
    ];

    /**
     * --------------------------------------------------------------------------
     * View Folder Paths
     * --------------------------------------------------------------------------
     *
     * Path to view directories. You may set this to a single directory string
     * or an array of directories.
     *
     * @var string[]|string
     */
    public $paths = [
        APPPATH . 'Views',
    ];

    /**
     * --------------------------------------------------------------------------
     * Class Aliases
     * --------------------------------------------------------------------------
     *
     * Maps class names and namespaces to aliases to make them more convenient
     * to use as helpers in views.
     *
     * @var array<string, string>
     */
    public array $aliases = [];

    /**
     * --------------------------------------------------------------------------
     * View Filters
     * --------------------------------------------------------------------------
     *
     * Allows you to filter the data before it's passed to the view.
     *
     * @var string[]
     */
    public array $filters = [];

    /**
     * --------------------------------------------------------------------------
     * Save Data
     * --------------------------------------------------------------------------
     *
     * Whether to save the data between views.
     *
     * @var bool
     */
    public bool $saveData = true;

    /**
     * --------------------------------------------------------------------------
     * View Decorators
     * --------------------------------------------------------------------------
     *
     * Classes that can modify the output of a view before it's rendered.
     *
     * @var array<string>
     */
    public array $decorators = [];

    /**
     * --------------------------------------------------------------------------
     * View Plugins
     * --------------------------------------------------------------------------
     *
     * Classes that provide view helper methods.
     *
     * @var array<string>
     */
    public array $plugins = [];

    /**
     * --------------------------------------------------------------------------
     * Application Overrides Folder
     * --------------------------------------------------------------------------
     *
     * Path to the application overrides folder. This folder can be used to override
     * system views and assets.
     *
     * @var string
     */
    public string $appOverridesFolder = APPPATH . 'Views' . DIRECTORY_SEPARATOR . 'overrides';
}
