<?php

namespace IconAgency\LagoonLogs;

use Monolog\Handler\SocketHandler;
use Monolog\Formatter\LogstashFormatter;

class LagoonHandler {

	const LAGOON_LOGS_DEFAULT_SAFE_BRANCH = 'unknown_branch';

	const LAGOON_LOGS_DEFAULT_LAGOON_PROJECT = 'unknown_project';

	const LAGOON_LOGS_DEFAULT_CHUNK_SIZE_BYTES = 15000;

	/**
	 * Lagoon logs hostname
	 *
	 * @var string
	 */
	protected $host;

	/**
	 * Lagoon logs port
	 *
	 * @var int
	 */
	protected $port;

	/**
	 * Helper class to get a handler and processor.
	 *
	 * @param string $host Lagoon logs host.
	 * @param int $port Lagoon logs port.
	 */
	public function __construct( $host, $port ) {
		$this->host = $host;
		$this->port = $port;
	}

	/**
	 * Monolog UDP socket handler.
	 */
	public function handler(): SocketHandler {
		$connection = sprintf( 'udp://%s:%s', $this->host, $this->port );

		$handler = new SocketHandler( $connection );
		$handler->setChunkSize( self::LAGOON_LOGS_DEFAULT_CHUNK_SIZE_BYTES );

		$formatter = new LogstashFormatter( $this->identifier(), null, 'extra', 'ctxt_' );
		$handler->setFormatter( $formatter );

		return $handler;
	}

	/**
	 * Lagoon project identifier.
	 */
	protected function identifier(): string {
		return implode(
			'-',
			array(
				getenv( 'LAGOON_PROJECT' ) ?: self::LAGOON_LOGS_DEFAULT_LAGOON_PROJECT,
				getenv( 'LAGOON_GIT_SAFE_BRANCH' ) ?: self::LAGOON_LOGS_DEFAULT_SAFE_BRANCH,
			)
		);
	}
}
