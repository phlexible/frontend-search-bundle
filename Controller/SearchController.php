<?php
/**
 * phlexible
 *
 * @copyright 2007-2013 brainbits GmbH (http://www.brainbits.net)
 * @license   proprietary
 */

namespace Phlexible\FrontendSearchBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Search controller
 *
 * @author Marco Fischer <mf@brainbits.net>
 */
class SearchController extends Controller
{
    /**
     * @param Request $request
     *
     * @return Response
     * @Route("/_search", name="frontendsearch_suggest")
     */
    public function searchAction(Request $request)
    {
        $language = $request->get('lang');
        $queryString = trim($request->get('q', ''));

        if (strlen($queryString) == 0) {
            return;
        }

        if (!Brainbits_Util_String::checkUtf8Encoding($queryString)) {
            return;
        }

        $cache = $this->get('cache');
        $query = $this->get('frontendSuggestSearchQuery');
        $query->parseInput($queryString);
        // TODO: Implement language... somehow...

        $search = $this->get('indexer.search');
        $resultObject = $search->query($query, $language);

        $heads = array();

        $n = 1;
        $output = '';
        foreach ($resultObject as $document) {
            if (isset($heads[md5($document->getValue('title'))])) {
                continue;
            }

            $heads[md5($document->getValue('title'))] = true;

            $output .= $document->getValue('title');
            //$output .= " |" . $document->getValue('url');
            $output .= PHP_EOL;

            if ($n >= 10) {
                break;
            }

            $n++;
        }

        return new Response($output);
    }
}
