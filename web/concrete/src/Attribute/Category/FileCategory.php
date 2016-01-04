<?php

namespace Concrete\Core\Attribute\Category;

use Concrete\Core\Attribute\Category\SearchIndexer\StandardSearchIndexerInterface;
use Concrete\Core\Entity\Attribute\Key\Key;
use Concrete\Core\Entity\Attribute\Type;
use Concrete\Core\Entity\File\Attribute;
use Symfony\Component\HttpFoundation\Request;

class FileCategory extends AbstractCategory implements StandardSearchIndexerInterface
{

    public function getIndexedSearchTable()
    {
        return 'FileSearchIndexAttributes';
    }

    public function getIndexedSearchPrimaryKeyValue($mixed)
    {
        return $mixed->getFileID();
    }


    public function getSearchIndexFieldDefinition()
    {
        return array(
            'columns' => array(
                array(
                    'name' => 'fID',
                    'type' => 'integer',
                    'options' => array('unsigned' => true, 'default' => 0, 'notnull' => true)
                )
            ),
            'primary' => array('fID')
        );
    }

    public function getAttributeRepository()
    {
        return $this->entityManager->getRepository('\Concrete\Core\Entity\File\Attribute');
    }

    /**
     * Takes an attribute key as created by the subroutine and assigns it to the page category.
     * @param Key $key
     */
    protected function assignToCategory(Key $key)
    {
        $this->entityManager->persist($key);
        $this->entityManager->flush();
        $attribute = new Attribute();
        $attribute->setAttributeKey($key);
        $this->entityManager->persist($attribute);
        $this->entityManager->flush();
        return $attribute;
    }

    public function addFromRequest(Type $type, Request $request)
    {
        $key = parent::addFromRequest($type, $request);
        return $this->assignToCategory($key);
    }

    public function import(Type $type, \SimpleXMLElement $element)
    {
        $key = parent::import($type, $element);
        return $this->assignToCategory($key);
    }

    public function getAttributeValues($file)
    {
        $r = $this->entityManager->getRepository('\Concrete\Core\Entity\File\AttributeValue');
        $values = $r->findBy(array(
            'fID' => $file->getFileID(),
            'fvID' => $file->getVersionID()
        ));
        return $values;
    }



}