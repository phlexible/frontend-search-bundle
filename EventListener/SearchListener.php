<?php
/**
 * phlexible
 *
 * @copyright 2007-2013 brainbits GmbH (http://www.brainbits.net)
 * @license   proprietary
 */

namespace Phlexible\Bundle\FrontendSearchBundle\EventListener;

/**
 * Search listener
 *
 * @author Marco Fischer <mf@brainbits.net>
 * @author Phillip Look <pl@brainbits.net>
 */
class SearchListener
{
    public function onSearch(Makeweb_Renderers_Event_Elementtype $event, $params)
    {
        /* @var $container MWF_Container_ContainerInterface */
        $container = $params['container'];

        $renderer = $event->getRenderer();
        $request  = $renderer->getRequest();
        $view     = $renderer->getView();

        $view->assign('query', '');
        $view->assign('hasQuery', $request->hasParam('query'));

        $queryString = trim(strip_tags($request->getParam('query', '')));

        // Parameter Query enthällt einen String länger 0 Zeichen?
        if (!mb_strlen($queryString) || !Brainbits_Util_String::checkUtf8Encoding($queryString))
        {
            return;
        }

        try
        {
            $current = $request->getParam('page', 1);
            $limit   = $request->getParam('limit', 10);

            $search = $container->get('frontendFullTextSearchSearch')->setRequest($request);


            $paginator = $search->search($queryString, $current, $limit);

            $view->assign('paginator', $paginator);
            $view->assign('query', htmlspecialchars($queryString));
        }
        catch (Exception $e)
        {
            MWF_Log::exception($e);
        }
    }
}
