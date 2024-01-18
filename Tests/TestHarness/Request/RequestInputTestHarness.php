<?php

namespace LittledTests\TestHarness\Request;


use Littled\Request\RequestInput;

class RequestInputTestHarness extends RequestInput
{
    /**
     * @inheritDoc
     */
    public function collectRequestData(?array $src = null)
    {
        /* abstract method stub to allow this class to be instantiated */
    }

    /**
     * @inheritDoc
     */
    public function render(string $label = '', string $css_class = '')
    {
        /* abstract method stub to allow this class to be instantiated */
    }

    /**
     * @inheritDoc
     */
    public function renderInput(?string $label = null)
    {
        /* abstract method stub to allow this class to be instantiated */
    }
}