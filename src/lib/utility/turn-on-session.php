<?php
/**
 * Turn on Function
 * Checking too old session ID and start session
 * 
 * @category function
 * @see https://github.com/GoogleChromeLabs/samesite-examples/blob/master/php.md 
 * @see https://stackoverflow.com/questions/36877/how-do-you-set-up-use-httponly-cookies-in-php
 * @see https://stackoverflow.com/a/46971326/2308553 
 * @param number $life_time
 * @param string $session_name
 * @return void
 * 
 */
function turn_on_session($session_handler, $life_time, $cookies_name, $path, $domain, $secure, $httponly)
{

 if (is_object($session_handler)) {

    $session_handler->start();

    if (!$session_handler->isValid()) {

        $session_handler->forget();

    }

 }

  // Do not allow to use too old session ID
 if (!empty($_SESSION['deleted_time']) && $_SESSION['deleted_time'] < time() - $life_time) {
        
      session_unset();

      $session_handler->forget();

      session_write_close();

      session_id();
 
      $session_handler->start();
        
      set_cookies_scl($cookies_name, session_id(), $life_time, $path, $domain, $secure, $httponly);

      $session_handler->refresh();
     
  }
   
}


