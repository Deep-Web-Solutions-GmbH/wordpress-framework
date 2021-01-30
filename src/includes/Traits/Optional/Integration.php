<?php

namespace DeepWebSolutions\Framework\Core\Traits\Optional;

use DeepWebSolutions\Framework\Core\Traits\Abstracts\Optional;

/**
 * Functionality trait for dependent setup of integration functionalities.
 *
 * @since   1.0.0
 * @version 1.0.0
 * @package DeepWebSolutions\Framework\Core\Traits\Optional
 */
trait Integration {
	use Optional {
		is_active as is_active_integration;
	}
}
