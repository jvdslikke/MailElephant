<?php
/*
  By Marco Arment <me@marco.org>.
  This code is released in the public domain.

  THERE IS ABSOLUTELY NO WARRANTY.

  Usage example:

  // In a registration or password-change form:
  $hash_for_user = Bcrypt::hash($_POST['password']);

  // In a login form:
  $is_correct = Bcrypt::check($_POST['password'], $stored_hash_for_user);

  // In a login form when migrating entries gradually from a legacy SHA-1 hash:
  $is_correct = Bcrypt::check(
      $_POST['password'], 
      $stored_hash_for_user, 
      function($password, $hash) { return $hash == sha1($password); }
  );
  
  if ($is_correct && Bcrypt::is_legacy_hash($stored_hash_for_user)) {
      $user->store_new_hash(Bcrypt::hash($_POST['password']));
  }

*/

/**
 * https://git.github.com/1053158
 * 
 * @version July 01, 2011 (legacy hash code and standalone functions removed)
 */
class Common_Bcrypt
{
    const DEFAULT_WORK_FACTOR = 8;

    public static function hash($password, $work_factor = 0)
    {
        if (version_compare(PHP_VERSION, '5.3') < 0) throw new Exception('Bcrypt requires PHP 5.3 or above');

        if (! function_exists('openssl_random_pseudo_bytes')) {
            throw new Exception('Bcrypt requires openssl PHP extension');
        }

        if ($work_factor < 4 || $work_factor > 31) $work_factor = self::DEFAULT_WORK_FACTOR;
        $salt = 
            '$2a$' . str_pad($work_factor, 2, '0', STR_PAD_LEFT) . '$' .
            substr(
                strtr(base64_encode(openssl_random_pseudo_bytes(16)), '+', '.'), 
                0, 22
            )
        ;
        return crypt($password, $salt);
    }

    public static function check($password, $stored_hash)
    {
        if (version_compare(PHP_VERSION, '5.3') < 0) throw new Exception('Bcrypt requires PHP 5.3 or above');

        return crypt($password, $stored_hash) == $stored_hash;
    }
}