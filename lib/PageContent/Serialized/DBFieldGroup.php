<?php

namespace Littled\PageContent\Serialized;


use Littled\Database\MySQLOperations;

class DBFieldGroup
{
    use MySQLOperations, PropertyEvaluations, SerializedFieldOperations, HydrateFieldOperations {
        extractPreparedStmtArgs as traitExtractPreparedStmtArgs;
        fill as traitFill;
        setRecordsetPrefix as traitSetRecordsetPrefix;
    }

    /**
     * @inheritDoc
     * Overrides parent to exclude "id" and "index" properties from fields that would be used to build SQL statements.
     */
    public function extractPreparedStmtArgs(array &$used_keys = []): array
    {
        $excluded = ['id', 'index'];
        $fields = $this->traitExtractPreparedStmtArgs($used_keys);
        for($i = count($fields)-1; $i >= 0; $i--) {
            if (in_array($fields[$i]->key,  $excluded)) {
                unset($fields[$i]);
            }
        }
        for($i = count($used_keys)-1; $i >= 0; $i--) {
            if (in_array($used_keys[$i],  $excluded)) {
                unset($used_keys[$i]);
            }
        }
        // re-index the arrays
        $used_keys = array_values($used_keys);
        return array_values($fields);
    }

    /**
     * @inheritDoc
     * @return $this
     */
    public function fill(object|array $src): DBFieldGroup
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