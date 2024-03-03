<?php

namespace Littled\PageContent\Serialized;


use Littled\Database\MySQLOperations;

class DBFieldGroup
{
    use MySQLOperations, SerializedFieldOperations {
        extractPreparedStmtArgs as public;
        fill as traitFill;
        hydrateFromRecordsetRow as public;
        setRecordsetPrefix as traitSetRecordsetPrefix;
    }

    /**
     * @inheritDoc
     * @return $this
     */
    public function fill($src): DBFieldGroup
    {
        $this->traitFill($src);
        return $this;
    }

    /**
     * @inheritDoc
     * @return $this
     */
    public function setRecordsetPrefix($prefix): DBFieldGroup
    {
        $this->traitSetRecordsetPrefix($prefix);
        return $this;
    }
}