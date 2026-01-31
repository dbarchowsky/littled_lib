<?php

namespace Littled\PageContent\Serialized;

trait InputOperations
{
    /**
     * Prepends $prefix to the key of each RequestInput property of the object.
     * @param string $prefix
     * @return SerializedContentUtils
     */
    public function applyInputKeyPrefix(string $prefix): static
    {
        $ip = $this->getInputPropertiesList(false);
        $assigned = [];
        foreach($ip as $property) {
            if (!in_array($this->$property->key, $assigned)) {
                $this->$property->setKey($prefix . $this->$property->key);
                $assigned[] = $this->$property->key;
            }
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
     * @param array $exclude_keys
     * @return SerializedContentUtils
     */
    public function applyLabelPrefix(string $prefix, array $exclude_keys=[]): static
    {
        $ip = array_unique([...$this->getKeyPropertiesList(), ...$this->getInputPropertiesList(false)]);
        foreach($ip as $property) {
            if (in_array($this->$property->key, $exclude_keys)) {
                continue;
            }
            $this->$property->label = ucfirst(strtolower(trim($prefix) . ' ' . $this->$property->label));
            $exclude_keys[] = $this->$property->key;
        }
        $cp = $this->getContentPropertiesList();
        foreach($cp as $property) {
            $this->$property->applyLabelPrefix($prefix, $exclude_keys);
        }
        return $this;
    }

    /**
     * Prepends $prefix to the label of each RequestInput property of the object.
     * @param string $prefix
     * @param array $exclude_keys
     * @return SerializedContentUtils
     */
    public function stripLabelPrefix(string $prefix, array $exclude_keys = []): static
    {
        $ip = array_unique([...$this->getKeyPropertiesList(), ...$this->getInputPropertiesList(false)]);
        foreach($ip as $property) {
            if (in_array($this->$property->key, $exclude_keys)) {
                continue;
            }
            $label = $this->$property->label;
            if (str_starts_with($label, $prefix)) {
                $label = substr($label, strlen($prefix));
            }
            $this->$property->label = ucfirst(strtolower(ltrim($label)));
            $exclude_keys[] = $this->$property->key;
        }
        $cp = $this->getContentPropertiesList();
        foreach($cp as $property) {
            $this->$property->stripLabelPrefix($prefix, $exclude_keys);
        }
        return $this;
    }
}