<?php

namespace Littled\PageContent\Serialized;

use Littled\Exception\ContentValidationException;
use Littled\Validation\ContentValidationTrait;


class SerializedContentValidation extends SerializedContentUtils
{
    use ContentValidationTrait {
        validateInput as protected traitValidateInput;
    }

    public function __construct()
    {
        $this->bootstrapValidation();
    }



    /**
     * Validates only the primary and foreign key properties of the object.
     * @param array $exclude_properties
     * @return void
     */
    protected function validateKeyProperties(array $exclude_properties = []): void
    {
        $properties = $this->getKeyPropertiesList();
        foreach ($properties as $p) {
            if (in_array($p, $exclude_properties)) {
                continue;
            }
            try {
                $this->{$p}->validate();
            }
            catch (ContentValidationException $ex) {
                $this->addValidationError($ex->getMessage());
                $exclude_properties[] = $p;
            }
        }
    }

    /**
     * Validates the internal property values of the object for data that is not valid.
     * Updates the $validation_errors property of the object with messages describing the invalid values.
     * @param array $exclude_properties Names of class properties to exclude from validation.
     * @param bool $clear_existing Optional flag controlling whether to clear existing validation errors before validation. Defaults to true.
     * @return void
     * @throws ContentValidationException
     */
    public function validateInput(array $exclude_properties = [], bool $clear_existing = true): void
    {
        if (true === $this->bypass_validation) {
            $this->validateKeyProperties($exclude_properties);
            if ($this->hasValidationErrors()) {
                throw new ContentValidationException($this->validation_message);
            }
            return;
        }
        $this->traitValidateInput($exclude_properties, $clear_existing);
    }
}