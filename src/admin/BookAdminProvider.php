<?php

namespace BookManager\Admin;

use League\Container\ServiceProvider\AbstractServiceProvider;
use League\Container\ServiceProvider\BootableServiceProviderInterface;
use Rabbit\Contracts\BootablePluginProviderInterface;

class BookAdminProvider extends AbstractServiceProvider implements BootableServiceProviderInterface, BootablePluginProviderInterface {

	public function register() {
		// Register services or dependencies
	}

	public function boot() {

	}

	public function bootPlugin() {

		$instance = $this;

		$this->getContainer()::macro(
			'admin',
			function () use ( $instance ) {
				return $instance->getContainer()->get( 'admin' );
			}
		);

	}
}