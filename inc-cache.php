<?php
class cache {
  private $compress = false;
  
  private $MC = null;
  
  private $skipAPC = false;
  
  public function __construct() {
    $this->MC = new Memcache;
    $this->addServer(); // Default connection to localhost
  }
  
  public function addServer($url='localhost',$port=11211,$persistent=true,$weight=1) {
    $this->MC->addServer($url,$port);
  }
  
  public function get($key) {
    $key = $this->generateKey($key);
    $ret = null;
    if(!$this->skipAPC) $ret = apc_fetch($key);
    if(!$ret) {
      $ret = $this->MC->get($key);
      if(!$ret) return false;
      // Found from MC, sync APC
      $this->store($key,$ret);
    }
    
    return $ret;
  }
  public function fetch($key){ return $this->get($key); }
  
  public function set($key, $data, $ttl=86400) {
    $key = $this->generateKey($key);
    if(!$this->skipAPC) apc_store($key, $data, $ttl);
    $this->MC->set($key, $data, $this->compress, $ttl);
    return;
  }
  public function store($key, $data, $ttl=86400) {return $this->set($key, $data, $ttl=86400);}
  
  public function delete($key) {
    $key = $this->generateKey($key);
    apc_delete($key);
    $this->MC->delete($key);
    return;
  }
  
  public function flush() {
    if(!$this->MC->flush()) die('Failed to flush MC!');
    if(!apc_clear_cache('user')) die('Failed to flush APC!');
    echo 'Cache flushed!';
  }
  
  public function generateKey($key) {
    return APC_PREFIX.$key; // TODO: Hash
  }
  
  public function getServerLoad() {
    $load = file_get_contents('/proc/loadavg');
    $load = explode(' ', $load);
    return $load[0]; // Return "one minute load"
  }
}

// Ignite cache
$cache = new cache();
if(!is_array($CacheServers)) $CacheServers = array();
foreach($CacheServers AS $server) {
  if(!$server['port']) $server['port'] = 11211;
  if($server['persistent']===null) $server['persistent'] = true;
  if(!$server['weight']) $server['weight'] = 1;
  $cache->addServer($server['url'],$server['port'],$server['persistent'],$server['weight']);
}

?>