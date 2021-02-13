<?php

namespace DeepWebSolutions\Framework\Core\Traits\Setup;

use DeepWebSolutions\Framework\Core\Interfaces\Traits\Setupable\SetupableInactive;
use DeepWebSolutions\Framework\Utilities\Handlers\AdminNoticesHandler;
use DeepWebSolutions\Framework\Utilities\Services\Traits\DependenciesService\DependenciesAdminNotice as UtilitiesDependenciesAdminNotice;

defined( 'ABSPATH' ) || exit;

/**
 * Functionality trait for enqueueing assets on the frontend.
 *
 * @since   1.0.0
 * @version 1.0.0
 * @author  Antonius Hegyes <a.hegyes@deep-web-solutions.com>
 * @package DeepWebSolutions\WP-Framework\Core\Traits\Setup
 */
trait DependenciesAdminNotice {
	use UtilitiesDependenciesAdminNotice;
	use SetupableInactive {
		setup as setup_dependencies_admin_notice;
	}

	/**
	 * Automagically call the asset registration method.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @param   AdminNoticesHandler   $admin_notices_handler    Instance of the admin notices handler.
	 */
	public function setup_dependencies_admin_notice( AdminNoticesHandler $admin_notices_handler ): void {
		$this->register_admin_notices( $admin_notices_handler );
	}
}
