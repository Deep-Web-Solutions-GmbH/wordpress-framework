<?php

namespace DeepWebSolutions\Framework\Core\v1_0_0\Abstracts;

use DeepWebSolutions\Framework\Core\v1_0_0\Exceptions\InexistentProperty;
use DeepWebSolutions\Framework\Core\v1_0_0\Exceptions\ReadOnly;
use DeepWebSolutions\Framework\Core\v1_0_0\Loader;

defined( 'ABSPATH' ) || exit;

/**
 * A template for encapsulating some of the most often required abilities of a class.
 *
 * @since   1.0.0
 * @version 1.0.0
 * @author  Antonius Hegyes <a.hegyes@deep-web-solutions.de>
 * @package DeepWebSolutions\Framework\Core\v1_0_0\Abstracts
 */
abstract class Root {
	// region FIELDS AND CONSTANTS

	/**
	 * Instance of the main plugin class.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @access  private
	 * @var     PluginBase
	 */
	private PluginBase $plugin;

	/**
	 * Instance of the hooks and shortcodes loader.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @access  private
	 * @var     Loader
	 */
	private Loader $loader;

	/**
	 * Maintains a list of all IDs of root class instances.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @access  private
	 * @var     array
	 */
	private static array $root_id = array();

	/**
	 * Maintains a list of all public names of root class instances.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @access  private
	 * @var     array
	 */
	private static array $root_public_name = array();

	// endregion

	// region MAGIC METHODS

	/**
	 * Root constructor.
	 *
	 * @param   PluginBase          $plugin     Instance of the main plugin class.
	 * @param   Loader          $loader     Instance of the hooks and shortcodes loader.
	 * @param   string          $root_id    The unique ID of the class instance. Must be persistent across requests.
	 * @param   string|false    $root_name  The 'nice_name' of the class instance. Must be persistent across requests. Mustn't be unique.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 */
	public function __construct( PluginBase $plugin, Loader $loader, $root_id, $root_name = false ) {
		self::$root_id[ static::class ]   = self::$root_id[ static::class ] ?? array();
		self::$root_id[ static::class ][] = $root_id;

		self::$root_public_name[ static::class ]   = self::$root_public_name[ static::class ] ?? array();
		self::$root_public_name[ static::class ][] = $root_name ?: static::class; // phpcs:ignore

		$this->plugin = $plugin;
		$this->loader = $loader;
		$this->loader->add_action( 'plugins_loaded', $this, 'configure_instance', 100 );

		$this->load_dependencies();
	}

	/**
	 * Used for easily accessing global variables and the values of defined getters.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @param   string  $name   Name of the property that should be retrieved.
	 *
	 * @return  mixed
	 */
	public function __get( string $name ) {
		if ( method_exists( $this, ( $function = "get_{$name}" ) ) || method_exists( $this, ( $function = 'get' . ucfirst( $name ) ) ) ) { // phpcs:ignore
			return $this->{$function}();
		}

		if ( method_exists( $this, ( $function = "is_{$name}" ) ) || method_exists( $this, ( $function = 'is' . ucfirst( $name ) ) ) ) { // phpcs:ignore
			return $this->{$function}();
		}

		if ( isset( $GLOBALS[ $name ] ) ) {
			return $GLOBALS[ $name ];
		}

		return false;
	}

	/**
	 * Used for writing data to global variables and to existent properties that have a setter defined.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @param   string  $name   The name of the property that should be reassigned.
	 * @param   mixed   $value  The value that should be assigned to the property.
	 *
	 * @throws  ReadOnly            Thrown if there is a getter for the property, but no setter.
	 * @throws  InexistentProperty  Thrown if there are no getters and no setter for the property, and a global variable also doesn't exist already.
	 *
	 * @return  mixed
	 */
	public function __set( string $name, $value ) {
		$function = "set_{$name}";
		if ( method_exists( $this, $function ) ) {
			return $this->{$function}( $value );
		}

		$function = 'set' . ucfirst( $name );
		if ( method_exists( $this, $function ) ) {
			return $this->{$function}( $value );
		}

		if ( method_exists( $this, "get_{$name}" ) || method_exists( $this, 'get' . ucfirst( $name ) )
			|| method_exists( $this, "is_{$name}" ) || method_exists( $this, 'is' . ucfirst( $name ) ) ) {
			throw new ReadOnly( sprintf( 'Property %s is ready-only', $name ) );
		}

		if ( isset( $GLOBALS[ $name ] ) ) {
			$GLOBALS[ $name ] = $value; // phpcs:ignore
			return true;
		}

		throw new InexistentProperty( sprintf( 'Inexistent property: %s', $name ) );
	}

	/**
	 * Used for checking whether a global variable or a getter for a given property exists.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @param   string  $name   The name of the property that existence is being checked.
	 *
	 * @return  bool
	 */
	public function __isset( string $name ) {
		if ( method_exists( $this, ( $function = "get_{$name}" ) ) || method_exists( $this, ( $function = 'get' . ucfirst($name) ) ) ) { // phpcs:ignore
			return true;
		}

		if ( method_exists( $this, ( $function = "is_{$name}" ) ) || method_exists( $this, ( $function = 'is' . ucfirst($name) ) ) ) { // phpcs:ignore
			return true;
		}

		return isset( $GLOBALS[ $name ] );
	}

	// endregion

	// region METHODS

	/**
	 * A late sort-of-constructor. Executed when all the plugins should have finished loading to make sure that everything
	 * is available.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 */
	final public function configure_instance() : void {
		$this->local_configure_instance();

		$this->loader->add_action( 'admin_enqueue_scripts', $this, 'admin_enqueue_assets' );
		$this->loader->add_action( 'wp_enqueue_scripts', $this, 'enqueue_assets' );
	}

	/**
	 * Child classes should define their dependencies in here.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 */
	protected function load_dependencies() : void {
		/* Child classes can overwrite this. Normally, this is not needed since the auto-loader should handle it... */
	}

	/**
	 * Allows children classes to overwrite the default class settings. If they fail to properly do so,
	 * these defaults will be used.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 */
	protected function local_configure_instance() : void {

	}

	// endregion

	// region HELPERS

	/**
	 * Computes the short name of the class.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @return  string
	 */
	final public static function get_class_name() : string {
		return ( new \ReflectionClass( static::class ) )->getShortName();
	}

	/**
	 * Computes the full name of the class.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @return  string
	 */
	final public static function get_full_class_name() : string {
		return '\\' . ltrim( ( new \ReflectionClass( static::class ) )->getName(), '\\' );
	}

	/**
	 * Computes the name of the file that the class is written in.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @return  string
	 */
	final public static function get_file_name() : string {
		return ( new \ReflectionClass( static::class ) )->getFileName();
	}

	/**
	 * Returns the path to the current folder of the class which inherits this class.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @param   bool    $keep_file_name     If true, then returns the path including the end filename.
	 *
	 * @return  string
	 */
	final public static function get_base_path( bool $keep_file_name = false ) {
		$file_name = static::get_file_name();

		return $keep_file_name
			? trailingslashit( $file_name )
			: trailingslashit( plugin_dir_path( $file_name ) );
	}

	/**
	 * Returns the relative URL to the current folder of the class which inherits this class.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @param   bool    $keep_file_name     If true, then returns the URL including the end filename.
	 *
	 * @return  string
	 */
	final public static function get_base_relative_url( bool $keep_file_name = false ) {
		$file_name = static::get_file_name();

		$relative_url = $keep_file_name
			? str_replace( ABSPATH, '', trailingslashit( $file_name ) )
			: trailingslashit( plugin_dir_url( $file_name ) );

		// Fix for operating systems where the directory separator is not a forward slash.
		return str_replace( DIRECTORY_SEPARATOR, '/', $relative_url );
	}

	/**
	 * Returns the base path of the folder of the file which inherits this class relative to the current plugin.
	 *
	 * @return  string
	 */
	public static function get_relative_base_path() {
		return str_replace( DWS_CUSTOM_EXTENSIONS_BASE_PATH, '', self::get_base_path() );
	}

	/**
	 * Returns the path to a custom file or directory prepended by the path
	 * to the calling class' path.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @param   string  $path       The path to append to the current file's base path.
	 * @param   bool    $relative   True if the path should be relative to the WP installation, false otherwise.
	 *
	 * @return  string
	 */
	final public static function get_custom_base_path( $path, $relative = false ) {
		return trailingslashit( self::get_base_path( $relative ) . $path );
	}

	/**
	 * Returns the path to the assets folder of the current class.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @param   bool    $relative   True if the path should be relative to the WP installation, false otherwise.
	 *
	 * @return  string
	 */
	final public static function get_assets_base_path( $relative = false ) {
		return self::get_custom_base_path( 'assets', $relative );
	}

	/**
	 * Returns the path to the templates folder of the current class.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @param   bool    $relative   True if the path should be relative to the WP installation, false otherwise.
	 *
	 * @return  string
	 */
	final public static function get_templates_base_path( $relative = false ) {
		return self::get_custom_base_path( 'templates', $relative );
	}

	/**
	 * Returns the path to the classes folder of the current class.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @param   bool    $relative   True if the path should be relative to the WP installation, false otherwise.
	 *
	 * @return  string
	 */
	final public static function get_includes_base_path( $relative = false ) {
		return self::get_custom_base_path( 'includes', $relative );
	}

	/**
	 * Returns a meaningful probably unique name for an internal hook.
	 *
	 * @since   1.0.0
	 * @version 1.5.2
	 *
	 * @param   string          $name       The actual descriptor of the hook's purpose.
	 * @param   string|array    $extra      Further descriptor of the hook's purpose.
	 * @param   string          $root       Prepended to all hooks inside the same class.
	 *
	 * @return  string  The resulting internal hook.
	 */
	public static function get_hook_name( $name, $extra = array(), $root = '' ) {
		return str_replace(
			' ',
			'-',
			join(
				'_',
				array_filter(
					array_merge(
						array( static::get_plugin_name(), $root, $name ),
						is_array( $extra ) ? $extra : array( $extra )
					)
				)
			)
		);
	}

	/**
	 * Returns a meaningful potentially unique handle for an asset.
	 *
	 * @since   1.0.0
	 * @version 1.5.2
	 *
	 * @param   string  $name   The actual descriptor of the asset's purpose. Leave blank for default.
	 *
	 * @return  string  A valid asset handle.
	 */
	public static function get_asset_handle( $name = '' ) {
		return str_replace(
			' ',
			'-',
			join(
				'_',
				array_filter(
					array(
						static::get_plugin_name(),
						self::get_root_public_name(),
						$name,
					)
				)
			)
		);
	}

	// endregion
}
