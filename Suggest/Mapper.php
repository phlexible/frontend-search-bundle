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
 * Field mapper
 *
 * @category    Makeweb
 * @package     Makeweb_FrontendSolrSuggest
 * @author      Phillip Look <pl@brainbits.net>
 * @copyright   2010 brainbits GmbH (http://www.brainbits.net)
 */
class Makeweb_FrontendSolrSuggest_Mapper
{
    /**
     * @var Makeweb_FrontendSolrSuggest_Configuration
     */
    protected $_configuration;

    /**
     * Constructor
     *
     * @param Makeweb_FrontendSolrSuggest_Configuration configuration
     */
    public function __construct(Makeweb_FrontendSolrSuggest_Configuration $configuration)
    {
        $this->_configuration = $configuration;
    }

    /**
     * Map autocompletion values.
     */
    public function applyAutocompletion(MWF_Core_Indexer_Document_Interface $document)
    {
        // check if document type supports autocompletion
        $documentType = $document->getDocumentType();
        if (!$this->_configuration->isDocumentTypeEnabled($documentType))
        {
            return;
        }

        // extract relevant text (fields) from document
        $text = $this->_extractRelevantText($document);

        // cleanup text (suggest tokens)
        $document->ac = $this->_normalizeText($text);
    }

    /**
     * Extract relevant text (fields) from document.
     *
     * @param MWF_Core_Indexer_Document_Interface $document
     *
     * @return string
     */
    protected function _extractRelevantText(MWF_Core_Indexer_Document_Interface $document)
    {
        $documentType   = $document->getDocumentType();
        $documentFields = $document->getFields();

        $words = '';
        foreach (array_keys($documentFields) as $field)
        {
            if ($this->_configuration->isFieldEnabled($documentType, $field))
            {
                // concatenate words
                $words .= implode(' ', (array) $document->$field) . ' ';
            }
        }

        return $words;
    }

    /**
     * Normaize text:
     * - strip tags
     * - remove non printable characters
     * - convert to lowercase
     * - remove short words (min_token_length)
     * - remove numeric
     *
     * @param string $words
     *
     * @param array of unique words
     */
    protected function _normalizeText($words)
    {
        $minTokenLength = $this->_configuration->getMinTokenLength();

        $words = strip_tags($words);
        $words = preg_replace('/[^\pL\d]/u', ' ', $words);
        $words = mb_strtolower($words);

        $ac = array();
        foreach (explode(' ', $words) as $token)
        {
            // filter all words with length smaller 4 chars
            if (mb_strlen($token) >= $minTokenLength && !is_numeric($token))
            {
                $ac[$token] = $token;
            }
        }

        return array_values($ac);
    }
}
