<?php
class Transloadit
{
  public $key = '';
  public $secret = '';

  public function __construct($attributes)
  {
    foreach ($attributes as $key => $val)
    {
      $this->{$key} = $val;
    }
  }

  public static function sign($params, $secret)
  {
    if (is_array($params))
    {
      $params = json_encode($params);
    }

    return hash_hmac('sha1', $params, $secret);
  }
}
?>
