<?php
/**
* @author SignpostMarv
*/
declare(strict_types=1);

namespace SignpostMarv\DaftRouter\Tests;

use SignpostMarv\CS\ConfigUsedWithStaticAnalysis as Base;

class ConfigUsedWithStaticAnalysis extends Base
{
    protected static function RuntimeResolveRules() : array
    {
        $rules = parent::RuntimeResolveRules();

        $rules['phpdoc_no_alias_tag'] = [
            'type' => 'var',
            'link' => 'see',
        ];

        return $rules;
    }
}
