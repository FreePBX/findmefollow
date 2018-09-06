<?php
namespace FreePBX\modules\Findmefollow\Api\Rest;
use FreePBX\modules\Api\Rest\Base;
class Findmefollow extends Base {
	protected $module = 'findmefollow';
	public function setupRoutes($app) {
		/**
		* @verb GET
		* @returns - a list of users' findmefollow settings
		* @uri /findmefollow/users
		*/
		$app->get('/users', function ($request, $response, $args) {
			\FreePBX::Modules()->loadFunctionsInc('findmefollow');
			$findmefollow_allusers = findmefollow_allusers();

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
			\FreePBX::Modules()->loadFunctionsInc('findmefollow');
			$users = findmefollow_get($args['id'], 1);
			$users = $users ? $users : false;
			return $response->withJson($users);
		})->add($this->checkAllReadScopeMiddleware());

		/**
		* @verb PUT
		* @uri /findmefollow/users/:id
		*/
		$app->put('/users/{id}', function ($request, $response, $args) {
			\FreePBX::Modules()->loadFunctionsInc('findmefollow');
			$params = $request->getParsedBody();

			findmefollow_del($args['id']);
			return $response->withJson(findmefollow_add(
				$args['id'],
				$params['strategy'],
				$params['grptime'],
				$params['grplist'],
				$params['postdest'],
				$params['grppre'],
				$params['annmsg_id'],
				$params['dring'],
				$params['needsconf'],
				$params['remotealert_id'],
				$params['toolate_id'],
				$params['ringing'],
				$params['pre_ring'],
				$params['ddial'],
				$params['changecid'],
				$params['fixedcid'])
			);

		})->add($this->checkAllWriteScopeMiddleware());
	}
}
