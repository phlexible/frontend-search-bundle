<?php

/**
 * Phlexible
 *
 * PHP Version 5
 *
 * @category    Makeweb
 * @package     Makeweb_FrontendSolrSuggest
 * @copyright   2010 brainbits GmbH (http://www.brainbits.net)
 */

/**
 * Suggest Configuration
 *
 * @category    Makeweb
 * @package     Makeweb_FrontendSolrSuggest
 * @author      Phillip Look <pl@brainbits.net>
 * @copyright   2010 brainbits GmbH (http://www.brainbits.net)
 */
class Makeweb_FrontendSolrSuggest_Configuration
{
    const USE_CONTEXT              = 'use_context';
    const FIELD_CONFIG             = 'query.field_config';
    const MIN_TOKEN_LENGTH         = 'query.min_token_length';
    const SKIP_RESTRICTED          = 'query.skip_restricted';
    const COST_INSERT              = 'levenshtein.cost_insert';
    const COST_REPLACE             = 'levenshtein.cost_replace';
    const COST_DELETE              = 'levenshtein.cost_delete';
    const MAX_SIM                  = 'levenshtein.max';
    const PARTIAL_ENABLE           = 'solr.partial_enable';
    const PARTIAL_BOOST            = 'solr.partial_boost';
    const FUZZY_ENABLE             = 'solr.fuzzy_enable';
    const FUZZY_BOOST              = 'solr.fuzzy_boost';
    const FUZZY_SIM                = 'solr.fuzzy_sim';
    const FUZZY_MIN_LENGTH         = 'solr.fuzzy_min_length';
    const SUBQUERY_SIZE            = 'solr.subquery_size';
    const SUBQUERY_SIZE_RESTRICTED = 'solr.subquery_size_restricted';
    const RESULT_SIZE              = 'result.suggestions';

    /**
     * @var array
     */
    protected $_fieldConfig;

    /**
     * Constructor
     *
     * @param array $config
     */
    public function __construct(array $config)
    {
        $this->config = $config;
    }

    /**
     * Check if a document type is enabled.
     *
     * @return boolean
     */
    public function getFieldConfig()
    {
        if (null === $this->_fieldConfig)
        {
            $this->_fieldConfig = array();

            $fieldConfig = $this->config[self::FIELD_CONFIG];
            $docConfigs = explode(';', $fieldConfig);

            foreach ($docConfigs as $docConfig)
            {
                $separatorPos = strpos($docConfig, ':');
                if (!$separatorPos)
                {
                    continue;
                }

                $docType = substr($docConfig, 0, $separatorPos);
                $fields  = explode(',', substr($docConfig, $separatorPos + 1));

                foreach ($fields as $field)
                {
                    $this->_fieldConfig[$docType][] = trim($field);
                }
            }
        }

        return $this->_fieldConfig;
    }

    /**
     * Check if a document type is enabled.
     *
     * @return boolean
     */
    public function isDocumentTypeEnabled($documentType)
    {
        $fieldConfig = $this->getFieldConfig();

        return isset($fieldConfig[$documentType]) && count($fieldConfig[$documentType]);
    }

    /**
     * Check if a document type is enabled.
     *
     * @return boolean
     */
    public function isFieldEnabled($documentType, $field)
    {
        $fieldConfig = $this->getFieldConfig();

        return isset($fieldConfig[$documentType])
            && in_array($field, $fieldConfig[$documentType]);
    }

    /**
     * Get minimum length for suggest tokens.
     *
     * @return integer
     */
    public function getMinTokenLength()
    {
        return (integer) $this->config[self::MIN_TOKEN_LENGTH];
    }

    /**
     * Check if context is used.
     *
     * @return boolean
     */
    public function useContext()
    {
        return (boolean) $this->config[self::USE_CONTEXT];
    }

    /**
     * Get cost of levenshtein insert.
     *
     * @return integer
     */
    public function getLevenshteinCostInsert()
    {
        return (integer) $this->config[self::COST_INSERT];
    }

    /**
     * Get cost of levenshtein replace.
     *
     * @return integer
     */
    public function getLevenshteinCostReplace()
    {
        return (integer) $this->config[self::COST_REPLACE];
    }

    /**
     * Get cost of levenshtein insert.
     *
     * @return integer
     */
    public function getLevenshteinCostDelete()
    {
        return (integer) $this->config[self::COST_DELETE];
    }

    /**
     * Get cost of levenshtein max.
     *
     * @return integer
     */
    public function getLevenshteinMaxSimilarity()
    {
        return (integer) $this->config[self::MAX_SIM];
    }

    /**
     * Are partial suggestions enabled.
     *
     * @return boolean
     */
    public function isPartialEnabled()
    {
        return (boolean) $this->config[self::PARTIAL_ENABLE];
    }

    /**
     * Get boost factor of partial matches.
     *
     * @return string (float)
     */
    public function getPartialBoost()
    {
        return $this->config[self::PARTIAL_BOOST];
    }

    /**
     * Are partial suggestions enabled.
     *
     * @return boolean
     */
    public function isFuzzyEnabled()
    {
        return (boolean) $this->config[self::FUZZY_ENABLE];
    }

    /**
     * Get boost factor of partial matches.
     *
     * @return string (float)
     */
    public function getFuzzyBoost()
    {
        return $this->config[self::FUZZY_BOOST];
    }

    /**
     * Get similarity for fuzzy search.
     *
     * @return string (float)
     */
    public function getFuzzySimilarity()
    {
        return $this->config[self::FUZZY_SIM];
    }

    /**
     * Get min length of input words for enabling fuzzy search.
     *
     * @return integer
     */
    public function getFuzzyMinLength()
    {
        return (integer) $this->config[self::FUZZY_MIN_LENGTH];
    }

    /**
     * Get result size for solrs per word queries.
     *
     * @return integer
     */
    public function getSubQuerySize()
    {
        return $this->skipRestricted()
            ? (integer) $this->config[self::SUBQUERY_SIZE]
            : (integer) $this->config[self::SUBQUERY_SIZE_RESTRICTED];
    }

    /**
     * Get result size for solrs per word queries.
     *
     * @return integer
     */
    public function getRealSubQuerySize()
    {
        return (integer) $this->config[self::SUBQUERY_SIZE];
    }

    /**
     * Get number of suggestions result size.
     *
     * @return integer
     */
    public function getResultSize()
    {
        return (integer) $this->config[self::RESULT_SIZE];
    }

    /**
     * Get number of suggestions result size.
     *
     * @return integer
     */
    public function skipRestricted()
    {
        $skipRestricted = (boolean) $this->config[self::SKIP_RESTRICTED];

        if (!$skipRestricted)
        {
            $contact        = MWF_Registry::getContainer()->contactsManager;
            $skipRestricted = !$contact->isAuthenticated();
        }

        return $skipRestricted;
    }

}
