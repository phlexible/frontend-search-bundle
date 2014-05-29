<?php
/**
 * phlexible
 *
 * @copyright 2007-2013 brainbits GmbH (http://www.brainbits.net)
 * @license   proprietary
 */

/**
 * Suggest controller
 *
 * @author Marco Fischer <mf@brainbits.net>
 */
class SuggestController extends MWF_Controller_Action
{
    public function suggestAction()
    {
        $language = $this->_getParam('lang');
        $queryString = trim($this->_getParam('q' ,''));

        if (strlen($queryString) == 0)
        {
            return;
        }

        if (!Brainbits_Util_String::checkUtf8Encoding($queryString))
        {
            return;
        }

        $container = $this->getContainer();
        $cache = $container->get('cache');
        $query = $container->get('frontendSuggestSearchQuery');
        $query->parseInput($queryString);
        // TODO: Implement language... somehow...

        $search = $container->get('indexer.search');
        $resultObject = $search->query($query, $language);

        $heads = array();

        $n = 1;
        $output = '';
        foreach ($resultObject as $document)
        {
            if (isset($heads[md5($document->getValue('title'))]))
            {
                continue;
            }

            $heads[md5($document->getValue('title'))] = true;

            $output .= $document->getValue('title');
            //$output .= " |" . $document->getValue('url');
            $output .= PHP_EOL;

            if ($n >= 10)
            {
                break;
            }

            $n++;
        }

        $this->_response->setBody($output);
    }
}
