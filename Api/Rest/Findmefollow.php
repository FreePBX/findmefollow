<?php
namespace FreePBX\modules\Findmefollow\Api\Rest;

use FreePBX\modules\Api\Rest\Base;

class Findmefollow extends Base {
    protected $module = 'findmefollow';

    public function __construct($freepbx, $module) {
        parent::__construct($freepbx, $module);
        $this->freepbx->Modules->loadFunctionsInc($module);
    }


    public function setupRoutes($app) {
        /**
         * @verb GET
         * @returns - a list of users' findmefollow settings
         * @uri /findmefollow/users
         */
        $app->get('/users', function ($request, $response, $args) {
            $findmefollow_allusers = \findmefollow_allusers();

            foreach ($findmefollow_allusers as $user) {
                $users[$user[0]] = $user[1];
                unset($user);
            }

            $users = $users ? $users : false;
            return $response->withJson($users);
        })->add($this->checkAllReadScopeMiddleware());

        /**
         * @verb GET
         * @returns - a list of users' find me follow me settings
         * @uri /findmefollow/users/:id
         */
        $app->get('/users/{id}', function ($request, $response, $args) {

            $users = $this->freepbx->Findmefollow->get($args['id'], 1);

            $users = $users ? $users : false;
            return $response->withJson($users);
        })->add($this->checkAllReadScopeMiddleware());

        /**
         * @verb PUT
         * @uri /findmefollow/users/:id
         */
        $app->put('/users/{id}', function ($request, $response, $args) {
            $params = $request->getParsedBody();

            $this->freepbx->Findmefollow->del($args['id']);
            return $response->withJson($this->freepbx->Findmefollow->add(
                $args['id'],
                $params)
            );

        })->add($this->checkAllWriteScopeMiddleware());
    }
}
