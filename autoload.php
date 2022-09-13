<?php
  $autoload = 'vendor/autoload.php';
  if(file_exists($autoload)) {
    require_once $autoload;
  }
  else {
    if(0 > version_compare(PHP_VERSION, '8.1.0')) {
      die('PHP >= 8.1.0 needed (' . PHP_VERSION . " is installed)");
    }
    if(!extension_loaded('curl')) {
      die("'curl' extension not loaded!");
    }
    if(!extension_loaded('sodium')) {
      die("'sodium' extension not loaded!");
    }
    $autoload = new autoload();
    $autoload->addNamespace('tanglePHP\\Core\\', __DIR__ . '/packages/core/src');
    $autoload->addNamespace('tanglePHP\\Network\\', __DIR__ . '/packages/network/src');
    $autoload->addNamespace('tanglePHP\\SingleNodeClient\\', __DIR__ . '/packages/singlenode-client/src');
    $autoload->addNamespace('tanglePHP\\IndexerPlugin\\', __DIR__ . '/packages/indexer-plugin/src');
    $autoload->addNamespace('tanglePHP\\FaucetClient\\', __DIR__ . '/packages/faucet-client/src');
    $autoload->addNamespace('tanglePHP\\ChronicleClient\\', __DIR__ . '/packages/chronicle-client/src');
    $autoload->addNamespace('tanglePHP\\MarketClient\\', __DIR__ . '/packages/market-client/src');
    $autoload->register();
  }
  /**
   * Class autoload
   */
  class autoload {
    /**
     * An associative array where the key is a namespace prefix and the value
     * is an array of base directories for classes in that namespace.
     *
     * @var array
     */
    protected array $prefixes = [];

    /**
     * Register loader with SPL autoloader stack.
     *
     * @return void
     */
    public function register() {
      spl_autoload_register([
        $this,
        'loadClass',
      ]);
    }

    /**
     * Adds a base directory for a namespace prefix.
     *
     * @param string $prefix   The namespace prefix.
     * @param string $base_dir A base directory for class files in the
     *                         namespace.
     * @param bool   $prepend  If true, prepend the base directory to the stack
     *                         instead of appending it; this causes it to be searched first rather
     *                         than last.
     *
     * @return void
     */
    public function addNamespace(string $prefix, string $base_dir, bool $prepend = false) {
      // normalize namespace prefix
      $prefix = trim($prefix, '\\') . '\\';
      // normalize the base directory with a trailing separator
      $base_dir = rtrim($base_dir, DIRECTORY_SEPARATOR) . '/';
      // initialize the namespace prefix array
      if(isset($this->prefixes[$prefix]) === false) {
        $this->prefixes[$prefix] = [];
      }
      // retain the base directory for the namespace prefix
      if($prepend) {
        array_unshift($this->prefixes[$prefix], $base_dir);
      }
      else {
        array_push($this->prefixes[$prefix], $base_dir);
      }
    }

    /**
     * Loads the class file for a given class name.
     *
     * @param string $class The fully-qualified class name.
     *
     * @return string|bool The mapped file name on success, or boolean false on
     * failure.
     */
    public function loadClass(string $class): string|bool {
      // the current namespace prefix
      $prefix = $class;
      // work backwards through the namespace names of the fully-qualified
      // class name to find a mapped file name
      while(false !== $pos = strrpos($prefix, '\\')) {

        // retain the trailing namespace separator in the prefix
        $prefix = substr($class, 0, $pos + 1);
        // the rest is the relative class name
        $relative_class = substr($class, $pos + 1);
        // try to load a mapped file for the prefix and relative class
        $mapped_file = $this->loadMappedFile($prefix, $relative_class);
        if($mapped_file) {
          return $mapped_file;
        }
        // remove the trailing namespace separator for the next iteration
        // of strrpos()
        $prefix = rtrim($prefix, '\\');
      }

      // never found a mapped file
      return false;
    }

    /**
     * Load the mapped file for a namespace prefix and relative class.
     *
     * @param string $prefix         The namespace prefix.
     * @param string $relative_class The relative class name.
     *
     * @return false|string Boolean false if no mapped file can be loaded, or the
     * name of the mapped file that was loaded.
     */
    protected function loadMappedFile(string $prefix, string $relative_class): bool|string {
      // are there any base directories for this namespace prefix?
      if(isset($this->prefixes[$prefix]) === false) {
        return false;
      }
      // look through base directories for this namespace prefix
      foreach($this->prefixes[$prefix] as $base_dir) {

        // replace the namespace prefix with the base directory,
        // replace namespace separators with directory separators
        // in the relative class name, append with .php
        $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';
        // if the mapped file exists, require it
        if($this->requireFile($file)) {
          // yes, we're done
          return $file;
        }
      }

      // never found it
      return false;
    }

    /**
     * If a file exists, require it from the file system.
     *
     * @param string $file The file to require.
     *
     * @return bool True if the file exists, false if not.
     */
    protected function requireFile(string $file): bool {
      if(file_exists($file)) {
        require $file;

        return true;
      }

      return false;
    }
  }