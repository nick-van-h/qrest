<?php

namespace Qrest\Util;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Routing\Exception\MethodNotAllowedException;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Routing\Exception\NoConfigurationException;

class Router
{
    public function __invoke(RouteCollection $routes)
    {
        //Custom
        $context = new RequestContext();
        $request = Request::create('home');
        $context->fromRequest(Request::create('home'));
        $matcher = new UrlMatcher($routes, $context);
        try {
            $arrayUri = explode('?', $_SERVER['REQUEST_URI']);
            // $matcher = $matcher->match($arrayUri[0]);
            $route = isset($_GET['route']) ? '/' . $_GET['route'] : '';
            $matcher = $matcher->match($route);

            // Cast params to int if numeric
            array_walk($matcher, function (&$param) {
                if (is_numeric($param)) {
                    $param = (int) $param;
                }
            });


            $className = $matcher['controller'];
            $classInstance = new $className();

            // Add routes as paramaters to the next class
            $params = array_merge(array_slice($matcher, 2, -1), array('routes' => $routes));

            //Call the page controller to render the page
            call_user_func_array(array($classInstance, $matcher['method']), $params);
        } catch (MethodNotAllowedException $e) {
            $this->errorRouter('405', $e);
        } catch (ResourceNotFoundException $e) {
            $this->errorRouter('404', $e);
        } catch (NoConfigurationException $e) {
            $this->errorRouter('501', $e);
        } catch (\Exception $e) {
            $this->errorRouter('500', $e);
        } catch (\TypeError $e) {
            $this->errorRouter('500', $e);
        } catch (\Error $e) {
            $this->errorRouter('500', $e);
        }
    }

    private function errorRouter($errorCode, $e)
    {
        $className = 'Qrest\Controllers\PageController';
        $classInstance = new $className();
        $params = array('errorCode' => $errorCode, 'message' => $e);
        call_user_func_array(array($classInstance, 'showErrorPage'), $params);
    }
}
