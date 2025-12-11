<?php

namespace Littled\Account;


use Littled\PageContent\Serialized\SerializedContent;
use Littled\Request\BooleanCheckbox;
use Littled\Request\StringTextField;

class UserAccess extends SerializedContent
{
    public StringTextField $name;
    public BooleanCheckbox $enabled;

    protected static string $table_name = 'user_access';

    public const ID_KEY = 'accessId';
    /** @var int Disabled value. */
    const NO_AUTHENTICATION = 1;
    /** @var int Basic credentials token value. */
    const BASIC_AUTHENTICATION = 2;
    /** @var int Admin credentials token value. */
    const ADMIN_AUTHENTICATION = 3;

    public function __construct()
    {
        parent::__construct();
        $this->id
            ->setLabel('Access ID')
            ->setKey(self::ID_KEY);
        $this->name = (new StringTextField())
            ->setLabel('Name')
            ->setKey('accessName')
            ->setAsRequired()
            ->setSizeLimit(50);
        $this->enabled = (new BooleanCheckbox())
            ->setLabel('Enabled')
            ->setKey('accessEnabled');
    }

    /**
     * @inheritDoc
     */
    protected function hasRecordData(): bool
    {
        return $this->name->hasData();
    }

    /**
     * @inheritDoc
     */
    public function getContentLabel(): string
    {
        return 'user access';
    }
}