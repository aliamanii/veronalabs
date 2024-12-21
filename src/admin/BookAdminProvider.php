<?php

namespace BookManager\Admin;

use League\Container\ServiceProvider\AbstractServiceProvider;
use League\Container\ServiceProvider\BootableServiceProviderInterface;
use Rabbit\Contracts\BootablePluginProviderInterface;

class BookAdminProvider extends AbstractServiceProvider implements BootableServiceProviderInterface, BootablePluginProviderInterface
{

    public function register()
    {
        // Register services or dependencies
    }

    public function boot()
    {
        add_action('admin_menu', array($this, 'admin_menu'));
    }

    public function admin_menu()
    {
        add_menu_page(
            __('Books List', 'book-library'),
            __('Books List', 'book-library'),
            'manage_options',
            'books-data',
            [$this, 'render_table_page'],
            'dashicons-book',
            56
        );
    }

    public function render_table_page()
    {
        $list_table = new BooksListTable();
        $list_table->prepare_items();
        ?>
        <div class="wrap">
            <h1 class="wp-heading-inline"><?php _e('Books List', 'book-library'); ?></h1>
            <form method="post">
                <?php $list_table->display(); ?>
            </form>
        </div>
        <?php
    }

    public function bootPlugin()
    {

        $instance = $this;

        $this->getContainer()::macro(
            'admin',
            function () use ($instance) {
                return $instance->getContainer()->get('admin');
            }
        );

    }
}