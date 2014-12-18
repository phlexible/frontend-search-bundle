<?php
/**
 * phlexible
 *
 * @copyright 2007-2013 brainbits GmbH (http://www.brainbits.net)
 * @license   proprietary
 */

namespace Phlexible\Bundle\FrontendSearchBundle\Controller;

use Phlexible\Bundle\IndexerBundle\Query\Aggregation\TermsAggregation;
use Phlexible\Bundle\IndexerBundle\Query\Filter\BoolAndFilter;
use Phlexible\Bundle\IndexerBundle\Query\Filter\TermFilter;
use Phlexible\Bundle\IndexerBundle\Query\Query\MultiMatchQuery;
use Phlexible\Bundle\IndexerBundle\Query\Query\PrefixQuery;
use Phlexible\Bundle\IndexerBundle\Query\Query\QueryString;
use Phlexible\Bundle\IndexerBundle\Query\Suggest;
use Phlexible\Bundle\IndexerBundle\Query\Suggest\PhraseSuggest;
use Phlexible\Bundle\IndexerBundle\Query\Suggest\TermSuggest;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Search controller
 *
 * @author Stephan Wentz <sw@brainbits.net>
 * @Route("/_search")
 */
class SearchController extends Controller
{
    /**
     * @param Request $request
     *
     * @return Response
     * @Route("/test", name="frontendsearch_test")
     */
    public function testAction(Request $request)
    {
        return $this->render(
            'PhlexibleFrontendSearchBundle::test.html.twig'
        );
    }

    /**
     * @param Request $request
     *
     * @return Response
     * @Route("/query", name="frontendsearch_query")
     */
    public function queryAction(Request $request)
    {
        $language = $request->get('language');
        $queryString = trim($request->get('q', ''));
        $siterootId = $request->get('siteroot_id');

        if (strlen($queryString) == 0) {
            return new Response('');
        }

        if (!mb_check_encoding($queryString, 'UTF-8')) {
            return new Response('');
        }

        $storage = $this->get('phlexible_indexer.storage.default');

        $filter = new BoolAndFilter();
        $filter->addFilter(new TermFilter(array('language' => $language)));
        $filter->addFilter(new TermFilter(array('siterootId' => $siterootId)));

        $query = $storage->createQuery()
            //->setFilter($filter)
            ->setQuery(new QueryString($queryString));

        $result = $storage->query($query);

        $template = 'PhlexibleFrontendSearchBundle::result.html.twig';

        return $this->render($template, array('result' => $result));
    }

    /**
     * @param Request $request
     *
     * @return JsonResponse
     * @Route("/query_json", name="frontendsearch_query_json")
     */
    public function queryJsonAction(Request $request)
    {
        $language = $request->get('language');
        $queryString = trim($request->get('q', ''));
        $siterootId = $request->get('siteroot_id');

        if (strlen($queryString) == 0) {
            return new Response('');
        }

        if (!mb_check_encoding($queryString, 'UTF-8')) {
            return new Response('');
        }

        $storage = $this->get('phlexible_indexer.storage.default');

        $filter = new BoolAndFilter();
        $filter->addFilter(new TermFilter(array('language' => $language)));
        $filter->addFilter(new TermFilter(array('siterootId' => $siterootId)));

        $query = $storage->createQuery()
            //->setFilter($filter)
            ->setQuery(new QueryString($queryString));

        $result = $storage->query($query);

        $template = 'PhlexibleFrontendSearchBundle::result.html.twig';

        return new JsonResponse(
            array(
                'result' => $result,
                'view' => $this->renderView($template, array('result' => $result))
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
        $language = $request->get('language');
        $siterootId = $request->get('siteroot_id');
        $term = trim($request->get('term'));

        $storage = $this->get('phlexible_indexer.storage.default');

        $suggestion = new PhraseSuggest('didYouMean', 'did_you_mean');
        $suggest = new Suggest($suggestion);
        $suggest->setGlobalText($term);

        $filter = new BoolAndFilter();
        $filter->addFilter(new TermFilter(array('language' => $language)));
        $filter->addFilter(new TermFilter(array('siterootId' => $siterootId)));

        $query = new MultiMatchQuery();
        $query
            ->setQuery($term)
            ->setFields(array('title', 'content'));

        $q = $storage->createQuery()
            ->setQuery($query)
            ->setSuggest($suggest)
            ->setFilter($filter);

        $results = $storage->query($q);

        return new JsonResponse($results);
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
  "size": 0,
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

        $language = $request->get('language');
        $siterootId = $request->get('siterootId');
        $term = trim($request->get('term'));

        $storage = $this->get('phlexible_indexer.storage.default');

        $filter = new BoolAndFilter();
        $filter
            ->addFilter(new TermFilter(array('language' => $language)))
            ->addFilter(new TermFilter(array('siteroot_id' => $siterootId)));

        $aggregation = new TermsAggregation('autocomplete');
        $aggregation
            ->setField('autocomplete')
            ->setOrder('_count', 'desc')
            ->setInclude("$term.*", '');

        $query = $storage->createQuery()
            ->setSize(0)
            ->setQuery(new PrefixQuery(array('autocomplete' => $term)))
            //->setFilter($filter)
            ->addAggregation($aggregation);

        $results = $storage->query($query);

        $autocompletes = array();
        foreach ($results['aggregations']['autocomplete']['buckets'] as $bucket) {
            $autocompletes[] = array(
                'value' => $bucket['key'],
                'count' => $bucket['doc_count']
            );
        }

        return new JsonResponse($autocompletes);
    }
}
