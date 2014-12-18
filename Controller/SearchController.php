<?php
/**
 * phlexible
 *
 * @copyright 2007-2013 brainbits GmbH (http://www.brainbits.net)
 * @license   proprietary
 */

namespace Phlexible\Bundle\FrontendSearchBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Search controller
 *
 * @author Stephan Wentz <sw@brainbits.net>
 * @Route("/{_locale}/_search")
 */
class SearchController extends Controller
{
    /**
     * @param Request $request
     *
     * @return Response
     * @Route("/query", name="frontendsearch_query")
     */
    public function queryAction(Request $request)
    {
        $queryString = trim($request->get('q', ''));
        $siterootId = $request->get('siterootId');

        if (strlen($queryString) == 0) {
            return new Response('');
        }

        if (!mb_check_encoding($queryString, 'UTF-8')) {
            return new Response('');
        }

        $elementSearch = $this->get('phlexible_frontend_search.element_search');

        $result = $elementSearch->query($queryString, $request->getLocale(), $siterootId);

        $suggestions = array();
        if (!$result['totalHits']) {
            $suggestions = $elementSearch->suggest($queryString, $request->getLocale(), $siterootId);
        }

        $template = '::search/results.html.twig';

        return $this->render($template, array('result' => $result, 'suggestions' => $suggestions));
    }

    /**
     * @param Request $request
     *
     * @return JsonResponse
     * @Route("/query_json", name="frontendsearch_query_json")
     */
    public function queryJsonAction(Request $request)
    {
        $queryString = strtolower(trim($request->get('q')));
        $siterootId = $request->get('siterootId');

        if (strlen($queryString) == 0) {
            return new Response('');
        }

        if (!mb_check_encoding($queryString, 'UTF-8')) {
            return new Response('');
        }

        $elementSearch = $this->get('phlexible_frontend_search.element_search');

        $result = $elementSearch->query($queryString, $request->getLocale(), $siterootId);

        $suggestions = array();
        if (!$result['totalHits']) {
            $suggestions = $elementSearch->suggest($queryString, $request->getLocale(), $siterootId);
        }

        $template = '::search/results.html.twig';

        return new JsonResponse(
            array(
                'result'      => $result,
                'suggestions' => $suggestions,
                'view'        => $this->renderView($template, array('result' => $result, 'suggestions' => $suggestions))
            )
        );
    }

    /**
     * @param Request $request
     *
     * @return JsonResponse
     * @Route("/suggest", name="frontendsearch_suggest")
     */
    public function suggestAction(Request $request)
    {
        /*
{
  "suggest": {
    "didYouMean": {
      "text": "schmrz",
      "phrase": {
        "field": "did_you_mean"
      }
    }
  },
  "query": {
    "multi_match": {
      "query": "schmrz",
      "fields": [
        "content",
        "title"
      ]
    }
  }
}
         */
        $siterootId = $request->get('siterootId');
        $queryString = strtolower(trim($request->get('q')));

        $elementSearch = $this->get('phlexible_frontend_search.element_search');

        $suggestions = $elementSearch->suggest($queryString, $request->getLocale(), $siterootId);

        return new JsonResponse($suggestions);
    }

    /**
     * @param Request $request
     *
     * @return JsonResponse
     * @Route("/complete", name="frontendsearch_complete")
     */
    public function completeAction(Request $request)
    {
        /*
{
  "aggs": {
    "autocomplete": {
      "terms": {
        "field": "autocomplete",
        "order": {
          "_count": "desc"
        },
        "include": {
          "pattern": "lor.*"
        }
      }
    }
  },
  "query": {
    "prefix": {
      "autocomplete": {
        "value": "lor"
      }
    }
  }
}
         */

        $siterootId = $request->get('siterootId');
        $queryString = strtolower(trim($request->get('q')));

        $elementSearch = $this->get('phlexible_frontend_search.element_search');

        $autocompletes = $elementSearch->autocomplete($queryString, $request->getLocale(), $siterootId);

        return new JsonResponse($autocompletes);
    }
}
