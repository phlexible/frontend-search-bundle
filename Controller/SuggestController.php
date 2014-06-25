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

/**
 * Suggest controller
 *
 * @author Michael Rick <mr@brainbits.net>
 * @author Phillip Look <pl@brainbits.net>
 */
class SuggestController extends Controller
{
    /**
     * @return JsonResponse
     * @Route("/_suggest", name="frontendsearch_suggest")
     */
    public function suggestAction()
    {
        $result = array();

        $filters = array(
            '*' => array('StripTags', 'StringTrim'),
            'term' => array('Utf8Encode'),
        );

        $validators = array(
            'siteroot_id' => array(
                \Zend_Filter_Input::ALLOW_EMPTY => false,
                \Zend_Filter_Input::PRESENCE => \Zend_Filter_Input::PRESENCE_REQUIRED,
                'Uuid',
            ),
            'language' => array(
                \Zend_Filter_Input::ALLOW_EMPTY => false,
                \Zend_Filter_Input::PRESENCE => \Zend_Filter_Input::PRESENCE_REQUIRED,
            ),
            'context' => array(
                \Zend_Filter_Input::ALLOW_EMPTY => true,
                \Zend_Filter_Input::PRESENCE => \Zend_Filter_Input::PRESENCE_REQUIRED,
            ),
            'term' => array(
                \Zend_Filter_Input::ALLOW_EMPTY => false,
                \Zend_Filter_Input::PRESENCE => \Zend_Filter_Input::PRESENCE_REQUIRED,
            ),
            'debug' => array(
                \Zend_Filter_Input::ALLOW_EMPTY => true,
                \Zend_Filter_Input::PRESENCE => \Zend_Filter_Input::PRESENCE_OPTIONAL,
                \Zend_Filter_Input::DEFAULT_VALUE => false,
            ),
            'filter' => array(
                \Zend_Filter_Input::ALLOW_EMPTY => true,
                \Zend_Filter_Input::PRESENCE => \Zend_Filter_Input::PRESENCE_OPTIONAL,
            )
        );

        $fi = new \Zend_Filter_Input($filters, $validators, $this->_getAllParams());
        if (!$fi->isValid()) {
            $e = new \Zend_Filter_Exception('Invalid data.', 0, $fi);
            MWF_Log::exception($e);

            return new JsonResponse(array('msg' => $e->getFilterMessagesAsSimpleArray()));
        }

        $term       = $fi->getUnescaped('term');
        $siterootId = $fi->getUnescaped('siteroot_id');
        $language   = $fi->getUnescaped('language');
        $context    = $fi->getUnescaped('context');
        $debug      = (boolean) $fi->getUnescaped('debug');
        $filter     = (array) $fi->getUnescaped('filter');

        $suggest     = $this->getContainer()->get('frontendsearchsuggest.suggest');
        $suggestions = $suggest->query($term, $siterootId, $language, $context, $filter);

        foreach ($suggestions as $suggestion) {
            $resultRow = array(
                'id'      => $suggestion[0],
                'label'   => $suggestion[0],
            );

            if ($debug) {
                $resultRow['sim']     = $suggestion[1];
                $resultRow['partial'] = $suggestion[2];
            }

            $result[] = $resultRow;
        }

        return new JsonResponse($result);
    }

}