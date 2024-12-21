<?php

namespace BookManager\Providers;

use Rabbit\Contracts\BootablePluginProviderInterface;
use League\Container\ServiceProvider\AbstractServiceProvider;
use League\Container\ServiceProvider\BootableServiceProviderInterface;

class BookServiceProvider extends AbstractServiceProvider implements BootableServiceProviderInterface, BootablePluginProviderInterface {

	public function register() {
		// Register services or dependencies
	}

	public function boot() {

	}

	public function bootPlugin() {

		$instance = $this;

		$this->getContainer()::macro(
			'book',
			function () use ( $instance ) {
				return $instance->getContainer()->get( 'book' );
			}
		);

	}
}