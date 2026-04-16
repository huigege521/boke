<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

class Pager extends BaseConfig
{
    /**
     * --------------------------------------------------------------------------
     * Templates
     * --------------------------------------------------------------------------
     *
     * Pagination links are rendered out using views to configure their appearance.
     * This array contains aliases and the view names to use when rendering them.
     *
     * Within each view, the following variables will be available:
     *
     * $pager  Pager instance
     * $items  array of pagination data for the view
     * $page   current page
     * $total  total rows
     * $url    page URL
     *
     * @var array<string, string>
     */
    public array $templates = [
        'default_full'   => 'CodeIgniter\Pager\Views\default_full',
        'default_simple' => 'CodeIgniter\Pager\Views\default_simple',
        'default_head'   => 'CodeIgniter\Pager\Views\default_head',
        'bootstrap_full' => 'pager/bootstrap_full',
    ];

    /**
     * --------------------------------------------------------------------------
     * Items Per Page
     * --------------------------------------------------------------------------
     *
     * The default number of results shown per page.
     *
     * @var int
     */
    public int $perPage = 20;

    /**
     * --------------------------------------------------------------------------
     * Page Name
     * --------------------------------------------------------------------------
     *
     * The name of the page query string segment that is used.
     *
     * @var string
     */
    public string $pageName = 'page';

    /**
     * --------------------------------------------------------------------------
     * URI Segment
     * --------------------------------------------------------------------------
     *
     * The URI segment that contains the page number, if you prefer to use URI
     * segments rather than query string segments.
     *
     * @var int|null
     */
    public ?int $uriSegment = null;

    /**
     * --------------------------------------------------------------------------
     * Service Alias
     * --------------------------------------------------------------------------
     *
     * The alias that will be used to access the service from the container.
     *
     * @var string
     */
    public string $serviceAlias = 'pager';
}