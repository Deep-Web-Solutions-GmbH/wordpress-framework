<?php

namespace DeepWebSolutions\Framework\Core\v1_0_0\Interfaces;

defined( 'ABSPATH' ) || exit;

/**
 * If a class implements this interface, then every time that the 'install' action will be triggered,
 * the 'install' method will be called if the current version is newer than the one previously installed.
 *
 * @since   1.0.0
 * @version 1.0.0
 * @author  Antonius Hegyes <a.hegyes@deep-web-solutions.de>
 * @package DeepWebSolutions\Framework\Core\v1_0_0\Interfaces
 */
interface Installable {
	/**
	 * Implements the data installation logic of the implementing class.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @return  void
	 */
	public static function install();

	/**
	 * Implements the data update logic of the implementing class.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @return  void
	 */
	public static function update();

	/**
	 * Implements the data uninstallation logic of the implementing class.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @return  void
	 */
	public static function uninstall();

	/**
	 * Returns the current version of the installable data of the implementing class.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @return  string
	 */
	public static function get_version();
}
