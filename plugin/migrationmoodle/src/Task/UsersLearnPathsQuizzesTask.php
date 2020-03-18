<?php
/* For licensing terms, see /license.txt */

namespace Chamilo\PluginBundle\MigrationMoodle\Task;

use Chamilo\PluginBundle\MigrationMoodle\Extractor\LoadedUsersFilterExtractor;
use Chamilo\PluginBundle\MigrationMoodle\Loader\UserLearnPathQuizLoader;
use Chamilo\PluginBundle\MigrationMoodle\Transformer\BaseTransformer;
use Chamilo\PluginBundle\MigrationMoodle\Transformer\Property\LearnPathItemViewQuizStatus;
use Chamilo\PluginBundle\MigrationMoodle\Transformer\Property\LoadedCourseModuleQuizByQuizLookup;
use Chamilo\PluginBundle\MigrationMoodle\Transformer\Property\LoadedUserLookup;
use Chamilo\PluginBundle\MigrationMoodle\Transformer\Property\LoadedUserSessionLookup;
use Chamilo\PluginBundle\MigrationMoodle\Transformer\Property\Subtract;

/**
 * Class UsersLearnPathsQuizzesTask.
 *
 * @package Chamilo\PluginBundle\MigrationMoodle\Task
 */
class UsersLearnPathsQuizzesTask extends BaseTask
{
    /**
     * @inheritDoc
     */
    public function getExtractConfiguration()
    {
        return [
            'class' => LoadedUsersFilterExtractor::class,
            'query' => "SELECT id, quiz, userid, timestart, timefinish, state, sumgrades
                FROM mdl_quiz_attempts
                WHERE preview = 0",
        ];
    }

    /**
     * @inheritDoc
     */
    public function getTransformConfiguration()
    {
        return [
            'class' => BaseTransformer::class,
            'map' => [
                'item_id' => [
                    'class' => LoadedCourseModuleQuizByQuizLookup::class,
                    'properties' => ['quiz'],
                ],
                'user_id' => [
                    'class' => LoadedUserLookup::class,
                    'properties' => ['userid'],
                ],
                'session_id' => [
                    'class' => LoadedUserSessionLookup::class,
                    'properties' => ['userid'],
                ],
                'start_time' => 'timestart',
                'total_time' => [
                    'class' => Subtract::class,
                    'properties' => ['timefinish', 'timestart'],
                ],
                'status' => [
                    'class' => LearnPathItemViewQuizStatus::class,
                    'properties' => ['quiz', 'state', 'sumgrades'],
                ],
                'score' => 'sumgrades',
            ],
        ];
    }

    /**
     * @inheritDoc
     */
    public function getLoadConfiguration()
    {
        return [
            'class' => UserLearnPathQuizLoader::class,
        ];
    }
}
