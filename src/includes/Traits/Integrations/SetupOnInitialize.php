<?php

namespace DeepWebSolutions\Framework\Core\Traits\Integrations;

use DeepWebSolutions\Framework\Core\Interfaces\Actions\Setupable;
use DeepWebSolutions\Framework\Core\Interfaces\Actions\Traits\Initializable\IntegrateableOnInitialize;
use Exception;

defined( 'ABSPATH' ) || exit;

/**
 * Trait for piping the 'setup' method at the end of the initialization routine.
 *
 * @since   1.0.0
 * @version 1.0.0
 * @author  Antonius Hegyes <a.hegyes@deep-web-solutions.com>
 * @package DeepWebSolutions\WP-Framework\Core\Traits\Integrations
 */
trait SetupOnInitialize {
	use IntegrateableOnInitialize {
		integrate as integrate_setup_on_initialize;
	}

	/**
	 * After successful initialization, call the setup method of the using class.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @return  Exception|null
	 */
	public function integrate_setup_on_initialize(): ?Exception {
		if ( $this instanceof Setupable ) {
			return $this->setup();
		}

		return null;
	}
}
