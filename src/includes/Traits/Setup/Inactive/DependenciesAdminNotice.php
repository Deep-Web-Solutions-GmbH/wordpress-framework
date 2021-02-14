<?php

namespace DeepWebSolutions\Framework\Core\Traits\Setup\Inactive;

use DeepWebSolutions\Framework\Core\Interfaces\Actions\Exceptions\SetupFailure;
use DeepWebSolutions\Framework\Core\Interfaces\Actions\Traits\Setupable\Integrations\SetupableInactive;
use DeepWebSolutions\Framework\Utilities\Handlers\AdminNoticesHandler;
use DeepWebSolutions\Framework\Utilities\Services\Traits\DependenciesService\DependenciesAdminNotice as UtilitiesDependenciesAdminNotice;

defined( 'ABSPATH' ) || exit;

/**
 * Functionality trait for enqueueing assets on the frontend.
 *
 * @since   1.0.0
 * @version 1.0.0
 * @author  Antonius Hegyes <a.hegyes@deep-web-solutions.com>
 * @package DeepWebSolutions\WP-Framework\Core\Traits\Setup\Inactive
 */
trait DependenciesAdminNotice {
	use UtilitiesDependenciesAdminNotice;
	use SetupableInactive;

	/**
	 * Automagically call the asset registration method.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @param   AdminNoticesHandler   $admin_notices_handler    Instance of the admin notices handler.
	 *
	 * @return  null
	 */
	public function setup_dependencies_admin_notice( AdminNoticesHandler $admin_notices_handler ): ?SetupFailure {
		$this->register_admin_notices( $admin_notices_handler );
		return null;
	}
}
