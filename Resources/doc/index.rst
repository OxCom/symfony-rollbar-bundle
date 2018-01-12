The Symfony Rollbar Bundle
=======================

The SymfonyRollbarBundle helps you track health state of the project. This bundle provides integration with Rollbar

Installation
============

Step 1: Download the Bundle
---------------------------

Open a command console, enter your project directory and execute the
following command to download the latest stable version of this bundle:

.. code-block:: bash

    $ composer require oxcom/symfony-rollbar-bundle

This command requires you to have Composer installed globally, as explained
in the `installation chapter`_ of the Composer documentation.

Step 2: Enable the Bundle
-------------------------
Then, enable the bundle by adding it to the list of registered bundles
in the ``AppKernel::registerBundles()`` of your project:

.. note::
    <?php

    // ..
    public function registerBundles()
    {
        $bundles = [
            // ...
            new \SymfonyRollbarBundle\SymfonyRollbarBundle(),
            // ...
        ];

        return $bundles;
    }

Step 3: Update configuration
----------------------------
Add new section in configuration file
.. code-block:: yaml
    symfony_rollbar:
      enable: true
      rollbar:
        access_token: '%ROLLBAR_ACCESS_TOKEN%'
        environment: '%kernel.environment%'

Replace ``%ROLLBAR_ACCESS_TOKEN%`` with token for Rollbar
.. _`installation chapter`: https://getcomposer.org/doc/00-intro.md