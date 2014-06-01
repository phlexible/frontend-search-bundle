<?php
/**
 * phlexible
 *
 * @copyright 2007-2013 brainbits GmbH (http://www.brainbits.net)
 * @license   proprietary
 */

namespace Phlexible\FrontendSearchComponent\Controller;

use Phlexible\CoreComponent\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Suggest controller
 *
 * @author Marco Fischer <mf@brainbits.net>
 */
class SuggestController extends Controller
{
    /**
     * @param Request $request
     *
     * @return Response
     */
    public function suggestAction(Request $request)
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
