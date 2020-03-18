<?php
/* For licensing terms, see /license.txt */

namespace Chamilo\PluginBundle\MigrationMoodle\Transformer\Property;

use Chamilo\PluginBundle\MigrationMoodle\Interfaces\TransformPropertyInterface;

/**
 * Class Explode.
 *
 * @package Chamilo\PluginBundle\MigrationMoodle\Transformer\Property
 */
class Explode implements TransformPropertyInterface
{
    const SEPARATOR = '@mm@';

    /**
     * @param array $data
     *
     * @return mixed
     */
    public function transform(array $data)
    {
        $value = current($data);

        return explode(self::SEPARATOR, $value);
    }
}
