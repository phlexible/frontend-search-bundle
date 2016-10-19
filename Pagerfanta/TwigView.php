<?php

/*
 * This file is part of the phlexible elastica package.
 *
 * (c) Stephan Wentz <sw@brainbits.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Phlexible\Bundle\FrontendSearchBundle\Pagerfanta;

use Pagerfanta\PagerfantaInterface;
use Pagerfanta\View\ViewInterface;

/**
 * @author Pablo Díez <pablodip@gmail.com>
 */
class TwigView implements ViewInterface
{
    /**
     * @var \Twig_Environment
     */
    private $twig;

    /**
     * @var string
     */
    private $template;

    /**
     * @var PagerfantaInterface
     */
    private $pagerfanta;

    /**
     * @var int
     */
    private $proximity;

    /**
     * @var array
     */
    private $parameters = array();

    /**
     * @var array
     */
    private $options = array();

    /**
     * @var int
     */
    private $currentPage;

    /**
     * @var int
     */
    private $maxPerPage;

    /**
     * @var int
     */
    private $nbPages;

    /**
     * @var int
     */
    private $startPage;

    /**
     * @var int
     */
    private $endPage;

    /**
     * @param \Twig_Environment $twig
     * @param string            $template
     */
    public function __construct(\Twig_Environment $twig, $template)
    {
        $this->twig = $twig;
        $this->template = $template;
    }

    /**
     * {@inheritdoc}
     */
    public function render(PagerfantaInterface $pagerfanta, $routeGenerator, array $options = array())
    {
        $this->pagerfanta = $pagerfanta;
        $this->currentPage = $pagerfanta->getCurrentPage();
        $this->maxPerPage = $pagerfanta->getMaxPerPage();
        $this->nbPages = $pagerfanta->getNbPages();

        $this->proximity = isset($options['proximity']) ? (int) $options['proximity'] : $this->getDefaultProximity();
        $this->parameters = isset($options['parameters']) ? $options['parameters'] : array();
        $this->options = $options;

        $this->calculateStartAndEndPage();

        return $this->twig->render($this->template, array(
            'view' => $this
        ));
    }

    /**
     * @return array
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * @param string $name
     * @param mixed  $default
     *
     * @return mixed
     */
    public function getOption($name, $default = null)
    {
        if (isset($this->options[$name])) {
            return $this->options[$name];
        }

        return $default;
    }

    /**
     * @return array
     */
    public function getParameters()
    {
        return $this->parameters;

    }

    /**
     * Returns whether there is prvious page or not.
     *
     * @return Boolean
     */
    public function hasPreviousPage()
    {
        return $this->pagerfanta->hasPreviousPage();
    }

    /**
     * Returns the previous page.
     *
     * @return integer
     */
    public function getPreviousPage()
    {
        return $this->pagerfanta->getPreviousPage();
    }

    /**
     * Returns whether there is next page or not.
     *
     * @return Boolean
     */
    public function hasNextPage()
    {
        return $this->pagerfanta->hasNextPage();
    }

    /**
     * Returns the next page.
     *
     * @return integer
     */
    public function getNextPage()
    {
        return $this->pagerfanta->getNextPage();
    }

    /**
     * Returns the current page.
     *
     * @return integer
     */
    public function getCurrentPage()
    {
        return $this->currentPage;
    }

    /**
     * Returns the maximum items per page.
     *
     * @return integer
     */
    public function getMaxPerPage()
    {
        return $this->maxPerPage;
    }

    /**
     * Returns the number of pages.
     *
     * @return integer
     */
    public function getNbPages()
    {
        return $this->nbPages;
    }

    /**
     * Returns the number of results.
     *
     * @return integer
     */
    public function getNbResults()
    {
        return $this->pagerfanta->getNbResults();
    }

    /**
     * Returns the start page
     *
     * @return integer
     */
    public function getStartPage()
    {
        return $this->startPage;
    }

    /**
     * Returns the end page
     *
     * @return integer
     */
    public function getEndPage()
    {
        return $this->endPage;
    }

    /**
     * Returns the page with negative offset from the last page
     *
     * @param int $page
     *
     * @return integer
     */
    public function toLast($page)
    {
        return $this->pagerfanta->getNbPages() - ($page - 1);
    }

    /**
     * @return int
     */
    private function getDefaultProximity()
    {
        return 2;
    }

    private function calculateStartAndEndPage()
    {
        $startPage = $this->currentPage - $this->proximity;
        $endPage = $this->currentPage + $this->proximity;

        if ($this->startPageUnderflow($startPage)) {
            $endPage = $this->calculateEndPageForStartPageUnderflow($startPage, $endPage);
            $startPage = 1;
        }
        if ($this->endPageOverflow($endPage)) {
            $startPage = $this->calculateStartPageForEndPageOverflow($startPage, $endPage);
            $endPage = $this->nbPages;
        }

        $this->startPage = $startPage;
        $this->endPage = $endPage;
    }

    private function startPageUnderflow($startPage)
    {
        return $startPage < 1;
    }

    private function endPageOverflow($endPage)
    {
        return $endPage > $this->nbPages;
    }

    private function calculateEndPageForStartPageUnderflow($startPage, $endPage)
    {
        return min($endPage + (1 - $startPage), $this->nbPages);
    }

    private function calculateStartPageForEndPageOverflow($startPage, $endPage)
    {
        return max($startPage - ($endPage - $this->nbPages), 1);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'twig';
    }
}
