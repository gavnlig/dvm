<?php
    class Autoloader {

        static public function loader($className) {
            error_log($className);
            $filename = 'classes/' . str_replace('\\', '/', $className) . '.php';
            if (file_exists($filename)) {
                include($filename);
                if (class_exists($className)) {
                    return TRUE;
                }
            }
                
            return FALSE;
        }

    }

    spl_autoload_register('Autoloader::loader');
    
    function httpError($code, $message, $options = array()) {
        if (!headers_sent()) {
            \Util\Http\RequestHandler::sendContentType('application/json');
            \Util\Http\RequestHandler::sendStatus($code, $_SERVER['SERVER_PROTOCOL']);
            http_response_code($code);

            /*
             * don't send a challenge so no browser popup appears
             *
             * if ($code == 401) {
             *     \Util\Http\RequestHandler::sendHttpAuthChallenge(@$options['realm'] ?: 'restricted area');
             * }
             */
        }

        echo sprintf(
                '{"http":{"status":"error","code":%d,"message":%s}}',
                $code,
                json_encode($message)
            );
    }

    try {
        /*
         * load system configuration
         */
        $config = sprintf('%s/config.php', \Util\System::homePath());
        if(file_exists($config)) {
            include $config;
        }
        
        /*
         * read authenticated user from alternative header
         */
        if(!array_key_exists('PHP_AUTH_USER', $_SERVER)) {
            @list($_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PW']) = explode(':', base64_decode(substr(@$_SERVER['HTTP_DVMAUTH'], 6)));
        }
        
        $request = new \Util\Http\RestHandler($_SERVER);
        Application\Requests::dispatchPath($request)->send();

    } catch (Util\Http\Exceptions\MethodNotAllowed $e) {
        error_log(print_r($e, true));
        httpError(405, '');
    } catch (Util\Http\Exceptions\NotFound $e) {
        error_log(print_r($e, true));
        httpError(404, '');
    } catch (Util\Http\Exceptions\Forbidden $e) {
        error_log(print_r($e, true));
        httpError(403, '');
    } catch (Util\NotImplementedException $e) {
        error_log(print_r($e, true));
        httpError(501, $e->getMessage());
    } catch (Util\Http\Exceptions\BadResponse $e) {
        error_log(print_r($e, true));
        httpError(500, $e->getMessage());
    } catch (Util\Http\Exceptions\BadRequest $e) {
        error_log(print_r($e, true));
        httpError(400, $e->getMessage());
    } catch (Util\Http\Exceptions\InvalidAuthentication $e) {
        error_log(print_r($e, true));
        httpError(401, $e->getMessage(), array('realm' => 'Application'));
    } catch (\Exception $e) {
        error_log(print_r($e, true));
        httpError(500, $e->getMessage());
    }
