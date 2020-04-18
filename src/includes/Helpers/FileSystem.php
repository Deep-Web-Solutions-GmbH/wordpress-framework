<?php

namespace DeepWebSolutions\Framework\Core\v1_0_0\Helpers;

defined( 'ABSPATH' ) || exit;

/**
 * A collection of very useful file system helpers to be used throughout the projects.
 *
 * @since   1.0.0
 * @version 1.0.0
 * @author  Antonius Hegyes <a.hegyes@deep-web-solutions.de>
 * @package DeepWebSolutions\Framework\Core\v1_0_0\Helpers
 */
final class FileSystem {
	/**
	 * Gets a list of all the files that exist in a certain directory recursively.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @param   string      $directory      The directory to search through.
	 * @param   int         $depth          Current recursion depth.
	 *
	 * @return  array   List of the relative paths of all the files inside the directory.
	 */
	public static function list_files( string $directory, int $depth = 0 ) : array {
		$directory = trailingslashit( $directory );
		if ( ! is_dir( $directory ) ) {
			return array();
		}

		$template_files = scandir( $directory );
		unset( $template_files[ array_search( '.', $template_files, true ) ] );
		unset( $template_files[ array_search( '..', $template_files, true ) ] );

		if ( count( $template_files ) < 1 ) {
			return array();
		}

		$files = call_user_func_array(
			'array_merge',
			array_map(
				function ( $template_file ) use ( $directory, $depth ) {
					if ( 'index.php' === $template_file ) {
						return array();
					}

					return is_dir( trailingslashit( $directory ) . $template_file )
						? self::list_files( trailingslashit( $directory ) . $template_file, $depth + 1 )
						: array( trailingslashit( $directory ) . $template_file );
				},
				$template_files
			)
		);

		// before returning for good, get rid of the original file path
		if ( 0 === $depth ) {
			$files = array_map(
				function ( $file ) use ( $directory ) {
					return str_replace( $directory, '', $file );
				},
				$files
			);
		}

		return $files;
	}

	/**
	 * Iterates through all the files in a certain directory and loads all of them.
	 * On top of that, it loads also all '.php' files in sub-directories which have
	 * the same name as the directory itself.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @param   string  $directory  The path to the directory from which the files should be loaded from.
	 */
	public static function load_files( string $directory ) : void {
		$directory = trailingslashit( $directory );
		$files     = self::list_files( $directory );

		foreach ( $files as $file ) {
			if ( basename( $file ) === $file || basename( $file, '.php' ) === ltrim( dirname( $file ), '/' ) ) {
				/* @noinspection PhpIncludeInspection */
				require_once $directory . $file;
			}
		}
	}
}