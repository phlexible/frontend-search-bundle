<?php
/**
 * phlexible
 *
 * @copyright 2007-2013 brainbits GmbH (http://www.brainbits.net)
 * @license   proprietary
 */

namespace Phlexible\Bundle\FrontendSearchBundle\EventListener;

/**
 * Document listener
 *
 * @author Phillip Look <pl@brainbits.net>
 */
class DocumentListener
{
    /**
     * Add autocompletion field to index.
     *
     * @param MWF_Core_Indexer_Event_CreateDocument $event
     * @param array $params
     */
    public function onCreateDocument(MWF_Core_Indexer_Event_CreateDocument $event,
                                            array $params = array())
    {
        /* @var $container MWF_Container_ContainerInterface */
        $container     = $params['container'];
        $configuration = $container->get('frontendsearchsuggest.configuration');

        $document = $event->getDocument();

        // check if document type should be used for autocomplete
        if (!$configuration->isDocumentTypeEnabled($document->getDocumentType()))
        {
            return;
        }

        $document->setFields(
            array(
                'ac' => array(MWF_Core_Indexer_Document_Interface::CONFIG_MULTIVALUE),
            )
        );
    }

    /**
     * Add autocompletion for element document.
     *
     * @param Makeweb_IndexerElements_Event_MapDocument $event
     * @param array $params
     */
    public function onIndexerElementsMapDocument(
        Makeweb_IndexerElements_Event_MapDocument $event,
        array $params = array())
    {
        /* @var $container MWF_Container_ContainerInterface */
        $container = $params['container'];
        $mapper    = $container->get('frontendsearchsuggest.mapper');

        $document = $event->getDocument();
        $mapper->applyAutocompletion($document);
    }

    /**
     * Add autocompletion for media document.
     *
     * @param Makeweb_IndexerElementsMedia_Event_MapDocument $event
     * @param array $params
     */
    public function onIndexerElementsMediaMapDocument(
        Makeweb_IndexerElementsMedia_Event_MapDocument $event,
        array $params = array())
    {
        /* @var $container MWF_Container_ContainerInterface */
        $container = $params['container'];
        $mapper    = $container->get('frontendsearchsuggest.mapper');

        $document = $event->getMediaDocument();
        $mapper->applyAutocompletion($document);
    }
}
