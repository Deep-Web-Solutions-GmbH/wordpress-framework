<?php

namespace DeepWebSolutions\Framework\Core\Traits\Integrations;

use DeepWebSolutions\Framework\Core\Interfaces\Actions\Traits\Setupable\IntegrateableOnSetup;
use DeepWebSolutions\Framework\Utilities\Interfaces\Actions\Runnable;
use Exception;

defined( 'ABSPATH' ) || exit;

/**
 * Trait for piping the 'run' method at the end of the setup routine.
 *
 * @since   1.0.0
 * @version 1.0.0
 * @author  Antonius Hegyes <a.hegyes@deep-web-solutions.com>
 * @package DeepWebSolutions\WP-Framework\Core\Traits\Integrations
 */
trait RunOnSetup {
	use IntegrateableOnSetup;

	/**
	 * After successful setup, call the run method of the using class.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @return  Exception|null
	 */
	public function integrate_run_on_setup(): ?Exception {
		if ( $this instanceof Runnable ) {
			return $this->run();
		}

		return null;
	}
}
