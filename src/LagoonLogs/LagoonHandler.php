<?php

namespace IconAgency\LagoonLogs;

use Monolog\Handler\SocketHandler;
use Monolog\Formatter\LogstashFormatter;

class LagoonHandler {

	const LAGOON_LOGS_DEFAULT_IDENTIFIER = 'wordpress';

	const LAGOON_LOGS_DEFAULT_SAFE_BRANCH = 'safe_branch_unset';

	const LAGOON_LOGS_DEFAULT_LAGOON_PROJECT = 'project_unset';

	const LAGOON_LOGS_DEFAULT_CHUNK_SIZE_BYTES = 15000;

	protected $hostName;

	protected $hostPort;

	protected $logFullIdentifier;

	protected $parser;

	public function __construct( $host, $port, $logFullIdentifier ) {
		$this->hostName          = $host;
		$this->hostPort          = $port;
		$this->logFullIdentifier = $logFullIdentifier;
	}

	/**
	 * Initialize lagoon socket handler to log.
	 */
	public function handler(): SocketHandler {
		$formatter = new LogstashFormatter( $this->getHostProcessIndex(), null, 'extra', 'ctxt_' );

		$connection = sprintf( 'udp://%s:%s', $this->hostName, $this->hostPort );
    
		$handler = new SocketHandler( $connection );
		$handler->setChunkSize( self::LAGOON_LOGS_DEFAULT_CHUNK_SIZE_BYTES );
		$handler->setFormatter( $formatter );

		return $handler;
	}

	/**
	 * Get Lagoon project identifier.
	 *
	 * @return string
	 */
	protected function getHostProcessIndex() {
		return implode(
			'-',
			array(
				getenv( 'LAGOON_PROJECT' ) ?: self::LAGOON_LOGS_DEFAULT_LAGOON_PROJECT,
				getenv( 'LAGOON_GIT_SAFE_BRANCH' ) ?: self::LAGOON_LOGS_DEFAULT_SAFE_BRANCH,
			)
		);
	}
}
