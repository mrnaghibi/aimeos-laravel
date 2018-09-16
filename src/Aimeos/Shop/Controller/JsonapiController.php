<?php

/**
 * @license MIT, http://opensource.org/licenses/MIT
 * @copyright Aimeos (aimeos.org), 2017
 * @package laravel
 * @subpackage Controller
 */


namespace Aimeos\Shop\Controller;

use App\Exceptions\AcceptException;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response;
use Laravel\Passport\TokenRepository;
use Lcobucci\JWT\Parser;
use Laravel\Passport\Token;
use App\Models\User;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;


/**
 * Aimeos controller for the JSON REST API
 *
 * @package laravel
 * @subpackage Controller
 */
class JsonapiController extends Controller
{
    /**
     * Deletes the resource object or a list of resource objects
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request Request object
     * @return \Psr\Http\Message\ResponseInterface Response object containing the generated output
     */
    public function deleteAction(ServerRequestInterface $request)
    {
        if (isset($request->getQueryParams()['_token'])) {
            if (strlen($request->getQueryParams()['_token']) == 40) {
                session()->setId($request->getQueryParams()['_token']);
                Session::start();
                $sessionId = session()->getId();
                if (!strcmp(session()->get('_token'), $request->getQueryParams()['_token']))
                    Session::put('_token', $sessionId);
                else
                    throw new AcceptException('_token Is Not Valid.');

            } else {
                throw new AcceptException('_token Must be equal 40 Characters.');
            }
        } else {
            throw new AcceptException('_token is Required.');
        }
        return $this->createClient()->delete($request, new Response());
    }


    /**
     * Returns the requested resource object or list of resource objects
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request Request object
     * @return \Psr\Http\Message\ResponseInterface Response object containing the generated output
     */
    public function getAction(ServerRequestInterface $request)
    {
        $resource = Route::input('resource');
        if (!strcmp('basket', $resource))
            if (!isset($request->getQueryParams()['_token'])) {
                session()->migrate();
                session()->flush();
                $sessionId = session()->getId();
                Session::put('_token', $sessionId);
            } else if (strlen($request->getQueryParams()['_token']) == 40) {
                session()->setId($request->getQueryParams()['_token']);
                session::start();
                $sessionId = session()->getId();
                if (!strcmp(session()->get('_token'), $request->getQueryParams()['_token']))
                    Session::put('_token', $sessionId);
                else
                    throw new AcceptException('_token Is Not Valid.');
            } else {
                throw new AcceptException('_token Must be equal 40 Characters.');
            }
        return $this->createClient()->get($request, new Response());
    }


    /**
     * Updates a resource object or a list of resource objects
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request Request object
     * @return \Psr\Http\Message\ResponseInterface Response object containing the generated output
     */
    public function patchAction(ServerRequestInterface $request)
    {
        if (isset($request->getQueryParams()['_token'])) {
            if (strlen($request->getQueryParams()['_token']) == 40) {
                session()->setId($request->getQueryParams()['_token']);
                Session::start();
                $sessionId = session()->getId();
                if (!strcmp(session()->get('_token'), $request->getQueryParams()['_token']))
                    Session::put('_token', $sessionId);
                else
                    throw new AcceptException('_token Is Not Valid.');            } else
                throw new AcceptException('_token Must be equal 40 Characters.');
        } else {
            throw new AcceptException('_token is Required.');
        }
        return $this->createClient()->patch($request, new Response());
    }


    /**
     * Creates a new resource object or a list of resource objects
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request Request object
     * @return \Psr\Http\Message\ResponseInterface Response object containing the generated output
     */
    public function postAction(ServerRequestInterface $request)
    {
        if (isset($request->getQueryParams()['_token'])) {
            if (strlen($request->getQueryParams()['_token']) == 40) {
                session()->setId($request->getQueryParams()['_token']);
                Session::start();
                $sessionId = session()->getId();
                if (!strcmp(session()->get('_token'), $request->getQueryParams()['_token']))
                    Session::put('_token', $sessionId);
                else
                    throw new AcceptException('_token Is Not Valid.');            } else
                throw new AcceptException('_token Must be equal 40 Characters.');
        } else {
            throw new AcceptException('_token is Required.');
        }
        $resource = Route::input('resource');
        if (!strcmp('basket', $resource) and !count($request->getParsedBody())) {
            try {
                $user = $this->checkTokenForUser($request->getQueryParams()['access_token']);
            } catch (\Exception $e) {
                throw new ModelNotFoundException('This User Does not exist!');
            }
        }
        return $this->createClient()->post($request, new Response());
    }


    /**
     * Creates or updates a single resource object
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request Request object
     * @return \Psr\Http\Message\ResponseInterface Response object containing the generated output
     */
    public function putAction(ServerRequestInterface $request)
    {
        if (isset($request->getQueryParams()['_token'])) {
            if (strlen($request->getQueryParams()['_token']) == 40) {
                session()->setId($request->getQueryParams()['_token']);
                Session::start();
                $sessionId = session()->getId();
                if (!strcmp(session()->get('_token'), $request->getQueryParams()['_token']))
                    Session::put('_token', $sessionId);
                else
                    throw new AcceptException('_token Is Not Valid.');            } else
                throw new AcceptException('_token Must be equal 40 Characters.');
        } else {
            throw new AcceptException('_token is Required.');
        }
        return $this->createClient()->put($request, new Response());
    }


    /**
     * Returns the available HTTP verbs and the resource URLs
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request Request object
     * @return \Psr\Http\Message\ResponseInterface Response object containing the generated output
     */
    public function optionsAction(ServerRequestInterface $request)
    {
        return $this->createClient()->options($request, new Response());
    }


    /**
     * Returns the JsonAdm client
     *
     * @return \Aimeos\Client\JsonApi\Iface JsonApi client
     */
    protected function createClient()
    {
        $resource = Route::input('resource');
        $related = Route::input('related', Input::get('related'));

        $aimeos = app('\Aimeos\Shop\Base\Aimeos')->get();
        $tmplPaths = $aimeos->getCustomPaths('client/jsonapi/templates');

        $context = app('\Aimeos\Shop\Base\Context')->get();
        $langid = $context->getLocale()->getLanguageId();

        $context->setView(app('\Aimeos\Shop\Base\View')->create($context, $tmplPaths, $langid));

        return \Aimeos\Client\JsonApi\Factory::createClient($context, $resource . '/' . $related);
    }

    public function checkTokenForUser($accessToken)
    {
        $bearerToken = $accessToken;
        $jwt = (new Parser())->parse($bearerToken);
        $token = \Illuminate\Support\Facades\Redis::hget('tokens', $jwt->getClaim('jti'));

        if ($token) {
            $token = new Token((array)json_decode($token));

        } else {
            $tokenRepository = new TokenRepository();
            $token = $tokenRepository->find($jwt->getClaim('jti'));

            if (!$token)
                throw new AuthenticationException();
            if ($token->revoked) {
                \Illuminate\Support\Facades\Redis::hdel('tokens', $jwt->getClaim('jti'));
                throw new AuthenticationException();

            }

            \Illuminate\Support\Facades\Redis::hset('tokens', $jwt->getClaim('jti'), json_encode($token));

        }


        $user = \Illuminate\Support\Facades\Redis::hget('users', $token->user_id);


        if ($user) {
            $user = new User(json_decode($user, true));


        } else {
            $user = User::find($token->user_id);
            if (!$user) {
                throw new AuthenticationException();
            }
            \Illuminate\Support\Facades\Redis::hset('users', $token->user_id, json_encode($user));
        }

        return $user;


    }
}
