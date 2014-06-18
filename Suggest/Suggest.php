<?php

/**
 * Phlexible
 *
 * PHP Version 5
 *
 * @category    Makeweb
 * @package     Makeweb_FrontendSolrSugges
 * @copyright   2009 brainbits GmbH (http://www.brainbits.net)
 * @version     SVN: $Id: $
 */

/**
 * Word Suggest (fuzzy / partial search)
 *
 * @category    Makeweb
 * @package     Makeweb_FrontendSolrSugges
 * @author      Phillip Look <plook@brainbits.net>
 * @copyright   2009 brainbits GmbH (http://www.brainbits.net)
 */
class Makeweb_FrontendSolrSuggest_Suggest
{
    /**
     * @var MWF_Core_IndexerRepositorySolr_Driver
     */
    protected $_solr;

    /**
     * @var Makeweb_FrontendSolrSuggest_Configuration
     */
    protected $_configuration;

    /**
     * @var Zend_Cache_Core
     */
    protected $_cache;

    /**
     * Constructor
     *
     * @param Doctrine\Common\Cache\Cache               $cache
     * @param MWF_Core_IndexerRepositorySolr_Driver     $solr
     * @param Makeweb_FrontendSolrSuggest_Configuration $configuration
     */
    public function __construct(Doctrine\Common\Cache\Cache               $cache,
                                MWF_Core_IndexerRepositorySolr_Driver     $solr,
                                Makeweb_FrontendSolrSuggest_Configuration $configuration)
    {
        $this->_cache         = $cache;
        $this->_solr          = $solr;
        $this->_configuration = $configuration;
    }

    /**
     * Build solr query string.
     *
     * @param string $word
     * @param string $siterootId
     * @param string $language
     * @param string $context
     *
     * @return string
     */
    protected function _buildQuery($word, $siterootId, $language, $context, $filter)
    {
        $query = new Brainbits_Solr_Query();

        $query
            // siteroot
            ->andSubquery()
            ->orTerm($siterootId, 'attr_is_siteroot')
            ->orTerm($siterootId, 'attr_im_siteroots')
            ->endSubquery()

            // language
            ->andSubquery()
            ->orTerm($language, 'attr_is_language')
            ->orTerm($language, 'attr_im_languages')
            ->endSubquery();

        if ($this->_configuration->skipRestricted())
        {
            $query->andTerm('0', 'attr_is_restricted');
        }

        foreach ($filter as $filterKey => $filterValue)
        {
            $query->andTerm($filterValue, $filterKey);
        }

        // context
        if ($this->_configuration->useContext())
        {
            $contextSubQuery = $query->andSubquery()->orTerm($context, 'attr_im_context');

            if ('global' !== $context)
            {
                $contextSubQuery->orTerm('global', 'attr_im_context');
            }

            $contextSubQuery->endSubquery();
        }

        // terms
        $termsSubquery = $query->andSubquery();

        if ($this->_configuration->isPartialEnabled())
        {
            $boost = $this->_configuration->getPartialBoost();
            $termsSubquery->orTerm($word.'*^' . $boost, 'attr_im_ac');
        }

        $fuzzyMinLength = $this->_configuration->getFuzzyMinLength();
        $wordLength     = mb_strlen($word);

        if ($this->_configuration->isFuzzyEnabled() && $wordLength >= $fuzzyMinLength)
        {
            $boost      = $this->_configuration->getFuzzyBoost();
            $similarity = $this->_configuration->getFuzzySimilarity();
            $termsSubquery->orTerm($word."~$similarity^$boost", 'attr_im_ac');
        }

        $termsSubquery->endSubquery();

        return (string) $query;
    }

    /**
     * Do a solr autocompletion query.
     *
     * @param string $query
     *
     * @return array Matching documents
     */
    protected function _queryMatchingDocs($query)
    {
        $params = array(
            'q'     => $query,
            'fl'    => $this->_getSolrFields(),
            'hl'    => 'off',
            'start' => 0,
            'rows'  => $this->_configuration->getSubQuerySize(),
        );

        $cacheId = $this->_getCacheId($params);

        $result = $this->_cache->fetch($cacheId);

        if (!$result || MWF_Env::isDev())
        {
            // execute query
            $result = $this->_solr->select($params);

            $result = $result['response']['docs'];

            $this->_filterExtranetResults($result);

            $this->_cache->save($cacheId, $result, 300);
        }

        return $result;
    }

    /**
     * Get 10 best suggestions.
     *
     * @return array (array(<suggestion>, <similarity>, <number of prefixes>), ...)
     */
    public function query($query, $siterootId, $language, $context, array $filter)
    {
        // get autocompletions per word
        $autocompletes = $this->_getAutoCompletes($query, $siterootId, $language, $context, $filter);

        $results = array();
        foreach ($autocompletes as $replacements)
        {
            if (!count($results))
            {
                $results = $replacements;
                continue;
            }

            $newResults = array();
            foreach ($replacements as $replacement)
            {
                foreach ($results as $result)
                {
                    $newResults[] = array(
                        $result[0] . ' ' . $replacement[0],
                        $result[1] + $replacement[1],
                        $result[2] + $replacement[2],
                    );
                }
            }

            $results = $newResults;
        }

        // remove query from possible results
        foreach ($results as $key => $result)
        {
            if ($result[0] == $query)
            {
                unset($results[$key]);
            }
        }

        // return n best suggestions
        $resultSize = $this->_configuration->getResultSize();
        usort($results, array($this, '_compareCompletions'));
        return array_slice($results, 0, $resultSize);
    }

    /**
     * Get array with 10 best autocompletions for each word
     *
     * @return array (array(<suggestion>, <similarity>, <number of prefixes>), ...)
     */
    protected function _getAutoCompletes($query, $siterootId, $language, $context, array $filter)
    {
        $result = array();
        $levenshteinMaxSimilarity = $this->_configuration->getLevenshteinMaxSimilarity();
        $levenshteinCostInsert    = $this->_configuration->getLevenshteinCostInsert();
        $levenshteinCostReplace   = $this->_configuration->getLevenshteinCostReplace();
        $levenshteinCostDelete    = $this->_configuration->getLevenshteinCostDelete();

        foreach (array_unique(explode(' ', $query)) as $word)
        {
            $filtered = $this->_filter($word);

            if (mb_strlen($filtered))
            {
                // build query
                $solrQuery = $this->_buildQuery($filtered, $siterootId, $language, $context, $filter);

                // maximum possible similarity
                $maxSim = mb_strlen($filtered) * $levenshteinMaxSimilarity;

                // rank results
                $completions = array();
                $found       = array();
                foreach ($this->_queryMatchingDocs($solrQuery) as $doc)
                {
                    foreach ($doc['attr_im_ac'] as $completion)
                    {
                        if (isset($found[$completion]))
                        {
                            continue;
                        }

                        // mark completion as found
                        $found[$completion] = true;

                        $sim = levenshtein(
                            $filtered,
                            $this->_filter($completion),
                            $levenshteinCostInsert,
                            $levenshteinCostReplace,
                            $levenshteinCostDelete
                        );

                        $prefix = (0 === strpos($completion, $filtered));

                        if ($prefix || $sim < $maxSim)
                        {
                            $completions[] = array(
                                $this->_applyCase($word, $completion),
                                $sim,
                                $prefix
                            );
                        }
                    }
                }

                // store 10 best autocompletions per word
                usort($completions, array($this, '_compareCompletions'));
                $result[$filtered] = array_slice($completions, 0, 10);;
            }
        }

        return $result;
    }

    /**
     * Replace umlauts and remove special characters.
     *
     * @param string $term
     *
     * @return string
     */
    protected function _filter($term)
    {
        // remove special characters
        $term = preg_replace('/[^\w\d]/u', '', $term);

        // compare same case
        $term = mb_strtolower($term);

        return $term;
    }

    /**
     * Apply the case from
     *
     * @param string $term
     *
     * @return string
     */
    protected function _applyCase($queryTerm, $suggestion)
    {
        $result = '';

        $queryTermArray  = preg_split('//u', $queryTerm, -1, PREG_SPLIT_NO_EMPTY);
        $suggestionArray = preg_split('//u', $suggestion, -1, PREG_SPLIT_NO_EMPTY);

        $queryTermLength  = count($queryTermArray);
        $suggestionLength = count($suggestionArray);

        $hasOnlyUppercase = true;
        for ($i = 0; $i < $suggestionLength; ++$i)
        {
            if ($i < $queryTermLength)
            {
                // use case from query term
                if ($queryTermArray[$i] == mb_strtoupper($queryTermArray[$i]))
                {
                    $result .= mb_strtoupper($suggestionArray[$i]);
                }
                else
                {
                    $hasOnlyUppercase = false;
                    $result .= mb_strtolower($suggestionArray[$i]);
                }
            }
            else
            {
                // copy next characters from suggestion
                $result .= $hasOnlyUppercase
                    ? mb_strtoupper($suggestionArray[$i])
                    : mb_strtolower($suggestionArray[$i]);
            }
        }

        return $result;
    }

    /**
     * Compare quality of two autocompletions.
     *
     * @param array $a
     * @param array $b
     *
     * @return int
     */
    public function _compareCompletions(array $a, array $b)
    {
        // prefixed are listed first
        $cmp = $b[2] - $a[2];

        if (0 === $cmp)
        {
            // otherwise sort by similarity
            $cmp = $a[1] - $b[1];
        }

        return $cmp;
    }

    /**
     * Create cache id-
     *
     * @param array $queryParams
     *
     * @return string
     */
    protected function _getCacheId(array $queryParams)
    {
        if (!$this->_configuration->skipRestricted())
        {
            $contact = MWF_Registry::getContainer()->contactsManager;

            $queryParams['contact_id'] = $contact->{'contact_id'};
        }

        $cacheId = md5(serialize($queryParams));

        return $cacheId;
    }

    protected function _filterExtranetResults(array &$result)
    {
        if ($this->_configuration->skipRestricted())
        {
            return;
        }

        $maxResultDocs = $this->_configuration->getRealSubQuerySize();
        $resultSize    = 0;

        foreach ($result as $index => $document)
        {
            if ($resultSize >= $maxResultDocs || !$this->_hasRight($document))
            {
                unset($result[$index]);
                continue;
            }

            // remove fields required for rights check
            unset(
                $result[$index]['attr_is_restricted'],
                $result[$index]['attr_is_tid'],
                $result[$index]['attr_im_tids'],
                $result[$index]['attr_is_folder_id']
            );

            ++$resultSize;
        }
    }

    protected function _getSolrFields()
    {
        if ($this->_configuration->skipRestricted())
        {
            return 'attr_im_ac';
        }

        return 'attr_im_ac,attr_is_restricted,attr_is_tid,attr_im_tids,attr_is_folder_id';
    }

    protected function _hasRight(array $document)
    {
        if (!$this->_hasElementRight($document))
        {
            return false;
        }

        if (!$this->_hasMediaRight($document))
        {
            return false;
        }

        return true;
    }

    protected function _hasElementRight(array $document)
    {
        $container           = MWF_Registry::getContainer();
        $isRestricted        = !isset($document['attr_is_restricted']) || !$document['attr_is_restricted'];
        $extranetIsInstalled = isset($container->extranetRightsManager);

        if (!$isRestricted || !$extranetIsInstalled)
        {
            return true;
        }

        $rightsManager = $container->extranetRightsManager;

        if (isset($document['attr_is_tid']) &&
            !$rightsManager->hasViewelementRight($document['attr_is_tid']))
        {
            return false;
        }

        if (isset($document['attr_im_tids']) &&
            count($document['attr_im_tids']) &&
            !count($rightsManager->intersectViewelementRight($document['attr_im_tids'])))
        {
            return false;
        }

        return true;
    }

    protected function _hasMediaRight(array $document)
    {
        $container                = MWF_Registry::getContainer();
        $extranetMediaIsInstalled = isset($container->extranetMediaViewRights);

        if (!$extranetMediaIsInstalled)
        {
            return true;
        }

        if (isset($document['attr_is_folder_id']))
        {
            /* @var $viewRights Makeweb_ExtranetMedia_ViewRights */
            $viewRights = $container->extranetMediaViewRights;

            if (!$viewRights->hasViewRight($document['attr_is_folder_id']))
            {
                return false;
            }
        }

        return true;
    }
}
