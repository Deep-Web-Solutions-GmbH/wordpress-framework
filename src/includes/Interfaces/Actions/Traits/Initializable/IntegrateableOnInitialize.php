<?php

namespace DeepWebSolutions\Framework\Core\Interfaces\Actions\Traits\Initializable;

use Exception;

defined( 'ABSPATH' ) || exit;

/**
 * Abstract trait that other traits should use to denote that they want their initialization integration logic called
 * after a successful initialization.
 *
 * @since   1.0.0
 * @version 1.0.0
 * @author  Antonius Hegyes <a.hegyes@deep-web-solutions.com>
 * @package DeepWebSolutions\WP-Framework\Core\Interfaces\Actions\Traits\Initializable
 */
trait IntegrateableOnInitialize {
	/**
	 * Executed after successful initialization of classes that use an inheriting trait.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @return  Exception|null
	 */
	abstract public function integrate(): ?Exception;
}
