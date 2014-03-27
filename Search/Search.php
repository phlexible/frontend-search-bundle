<?php
/**
 * phlexible
 *
 * @copyright 2007-2013 brainbits GmbH (http://www.brainbits.net)
 * @license   proprietary
 */

namespace Phlexible\FrontendSearchComponent\Search;

/**
 * Search
 *
 * @author Marco Fischer <mf@brainbits.net>
 */
class Search
{
    /**
     * @var integer
     */
    const PAGINATOR_PAGE_RANGE = 5;

    /**
     * @var integer
     */
    const CACHE_LIFETIME = 300;

    /**
     * @var Doctrine\Common\Cache\Cache
     */
    protected $_cache;

    /**
     * @var MWF_Core_Indexer_Search
     */
    protected $_indexerSearch;

    /**
     * @var Makeweb_FrontendFulltextSearch_Query
     */
    protected $_query;

    /**
     * @var Makeweb_Elements_Element_Manager
     */
    protected $_elementManager;

    /**
     * @var Makeweb_Elements_Context_Manager
     */
    protected $_contextManager;

    /**
     * @var Makeweb_Elements_Tree_Manager
     */
    protected $_treeManager;

    /**
     * @var Makeweb_Frontend_Request
     */
    protected $_request;

    /**
     * @var Brainbits_Event_Dispatcher
     */
    protected $_dispatcher;
    
    /**
     * @var array
     */
    protected $_siterootIds;
    

	/**
     * @param Doctrine\Common\Cache\Cache          $cache
     * @param Brainbits_Event_Dispatcher           $dispatcher
     * @param MWF_Core_Indexer_Search              $search
     * @param Makeweb_FrontendFulltextSearch_Query $query
     * @param Makeweb_Elements_Element_Manager     $elementManager
     * @param Makeweb_Elements_Context_Manager     $contextManager
     * @param Makeweb_Elements_Tree_Manager        $treeManager
     */
    public function __construct(Doctrine\Common\Cache\Cache          $cache,
                                Brainbits_Event_Dispatcher           $dispatcher,
                                MWF_Core_Indexer_Search              $search,
                                Makeweb_FrontendFulltextSearch_Query $query,
                                Makeweb_Elements_Element_Manager     $elementManager,
                                Makeweb_Elements_Context_Manager     $contextManager,
                                Makeweb_Elements_Tree_Manager        $treeManager)
    {
        $this->_cache          = $cache;
        $this->_dispatcher     = $dispatcher;
        $this->_query          = $query;
        $this->_indexerSearch  = $search;
        $this->_elementManager = $elementManager;
        $this->_contextManager = $contextManager;
        $this->_treeManager    = $treeManager;
    }

    /**
     * @param Makeweb_Frontend_Request $request
     *
     * @return Makeweb_FrontendFulltextSearch_Search
     */
    public function setRequest(Makeweb_Frontend_Request $request)
    {
        $this->_request     = $request;
        $this->_siterootIds = array($this->_request->getSiteRootId());

        return $this;
    }

    public function setSiteroots(array $siterootIds)
    {
        $this->_siterootIds = $siterootIds;
    }
    
    /**
     * @param string $queryString
     * @param string $currentPage
     * @param string $pageSize
     *
     * @return Zend_Paginator
     */
    public function search($queryString, $currentPage, $pageSize)
    {
        $this->_setFilters();

        $result    = $this->_query($queryString);
        $paginator = $this->_createPaginator($result, $pageSize, $currentPage);

        $this->_filterResult($paginator);

        return $paginator;
    }

    protected function _setFilters()
    {
        $language = $this->_request->getLanguage();

        $filter = array(
            array(
                array('language' => $language),
                array('languages' => $language),
            ),
            array(
                array('siteroot' => $this->_siterootIds),
                array('siteroots' => $this->_siterootIds),
            ),
        );

        if ($this->_contextManager->useContext() && $this->_request->hasContext())
        {
            $context = $this->_request->getContext();
            $country = $context->getCountry();

            $filter['context'] = array_unique(array($country, 'global'));
        }

        $this->_query->setFilters($filter);
    }

    protected function _getCacheId($queryString)
    {
        return __CLASS__ . '_' . md5($queryString . (string) $this->_query);
    }

    protected function _query($queryString)
    {
        $this->_query->parseInput($queryString);

        $beforeEvent = new Makeweb_FrontendFulltextSearch_Event_BeforeSearch(
            $this,
            $this->_query,
            $queryString
        );
        $this->_dispatcher->postNotification($beforeEvent);

        $results = $this->_indexerSearch
            ->query($this->_query)
            ->getResult();

        $event = new Makeweb_FrontendFulltextSearch_Event_Search($this, $results);
        $event->setBeforeNotification($beforeEvent);
        $this->_dispatcher->postNotification($event);

        return $results;
    }

    protected function _createPaginator(array &$result, $pageSize, $currentPage)
    {
        $paginator = Zend_Paginator::factory($result);
        $paginator->setItemCountPerPage($pageSize);
        $paginator->setCurrentPageNumber($currentPage);
        $paginator->setPageRange(self::PAGINATOR_PAGE_RANGE);

        return $paginator;
    }

    protected function _filterResult(Zend_Paginator $paginator)
    {
        $trees = array();
        foreach ($this->_siterootIds as $siterootId)
        {
            $trees[] = $this->_treeManager->getBySiteRootId($siterootId);
        }

        foreach ($paginator as $document)
        {
            $docType = $document->getDocumentType();
            if ($docType !== 'elements' && $docType !== 'media')
            {
                continue;
            }

            /* @var $document MWF_Core_Indexer_Document_Interface */

            $docTid   = (integer) ($document->hasField('tid') ? $document->getValue('tid') : 0);
            $language = $document->hasField('language') ? $document->getValue('language') : null;

            // remove deleted tids from result
            
            $foundInTrees = false;
            
            foreach ($trees as $tree)
            {
                if ($docTid && $tree->getOnlineVersion($docTid, $language))
                {
                    $foundInTrees = true;
                }
            }
            
            if ($docTid && !$foundInTrees)
            {
                $document->setValue('hidden', true, true);
            }

            $docTids = $document->hasField('tids') ? $document->getValue('tids') : null;
            if (is_array($docTids) && count($docTids))
            {
                $fileId = $document->hasField('file_id') ? $document->getValue('file_id') : null;
                $links = array();
                foreach ($docTids as $docTid)
                {
                    $linkInfo = $this->_getElementLinkInfo($docTid, $fileId);

                    if ($linkInfo)
                    {
                        $links[$docTid] = $linkInfo;
                    }
                }

                if (count($links))
                {
                    $document->setValue('links', $links, true);
                }
                else
                {
                    $document->setValue('hidden', true, true);
                }
            }
        }
    }

    protected function _getElementLinkInfo($tid, $fileId)
    {
        $siterootId = $this->_request->getSiteRootId();
        $language   = $this->_request->getLanguage();
        $tree       = $this->_treeManager->getBySiteRootId($siterootId);

        if (!$tree->hasNode($tid))
        {
            return null;
        }

        $node    = $tree->getNode($tid);
        $eid     = (integer) $node->getEid();
        $version = (integer) $node->getOnlineVersion($language);

        if (!$version)
        {
            return null;
        }

        $element        = $this->_elementManager->getByEID($eid);
        $elementVersion = $element->getVersion($version);
        $title          = $elementVersion->getNavigationTitle($language);

        $params = array();
        if ($fileId)
        {
            $params['fileid'] = $fileId;
        }

        $link = Makeweb_Navigations_Link::createFromTid(
            $tid,
            $language,
            $this->_request->isPreviewRequest(),
            false,
            $params
        );

        return array(
            'tid'      => $tid,
            'eid'      => $tid,
            'version'  => $version,
            'language' => $language,
            'title'    => $title,
            'link'     => $link,
        );
    }

    /**
     * @return \Makeweb_Frontend_Request
     */
    public function getRequest()
    {
        return $this->_request;
    }


}
