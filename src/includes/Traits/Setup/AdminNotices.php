<?php

namespace DeepWebSolutions\Framework\Core\Traits\Setup;

use DeepWebSolutions\Framework\Core\Interfaces\Actions\Exceptions\SetupFailure;
use DeepWebSolutions\Framework\Core\Interfaces\Actions\Traits\Setupable\Setupable;
use DeepWebSolutions\Framework\Utilities\Handlers\AdminNoticesHandler;
use DeepWebSolutions\Framework\Utilities\Handlers\Traits\AdminNotices as AdminNoticesUtilities;

defined( 'ABSPATH' ) || exit;

/**
 * Functionality trait for registering admin notices on active instances.
 *
 * @since   1.0.0
 * @version 1.0.0
 * @author  Antonius Hegyes <a.hegyes@deep-web-solutions.com>
 * @package DeepWebSolutions\WP-Framework\Core\Traits\Setup
 */
trait AdminNotices {
	use AdminNoticesUtilities;
	use Setupable;

	/**
	 * Automagically call the admin notices registration method.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @param   AdminNoticesHandler     $admin_notices_handler      Instance of the admin notices handler.
	 *
	 * @return  null
	 */
	public function setup_admin_notices( AdminNoticesHandler $admin_notices_handler ): ?SetupFailure {
		$this->set_admin_notices_handler( $admin_notices_handler );
		$this->register_admin_notices( $admin_notices_handler );
		return null;
	}
}
