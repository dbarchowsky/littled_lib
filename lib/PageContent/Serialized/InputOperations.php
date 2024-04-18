<?php

namespace Littled\PageContent\Serialized;

trait InputOperations
{
    /**
     * Prepends $prefix to the key of each RequestInput property of the object.
     * @param string $prefix
     * @return void
     */
    public function applyInputKeyPrefix(string $prefix): SerializedContentUtils
    {
        $ip = $this->getInputPropertiesList(false);
        foreach($ip as $property) {
            $this->$property->setKey($prefix . $this->$property->key);
        }
        $cp = $this->getContentPropertiesList();
        foreach($cp as $property) {
            $this->$property->applyInputKeyPrefix($prefix);
        }
        return $this;
    }

    /**
     * Prepends $prefix to the label of each RequestInput property of the object.
     * @param string $prefix
     * @return void
     */
    public function applyLabelPrefix(string $prefix): SerializedContentUtils
    {
        $ip = $this->getInputPropertiesList(false);
        foreach($ip as $property) {
            $this->$property->label = trim($prefix) . ' ' . $this->$property->label;
        }
        $cp = $this->getContentPropertiesList();
        foreach($cp as $property) {
            $this->$property->applyLabelPrefix($prefix);
        }
        return $this;
    }
}